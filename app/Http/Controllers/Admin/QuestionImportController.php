<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SourceMaterial;
use App\Services\Questions\QuestionCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        ]);

        try {
            $batch = $importService->createPreview($request->file('file'), (int) auth()->id());

            return redirect()
                ->route('admin.question-import-batches.review', $batch)
                ->with('success', 'Arquivo analisado. Revise as linhas antes de confirmar a importação.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Não foi possível analisar o CSV: '.$e->getMessage());
        }
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
        $filename = 'papirar_subjects_topics_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['subject_id', 'subject_name', 'topic_id', 'topic_name'], ';');

            $rows = DB::table('topics')
                ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
                ->select('subjects.id as subject_id', 'subjects.name as subject_name', 'topics.id as topic_id', 'topics.name as topic_name')
                ->orderBy('subjects.name')
                ->orderBy('topics.name')
                ->get();

            foreach ($rows as $row) {
                fputcsv($handle, [$row->subject_id, $row->subject_name, $row->topic_id, $row->topic_name], ';');
            }

            fclose($handle);
        }, $filename, $headers);
    }

    public function downloadSourceMaterialsCsv(): StreamedResponse
    {
        $filename = 'papirar_source_materials_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'source_material_id',
                'title',
                'corporation_id',
                'corporation_name',
                'subject_id',
                'subject_name',
                'material_type',
                'year',
                'reference_code',
                'active',
            ], ';');

            $rows = SourceMaterial::query()
                ->with(['corporation', 'subject'])
                ->orderBy('title')
                ->get();

            foreach ($rows as $material) {
                fputcsv($handle, [
                    $material->id,
                    $material->title,
                    $material->corporation_id,
                    optional($material->corporation)->name,
                    $material->subject_id,
                    optional($material->subject)->name,
                    $material->material_type,
                    $material->year,
                    $material->reference_code,
                    $material->active ? 1 : 0,
                ], ';');
            }

            fclose($handle);
        }, $filename, $headers);
    }
}
