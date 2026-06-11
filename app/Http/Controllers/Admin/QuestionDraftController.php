<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class QuestionDraftController extends Controller
{
    public function __invoke(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $difficulty = trim((string) $request->get('difficulty', ''));
        $corporationId = $request->integer('corporation_id');
        $subjectId = $request->integer('subject_id');
        $sourceMaterialId = $request->integer('source_material_id');

        $relations = ['corporation', 'exam', 'subject', 'topic'];

        $query = Question::query()
            ->with($relations)
            ->where('status', 'draft')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('statement', 'like', "%{$search}%")
                        ->orWhere('source_reference', 'like', "%{$search}%");
                });
            })
            ->when($difficulty !== '', fn ($q) => $q->where('difficulty', $difficulty))
            ->when($corporationId, fn ($q) => $q->where('corporation_id', $corporationId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId));

        if ($sourceMaterialId && Schema::hasColumn('questions', 'source_material_id')) {
            $query->where('source_material_id', $sourceMaterialId);
        }

        $questions = $query
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $sourceMaterials = collect();
        if (class_exists(\App\Models\SourceMaterial::class) && Schema::hasTable('source_materials')) {
            $sourceMaterials = \App\Models\SourceMaterial::query()
                ->orderBy('title')
                ->get();
        }

        return view('admin.questions.index', [
            'questions' => $questions,
            'search' => $search,
            'status' => 'draft',
            'difficulty' => $difficulty,
            'corporationId' => $corporationId,
            'subjectId' => $subjectId,
            'sourceMaterialId' => $sourceMaterialId,
            'corporations' => Corporation::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'sourceMaterials' => $sourceMaterials,
            'isDraftView' => true,
            'pageTitle' => 'Rascunhos de questões',
        ]);
    }
}
