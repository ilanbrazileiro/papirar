<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Question;
use App\Models\SourceMaterial;
use App\Models\Subject;
use Illuminate\Http\Request;

class QuestionDraftController extends Controller
{
    public function __invoke(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $difficulty = trim((string) $request->get('difficulty', ''));
        $corporationId = $request->integer('corporation_id');
        $subjectId = $request->integer('subject_id');
        $sourceMaterialId = $request->integer('source_material_id');

        $questions = Question::query()
            ->with(['corporation', 'exam', 'subject', 'topic', 'sourceMaterial'])
            ->where('status', 'draft')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('statement', 'like', "%{$search}%")
                        ->orWhere('source_reference', 'like', "%{$search}%")
                        ->orWhereHas('sourceMaterial', function ($sourceQuery) use ($search) {
                            $sourceQuery->where('title', 'like', "%{$search}%")
                                ->orWhere('reference_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($difficulty !== '', fn ($q) => $q->where('difficulty', $difficulty))
            ->when($corporationId, fn ($q) => $q->where('corporation_id', $corporationId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->when($sourceMaterialId, fn ($q) => $q->where('source_material_id', $sourceMaterialId))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

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
            'sourceMaterials' => SourceMaterial::query()
                ->with(['corporation', 'subject'])
                ->orderBy('title')
                ->get(),
            'isDraftPage' => true,
        ]);
    }
}
