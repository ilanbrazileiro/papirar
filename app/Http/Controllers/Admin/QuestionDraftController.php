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
        $status = Question::STATUS_DRAFT;

        $baseQuery = Question::query()
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
            ->when($sourceMaterialId, fn ($q) => $q->where('source_material_id', $sourceMaterialId));

        $questions = (clone $baseQuery)
            ->with(['corporation', 'exam', 'subject', 'topic', 'sourceMaterial'])
            ->where('status', $status)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $statusCounts = [
            Question::STATUS_DRAFT => (clone $baseQuery)->where('status', Question::STATUS_DRAFT)->count(),
            Question::STATUS_PUBLISHED => (clone $baseQuery)->where('status', Question::STATUS_PUBLISHED)->count(),
            Question::STATUS_REVIEWED => (clone $baseQuery)->where('status', Question::STATUS_REVIEWED)->count(),
            Question::STATUS_ARCHIVED => (clone $baseQuery)->where('status', Question::STATUS_ARCHIVED)->count(),
        ];

        $totalCount = array_sum($statusCounts);
        $visibleCount = $statusCounts[Question::STATUS_PUBLISHED] + $statusCounts[Question::STATUS_REVIEWED];
        $pendingReviewCount = $statusCounts[Question::STATUS_PUBLISHED];
        $reviewedCount = $statusCounts[Question::STATUS_REVIEWED];
        $reviewProgress = $visibleCount > 0 ? round(($reviewedCount / $visibleCount) * 100) : 0;

        $statusCards = [
            [
                'key' => '',
                'title' => 'Todas',
                'count' => $totalCount,
                'description' => 'Visão geral do banco filtrado.',
                'icon' => 'fas fa-layer-group',
                'class' => 'secondary',
                'url' => route('admin.questions.index', $this->statusCardQuery($request, '')),
            ],
            [
                'key' => Question::STATUS_DRAFT,
                'title' => 'Rascunhos',
                'count' => $statusCounts[Question::STATUS_DRAFT],
                'description' => 'Ainda não aparecem para o aluno.',
                'icon' => 'fas fa-edit',
                'class' => 'danger',
                'url' => route('admin.questions.index', $this->statusCardQuery($request, Question::STATUS_DRAFT)),
            ],
            [
                'key' => Question::STATUS_PUBLISHED,
                'title' => 'Publicadas',
                'count' => $statusCounts[Question::STATUS_PUBLISHED],
                'description' => 'Aparecem para o aluno e aguardam revisão editorial.',
                'icon' => 'fas fa-eye',
                'class' => 'warning',
                'url' => route('admin.questions.index', $this->statusCardQuery($request, Question::STATUS_PUBLISHED)),
            ],
            [
                'key' => Question::STATUS_REVIEWED,
                'title' => 'Revisadas',
                'count' => $statusCounts[Question::STATUS_REVIEWED],
                'description' => 'Aparecem para o aluno e já foram validadas.',
                'icon' => 'fas fa-check-circle',
                'class' => 'success',
                'url' => route('admin.questions.index', $this->statusCardQuery($request, Question::STATUS_REVIEWED)),
            ],
            [
                'key' => Question::STATUS_ARCHIVED,
                'title' => 'Arquivadas',
                'count' => $statusCounts[Question::STATUS_ARCHIVED],
                'description' => 'Fora da área do aluno.',
                'icon' => 'fas fa-archive',
                'class' => 'secondary',
                'url' => route('admin.questions.index', $this->statusCardQuery($request, Question::STATUS_ARCHIVED)),
            ],
        ];

        return view('admin.questions.index', [
            'questions' => $questions,
            'search' => $search,
            'status' => $status,
            'difficulty' => $difficulty,
            'corporationId' => $corporationId,
            'subjectId' => $subjectId,
            'sourceMaterialId' => $sourceMaterialId,
            'corporations' => Corporation::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'sourceMaterials' => SourceMaterial::query()->with(['corporation', 'subject'])->orderBy('title')->get(),
            'statusCounts' => $statusCounts,
            'statusCards' => $statusCards,
            'totalCount' => $totalCount,
            'visibleCount' => $visibleCount,
            'pendingReviewCount' => $pendingReviewCount,
            'reviewedCount' => $reviewedCount,
            'reviewProgress' => $reviewProgress,
            'isDraftPage' => true,
        ]);
    }

    private function statusCardQuery(Request $request, string $status): array
    {
        $query = $request->except(['page', 'status']);

        if ($status !== '') {
            $query['status'] = $status;
        }

        return $query;
    }
}
