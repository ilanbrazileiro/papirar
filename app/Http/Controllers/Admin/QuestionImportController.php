<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Questions\QuestionCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

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

    public function downloadTopicsCsv(): StreamedResponse
    {
        $filename = 'papirar_subjects_topics_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 para abrir corretamente no Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Cabeçalho do CSV
            fputcsv($handle, [
                'subject_id',
                'subject_name',
                'topic_id',
                'topic_name',
            ], ';');

            $rows = DB::table('topics')
                ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
                ->select(
                    'subjects.id as subject_id',
                    'subjects.name as subject_name',
                    'topics.id as topic_id',
                    'topics.name as topic_name'
                )
                ->orderBy('subjects.name')
                ->orderBy('topics.name')
                ->get();

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->subject_id,
                    $row->subject_name,
                    $row->topic_id,
                    $row->topic_name,
                ], ';');
            }

            fclose($handle);
        }, $filename, $headers);
    }
}
