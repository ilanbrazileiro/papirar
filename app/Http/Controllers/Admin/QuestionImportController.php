<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAiQuestionImport;
use App\Models\Corporation;
use App\Models\Exam;
use App\Models\ExamBoard;
use App\Models\QuestionImportBatch;
use App\Models\SourceMaterial;
use App\Services\Questions\QuestionCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionImportController extends Controller
{
    public function create()
    {
        return view('admin.questions.import.create', [
            'corporations' => Corporation::query()->where('active', true)->orderBy('name')->get(['id', 'name']),
            'exams' => Exam::query()->where('active', true)->with('corporation:id,name')->orderByDesc('year')->orderBy('title')->get(),
            'examBoards' => ExamBoard::query()->where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeAi(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'corporation_id' => ['nullable', 'integer', 'exists:corporations,id'],
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'source_type' => ['required', Rule::in(['exam', 'authored', 'adapted'])],
            'exam_board_id' => ['nullable', 'integer', 'exists:exam_boards,id'],
            'exam_year' => [Rule::requiredIf($request->input('source_type') === 'exam'), 'nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'exam_reference' => [Rule::requiredIf($request->input('source_type') === 'exam'), 'nullable', 'string', 'max:180'],
            'source_file' => ['required', 'file', 'mimes:pdf,docx,jpg,jpeg,png', 'max:15360'],
            'answer_file' => ['nullable', 'file', 'mimes:pdf,docx,jpg,jpeg,png', 'max:8192'],
        ], [
            'source_file.required' => 'Envie o arquivo da prova ou documento com as questões.',
            'source_file.mimes' => 'A prova deve estar em PDF, DOCX, JPG ou PNG.',
            'answer_file.mimes' => 'O gabarito deve estar em PDF, DOCX, JPG ou PNG.',
        ]);

        $isAuthored = $data['source_type'] === 'authored';
        $exam = !$isAuthored && !empty($data['exam_id']) ? Exam::query()->findOrFail($data['exam_id']) : null;

        if ($exam && !empty($data['corporation_id']) && (int) $exam->corporation_id !== (int) $data['corporation_id']) {
            return back()->withInput()->with('error', 'A prova selecionada não pertence à corporação informada.');
        }

        $corporationId = $data['corporation_id'] ?? $exam?->corporation_id;

        $sourceSize = (int) $request->file('source_file')->getSize();
        $answerSize = $request->file('answer_file') ? (int) $request->file('answer_file')->getSize() : 0;

        if (($sourceSize + $answerSize) > (18 * 1024 * 1024)) {
            return back()->withInput()->with('error', 'A soma dos arquivos não pode ultrapassar 18 MB nesta versão do importador.');
        }

        $batch = QuestionImportBatch::query()->create([
            'user_id' => (int) auth()->id(),
            'import_type' => 'ai',
            'corporation_id' => $corporationId,
            'exam_id' => $isAuthored ? null : ($data['exam_id'] ?? null),
            'exam_board_id' => $isAuthored ? null : ($data['exam_board_id'] ?? null),
            'exam_year' => $isAuthored ? null : ($data['exam_year'] ?? null),
            'exam_reference' => $isAuthored ? null : (isset($data['exam_reference']) ? trim($data['exam_reference']) : null),
            'source_type' => $data['source_type'],
            'filename' => $request->file('source_file')->hashName(),
            'original_filename' => $request->file('source_file')->getClientOriginalName(),
            'answer_original_filename' => $request->file('answer_file')?->getClientOriginalName(),
            'status' => 'uploaded',
            'started_at' => now(),
        ]);

        try {
            $directory = 'question-imports/'.$batch->id;
            $sourcePath = $request->file('source_file')->store($directory, 'local');
            $answerPath = $request->file('answer_file')?->store($directory, 'local');

            $batch->update([
                'source_file_path' => $sourcePath,
                'answer_file_path' => $answerPath,
                'status' => 'validating',
            ]);

            ProcessAiQuestionImport::dispatch($batch->id);

            return redirect()
                ->route('admin.question-import-batches.show', $batch)
                ->with('success', 'Arquivos recebidos. A extração foi enviada para processamento.');
        } catch (\Throwable $exception) {
            $batch->update([
                'status' => 'failed',
                'processing_error' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            return back()->withInput()->with('error', 'Não foi possível iniciar a extração: '.$exception->getMessage());
        }
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

    public function storeDirect(Request $request, QuestionCsvImportService $importService): RedirectResponse
    {
        $data = $request->validate([
            'csv_content' => ['required', 'string', 'min:20'],
        ], [
            'csv_content.required' => 'Cole o conteúdo do CSV antes de analisar.',
            'csv_content.min' => 'O conteúdo colado parece incompleto. Cole o cabeçalho e pelo menos uma linha de questão.',
        ]);

        try {
            $batch = $importService->createPreviewFromText(
                $data['csv_content'],
                (int) auth()->id(),
                'csv_colado_'.now()->format('Ymd_His').'.csv'
            );

            return redirect()
                ->route('admin.question-import-batches.review', $batch)
                ->with('success', 'CSV colado analisado. Revise as linhas antes de confirmar a importação.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Não foi possível analisar o CSV colado: '.$e->getMessage());
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
