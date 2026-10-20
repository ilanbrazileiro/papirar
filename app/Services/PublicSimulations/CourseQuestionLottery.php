<?php

namespace App\Services\PublicSimulations;

use App\Models\Course;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Sorteio baseado no escopo acadêmico efetivamente cadastrado para o curso. */
class CourseQuestionLottery
{
    public function subjects(Course $course): array
    {
        $examScope = $course->inherit_exam_scope && $course->exam_id;
        $rows = $examScope
            ? DB::table('exam_subjects')->join('subjects', 'subjects.id', '=', 'exam_subjects.subject_id')
                ->where('exam_subjects.exam_id', $course->exam_id)->where('exam_subjects.is_active', true)
                ->where('subjects.active', true)->get(['subjects.id', 'subjects.name'])
            : DB::table('course_subjects')->join('subjects', 'subjects.id', '=', 'course_subjects.subject_id')
                ->where('course_subjects.course_id', $course->id)->where('course_subjects.is_active', true)
                ->where('subjects.active', true)->get(['subjects.id', 'subjects.name']);

        $result = [];
        foreach ($rows->unique('id') as $row) {
            $result[] = ['id' => (int) $row->id, 'name' => $row->name,
                'available' => $this->eligible($course, (int) $row->id)->count()];
        }
        return $result;
    }

    /** Retorna exatamente a quantidade de cada disciplina, sem substituir por outras. */
    public function draw(Course $course, array $quantities): array
    {
        $eligibleSubjects = collect($this->subjects($course))->keyBy('id');
        $ordered = [];
        $total = 0;
        foreach ($quantities as $subjectId => $amount) {
            $subjectId = (int) $subjectId;
            $amount = (int) $amount;
            if ($amount === 0) continue;
            if ($amount < 0 || $amount > 120 || ! $eligibleSubjects->has($subjectId)) {
                throw ValidationException::withMessages(['quantities' => 'Disciplina ou quantidade inválida para este curso.']);
            }
            $total += $amount;
            if ($total > 120) throw ValidationException::withMessages(['quantities' => 'O simulado aceita no máximo 120 questões.']);
            $subject = $eligibleSubjects->get($subjectId);
            if ($amount > $subject['available']) {
                throw ValidationException::withMessages(['quantities' => 'A disciplina '.$subject['name'].' possui somente '.$subject['available'].' questões elegíveis.']);
            }
            $ids = $this->eligible($course, $subjectId)->inRandomOrder()->limit($amount)->pluck('questions.id')->all();
            if (count($ids) !== $amount) {
                throw ValidationException::withMessages(['quantities' => 'Não há questões suficientes em '.$subject['name'].'.']);
            }
            array_push($ordered, ...array_map('intval', $ids));
        }
        if ($total < 1) throw ValidationException::withMessages(['quantities' => 'Informe pelo menos uma questão em alguma disciplina.']);
        shuffle($ordered);
        return $ordered;
    }

    private function eligible(Course $course, int $subjectId): Builder
    {
        $examScope = $course->inherit_exam_scope && $course->exam_id;
        $topicIds = $examScope
            ? DB::table('exam_subject_topics')
                ->join('exam_subjects', 'exam_subjects.id', '=', 'exam_subject_topics.exam_subject_id')
                ->where('exam_subjects.exam_id', $course->exam_id)
                ->where('exam_subjects.subject_id', $subjectId)
                ->where('exam_subjects.is_active', true)->where('exam_subject_topics.is_active', true)
                ->pluck('exam_subject_topics.topic_id')->all()
            : DB::table('course_topics')
                ->join('topics', 'topics.id', '=', 'course_topics.topic_id')
                ->where('course_topics.course_id', $course->id)->where('course_topics.is_active', true)
                ->where('topics.subject_id', $subjectId)->pluck('course_topics.topic_id')->all();

        // Se os tópicos não foram definidos para esta disciplina, não ampliar o sorteio.
        $query = Question::query()->visibleToStudent()->where('questions.subject_id', $subjectId)
            ->whereIn('questions.topic_id', $topicIds ?: [0]);

        if ($course->corporation_id) {
            $query->where(fn ($q) => $q->whereNull('questions.corporation_id')
                ->orWhere('questions.corporation_id', $course->corporation_id));
        }
        if ($course->exam_id) {
            $query->where(fn ($q) => $q->whereNull('questions.exam_id')
                ->orWhere('questions.exam_id', $course->exam_id));
        }
        return $query;
    }
}
