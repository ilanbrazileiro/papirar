<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Questions\QuestionCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionImportController extends Controller
{
    public function create()
    {
        return view('admin.questions.import.create');
    }

    public function store(Request $request, QuestionCsvImportService $importService): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'dry_run' => ['nullable', 'boolean'],
        ]);

        $result = $importService->import(
            $request->file('file')->getRealPath(),
            (bool) ($data['dry_run'] ?? false),
            auth()->id()
        );

        return back()
            ->with($result['success'] ? 'success' : 'error', $result['message'])
            ->with('import_report', $result);
    }

    public function downloadTemplate(): StreamedResponse
    {
        return Storage::disk('local')->download(
            'templates/questions_import_template.csv',
            'questions_import_template.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }
}
