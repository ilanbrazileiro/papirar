<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QuestionTaxonomyReviewApiController extends Controller
{
    public function reviewTaxonomy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject_id' => ['nullable','integer','exists:subjects,id'],
            'active' => ['nullable','boolean'],
            'per_page' => ['nullable','integer','min:1','max:100'],
        ]);

        $perPage = (int)($data['per_page'] ?? 50);

        if (!empty($data['subject_id'])) {
            $subject = Subject::findOrFail((int)$data['subject_id']);
            $query = Topic::where('subject_id', $subject->id)->withCount('questions')->orderBy('name');

            if (array_key_exists('active', $data)) {
                $query->where('active', (bool)$data['active']);
            }

            return response()->json([
                'mode' => 'topics',
                'subject' => ['id'=>$subject->id,'name'=>$subject->name,'active'=>(bool)$subject->active],
                'data' => $query->paginate($perPage),
            ]);
        }

        $query = Subject::withCount(['topics','questions'])->orderBy('name');

        if (array_key_exists('active', $data)) {
            $query->where('active', (bool)$data['active']);
        }

        return response()->json(['mode'=>'subjects','data'=>$query->paginate($perPage)]);
    }

    public function updateQuestionClassification(Request $request, Question $question): JsonResponse
    {
        if ($question->status === Question::STATUS_ARCHIVED) {
            return response()->json(['message'=>'Questões arquivadas não podem ser reclassificadas.'], 409);
        }

        $data = $request->validate([
            'subject_id' => ['required','integer','exists:subjects,id'],
            'topic_id' => ['required','integer','exists:topics,id'],
            'reason' => ['required','string','min:10','max:1000'],
        ]);

        $topic = Topic::findOrFail((int)$data['topic_id']);

        if ((int)$topic->subject_id !== (int)$data['subject_id']) {
            throw ValidationException::withMessages([
                'topic_id' => ['O tópico informado não pertence à disciplina selecionada.'],
            ]);
        }

        $before = ['subject_id'=>$question->subject_id,'topic_id'=>$question->topic_id];

        $question->update([
            'subject_id' => (int)$data['subject_id'],
            'topic_id' => (int)$data['topic_id'],
        ]);

        Log::info('GPT Revisor reclassificou questão', [
            'question_id'=>$question->id,
            'before'=>$before,
            'after'=>['subject_id'=>$question->subject_id,'topic_id'=>$question->topic_id],
            'reason'=>$data['reason'],
        ]);

        return response()->json([
            'message'=>'Questão reclassificada com sucesso.',
            'data'=>['id'=>$question->id,'status'=>$question->status,'subject_id'=>$question->subject_id,'topic_id'=>$question->topic_id],
        ]);
    }

    public function moveTopic(Request $request, Topic $topic): JsonResponse
    {
        $data = $request->validate([
            'target_subject_id' => ['required','integer','exists:subjects,id'],
            'confirm' => ['required','accepted'],
            'reason' => ['required','string','min:10','max:1000'],
        ]);

        $targetSubjectId = (int)$data['target_subject_id'];

        if ((int)$topic->subject_id === $targetSubjectId) {
            return response()->json(['message'=>'O tópico já pertence à disciplina de destino.'], 409);
        }

        $normalized = $this->normalize($topic->name);
        $collision = Topic::where('subject_id',$targetSubjectId)->get(['id','name'])
            ->first(fn($t) => $this->normalize($t->name) === $normalized);

        if ($collision) {
            return response()->json([
                'message'=>'Já existe tópico equivalente no destino. Use mesclagem.',
                'source_topic_id'=>$topic->id,
                'target_topic_id'=>$collision->id,
                'target_topic_name'=>$collision->name,
            ], 409);
        }

        DB::transaction(function () use ($topic,$targetSubjectId) {
            Question::where('topic_id',$topic->id)->update(['subject_id'=>$targetSubjectId]);
            $topic->update(['subject_id'=>$targetSubjectId]);
        });

        Log::warning('GPT Revisor moveu tópico', [
            'topic_id'=>$topic->id,'target_subject_id'=>$targetSubjectId,'reason'=>$data['reason'],
        ]);

        return response()->json(['message'=>'Tópico movido com sucesso.']);
    }

    public function mergeTopic(Request $request, Topic $sourceTopic): JsonResponse
    {
        $data = $request->validate([
            'target_topic_id' => ['required','integer','exists:topics,id'],
            'confirm' => ['required','accepted'],
            'reason' => ['required','string','min:10','max:1000'],
        ]);

        $target = Topic::findOrFail((int)$data['target_topic_id']);

        if ($target->id === $sourceTopic->id) {
            return response()->json(['message'=>'Origem e destino não podem ser iguais.'], 422);
        }

        $affected = [
            'questions'=>Question::where('topic_id',$sourceTopic->id)->count(),
            'course_links'=>$this->countPivot('course_topics','topic_id',$sourceTopic->id),
        ];

        DB::transaction(function () use ($sourceTopic,$target) {
            Question::where('topic_id',$sourceTopic->id)->update([
                'topic_id'=>$target->id,
                'subject_id'=>$target->subject_id,
            ]);

            $this->mergePivot('course_topics','topic_id',$sourceTopic->id,$target->id,'course_id');
            $sourceTopic->update(['active'=>false]);
        });

        Log::warning('GPT Revisor mesclou tópicos', [
            'source_topic_id'=>$sourceTopic->id,'target_topic_id'=>$target->id,'affected'=>$affected,'reason'=>$data['reason'],
        ]);

        return response()->json([
            'message'=>'Tópicos mesclados. A origem foi desativada.',
            'affected'=>$affected,
        ]);
    }

    public function mergeSubject(Request $request, Subject $sourceSubject): JsonResponse
    {
        $data = $request->validate([
            'target_subject_id' => ['required','integer','exists:subjects,id'],
            'confirm' => ['required','accepted'],
            'reason' => ['required','string','min:10','max:1000'],
        ]);

        $target = Subject::findOrFail((int)$data['target_subject_id']);

        if ($target->id === $sourceSubject->id) {
            return response()->json(['message'=>'Origem e destino não podem ser iguais.'], 422);
        }

        $remaining = Topic::where('subject_id',$sourceSubject->id)
            ->where('active',true)->orderBy('name')->get(['id','name']);

        if ($remaining->isNotEmpty()) {
            return response()->json([
                'message'=>'A disciplina origem ainda possui tópicos ativos. Mova ou mescle-os antes.',
                'remaining_topics'=>$remaining,
            ], 409);
        }

        $affected = [
            'questions'=>Question::where('subject_id',$sourceSubject->id)->count(),
            'source_materials'=>Schema::hasTable('source_materials')
                ? DB::table('source_materials')->where('subject_id',$sourceSubject->id)->count() : 0,
            'course_links'=>$this->countPivot('course_subjects','subject_id',$sourceSubject->id),
            'exam_links'=>$this->countPivot('exam_subjects','subject_id',$sourceSubject->id),
        ];

        DB::transaction(function () use ($sourceSubject,$target) {
            Question::where('subject_id',$sourceSubject->id)->update(['subject_id'=>$target->id]);

            if (Schema::hasTable('source_materials') && Schema::hasColumn('source_materials','subject_id')) {
                DB::table('source_materials')->where('subject_id',$sourceSubject->id)->update(['subject_id'=>$target->id]);
            }

            $this->mergePivot('course_subjects','subject_id',$sourceSubject->id,$target->id,'course_id');
            $this->mergePivot('exam_subjects','subject_id',$sourceSubject->id,$target->id,'exam_id');
            $sourceSubject->update(['active'=>false]);
        });

        Log::warning('GPT Revisor mesclou disciplinas', [
            'source_subject_id'=>$sourceSubject->id,'target_subject_id'=>$target->id,'affected'=>$affected,'reason'=>$data['reason'],
        ]);

        return response()->json([
            'message'=>'Disciplinas mescladas. A origem foi desativada.',
            'affected'=>$affected,
        ]);
    }

    private function countPivot(string $table,string $fk,int $id): int
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table,$fk)) return 0;
        return DB::table($table)->where($fk,$id)->count();
    }

    private function mergePivot(string $table,string $fk,int $source,int $target,string $owner): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table,$fk) || !Schema::hasColumn($table,$owner)) return;

        foreach (DB::table($table)->where($fk,$source)->get() as $row) {
            $ownerId = $row->{$owner};
            $targetExists = DB::table($table)->where($owner,$ownerId)->where($fk,$target)->exists();

            if ($targetExists) {
                DB::table($table)->where($owner,$ownerId)->where($fk,$source)->delete();
            } else {
                DB::table($table)->where($owner,$ownerId)->where($fk,$source)->update([$fk=>$target]);
            }
        }
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/',' ')->squish()->toString();
    }
}
