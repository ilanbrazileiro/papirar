<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionImportBatch;
use Illuminate\Http\Request;

class QuestionImportBatchController extends Controller
{
    public function index(Request $request)
    {
        $query = QuestionImportBatch::query()
            ->with('user')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = '%' . trim((string) $request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('original_filename', 'like', $search)
                    ->orWhere('filename', 'like', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
            });
        }

        $batches = $query->paginate(20)->withQueryString();

        return view('admin.question_import_batches.index', compact('batches'));
    }

    public function show(QuestionImportBatch $questionImportBatch)
    {
        $questionImportBatch->load('user');

        $rows = $questionImportBatch->rows()
            ->with(['createdQuestion.subject', 'createdQuestion.topic', 'duplicateQuestion.subject', 'duplicateQuestion.topic'])
            ->orderBy('row_number')
            ->paginate(50);

        return view('admin.question_import_batches.show', [
            'batch' => $questionImportBatch,
            'rows' => $rows,
        ]);
    }
}
