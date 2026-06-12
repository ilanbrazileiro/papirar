<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionImportBatch;
use App\Services\Questions\QuestionCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuestionImportReviewController extends Controller
{
    public function review(QuestionImportBatch $batch)
    {
        $batch->load([
            'user',
            'rows' => fn ($query) => $query->orderBy('row_number'),
            'rows.duplicateQuestion',
            'rows.createdQuestion',
        ]);

        return view('admin.question_import_batches.review', compact('batch'));
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
                ->route('admin.question-import-batches.show', $batch)
                ->with('success', $result['message']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(QuestionImportBatch $batch): RedirectResponse
    {
        if (in_array($batch->status, ['imported', 'partial_imported'], true)) {
            return back()->with('error', 'Este lote já possui questões importadas e não pode ser cancelado totalmente.');
        }

        $batch->update([
            'status' => 'cancelled',
            'finished_at' => now(),
        ]);

        return redirect()
            ->route('admin.question-import-batches.index')
            ->with('success', 'Lote cancelado. Nenhuma questão foi importada.');
    }
}
