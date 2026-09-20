<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionImportBatch;
use App\Models\QuestionImportBatchRow;
use App\Models\Subject;
use App\Services\Questions\QuestionCsvImportService;
use App\Services\Questions\QuestionImportFileCleanupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuestionImportReviewController extends Controller
{
    public function review(Request $request, QuestionImportBatch $batch)
    {
        $statusFilter = $request->query('status');
        $allowedStatuses = ['valid', 'duplicate', 'error', 'imported', 'ignored'];

        $batch->load(['user']);

        $rows = $batch->rows()
            ->with(['duplicateQuestion', 'createdQuestion'])
            ->when(in_array($statusFilter, $allowedStatuses, true), function ($query) use ($statusFilter) {
                $query->where('status', $statusFilter);
            })
            ->orderBy('row_number')
            ->paginate(50)
            ->withQueryString();

        $statusCounts = $batch->rows()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.question_import_batches.review', compact('batch', 'rows', 'statusFilter', 'statusCounts'));
    }

    public function confirm(Request $request, QuestionImportBatch $batch, QuestionCsvImportService $importService): RedirectResponse
    {
        $data = $request->validate([
            'row_ids' => ['nullable', 'array'],
            'row_ids.*' => ['integer'],
        ]);

        try {
            $result = $importService->importApprovedRows(
                $batch,
                $data['row_ids'] ?? null,
                (int) auth()->id()
            );

            return redirect()
                ->route('admin.question-import-batches.review', $batch)
                ->with('success', $result['message']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function editRow(QuestionImportBatch $batch, QuestionImportBatchRow $row)
    {
        abort_unless((int) $row->batch_id === (int) $batch->id, 404);

        $subjects = Subject::query()
            ->where('active', true)
            ->with(['topics' => fn ($query) => $query->where('active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('admin.question_import_batches.edit-row', compact('batch', 'row', 'subjects'));
    }

    public function updateRow(
        Request $request,
        QuestionImportBatch $batch,
        QuestionImportBatchRow $row,
        QuestionCsvImportService $importService
    ): RedirectResponse {
        abort_unless((int) $row->batch_id === (int) $batch->id, 404);

        $data = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'statement' => ['required', 'string'],
            'correct_letter' => ['required', 'in:A,B,C,D,E'],
            'alternatives' => ['required', 'array', 'size:5'],
            'alternatives.A' => ['required', 'string'],
            'alternatives.B' => ['required', 'string'],
            'alternatives.C' => ['required', 'string'],
            'alternatives.D' => ['required', 'string'],
            'alternatives.E' => ['required', 'string'],
        ]);

        $importService->updatePreviewRow($batch, $row, $data);

        return redirect()
            ->route('admin.question-import-batches.review', $batch)
            ->with('success', "Questão original {$row->row_number} revalidada.");
    }

    public function importRow(QuestionImportBatch $batch, QuestionImportBatchRow $row, QuestionCsvImportService $importService): RedirectResponse
    {
        if ((int) $row->batch_id !== (int) $batch->id) {
            abort(404);
        }

        if ($row->status !== 'valid') {
            return back()->with('error', 'Somente linhas válidas podem ser importadas individualmente.');
        }

        try {
            $result = $importService->importApprovedRows($batch, [$row->id], (int) auth()->id());
            return back()->with('success', $result['message']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function ignoreRow(QuestionImportBatch $batch, QuestionImportBatchRow $row, QuestionCsvImportService $importService): RedirectResponse
    {
        if ((int) $row->batch_id !== (int) $batch->id) {
            abort(404);
        }

        if (in_array($row->status, ['imported', 'ignored'], true)) {
            return back()->with('error', 'Esta linha não pode ser ignorada nesse status.');
        }

        $row->update([
            'status' => 'ignored',
            'error_message' => $row->error_message ?: 'Linha ignorada manualmente pelo usuário.',
        ]);

        $importService->refreshBatchCounters($batch);

        return back()->with('success', "Linha {$row->row_number} ignorada com sucesso.");
    }

    public function cancel(
        QuestionImportBatch $batch,
        QuestionImportFileCleanupService $fileCleanup
    ): RedirectResponse
    {
        if (in_array($batch->status, ['imported', 'partial_imported', 'partial'], true)) {
            return back()->with('error', 'Este lote já possui questões importadas e não pode ser cancelado totalmente.');
        }

        $batch->update([
            'status' => 'cancelled',
            'finished_at' => now(),
        ]);

        try {
            $fileCleanup->deleteFiles($batch->fresh());
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()
            ->route('admin.question-import-batches.index')
            ->with('success', 'Lote cancelado. Nenhuma questão foi importada.');
    }
}
