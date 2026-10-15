<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LibraryDocument;
use App\Services\Library\LibraryStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommentedMaterialController extends Controller
{
    public function index()
    {
        return view('admin.commented-materials.index', [
            'courses' => Course::query()->orderBy('title')->get(['id', 'title']),
            'documents' => LibraryDocument::query()->where('type', 'commented_pdf')
                ->with('course')->latest('id')->paginate(20),
        ]);
    }

    public function store(Request $request, LibraryStorage $storage)
    {
        $data = $request->validate($this->rules(true));
        $disk = $storage->disk();
        $fileKey = 'commented-materials/'.Str::uuid().'.pdf';
        $previewKey = null;

        $this->upload($disk, $request->file('pdf'), $fileKey);
        try {
            if ($request->hasFile('preview')) {
                $previewKey = 'previews/'.Str::uuid().'.'.$request->file('preview')->extension();
                $this->upload($disk, $request->file('preview'), $previewKey);
            }
            LibraryDocument::query()->create([
                'course_id' => $data['course_id'],
                'type' => 'commented_pdf',
                'title' => $data['title'],
                'year' => $data['year'] ?? null,
                'exam_board' => null,
                'description' => $data['description'] ?? null,
                'summary' => $data['summary'] ?? null,
                'source_url' => $data['source_url'] ?? null,
                'file_key' => $fileKey,
                'file_name' => $request->file('pdf')->getClientOriginalName(),
                'file_size' => $request->file('pdf')->getSize(),
                'preview_key' => $previewKey,
                'is_published' => (bool) ($data['is_published'] ?? false),
            ]);
        } catch (\Throwable $e) {
            $disk->delete(array_filter([$fileKey, $previewKey]));
            throw $e;
        }

        return redirect()->route('admin.library.materials.index')->with('success', 'PDF comentado cadastrado.');
    }

    public function edit(LibraryDocument $document)
    {
        $this->assertType($document);
        return view('admin.commented-materials.edit', [
            'document' => $document,
            'courses' => Course::query()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, LibraryDocument $document, LibraryStorage $storage)
    {
        $this->assertType($document);
        $data = $request->validate($this->rules(false));
        $previewKey = null;
        if ($request->hasFile('preview')) {
            $previewKey = 'previews/'.Str::uuid().'.'.$request->file('preview')->extension();
            $this->upload($storage->disk(), $request->file('preview'), $previewKey);
            $data['preview_key'] = $previewKey;
        }
        unset($data['preview']);
        try {
            $document->update($data);
        } catch (\Throwable $e) {
            if ($previewKey) $storage->disk()->delete($previewKey);
            throw $e;
        }
        return redirect()->route('admin.library.materials.index')->with('success', 'Material atualizado.');
    }

    public function unpublish(LibraryDocument $document)
    {
        $this->assertType($document);
        $document->update(['is_published' => false]);
        return back()->with('success', 'Material despublicado; PDF preservado no R2.');
    }

    private function assertType(LibraryDocument $document): void
    {
        abort_unless($document->type === 'commented_pdf', 404);
    }

    private function rules(bool $creating): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:240'],
            'year' => ['nullable', 'integer', 'between:1900,2100'],
            'description' => ['required', 'string', 'min:40', 'max:5000'],
            'summary' => ['nullable', 'string', 'max:12000'],
            'source_url' => ['nullable', 'url', 'max:1500'],
            'pdf' => $creating ? ['required', 'file', 'mimes:pdf', 'max:51200'] : ['prohibited'],
            'preview' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_published' => [$creating ? 'nullable' : 'required', 'boolean'],
        ];
    }

    private function upload($disk, $file, string $key): void
    {
        $stream = fopen($file->getRealPath(), 'rb');
        try {
            $saved = $disk->put($key, $stream);
        } finally {
            if (is_resource($stream)) fclose($stream);
        }
        if (! $saved) {
            throw new \RuntimeException('Falha ao enviar arquivo para o R2.');
        }
    }
}
