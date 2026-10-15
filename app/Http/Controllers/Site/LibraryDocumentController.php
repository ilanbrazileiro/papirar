<?php
namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Library\LibraryDownloadAccess;
use App\Models\LibraryDocument;
use App\Services\Library\LibraryStorage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LibraryDocumentController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $base = LibraryDocument::query()->where('type', 'official_exam')->where('is_published', true)
            ->whereHas('course', fn ($q) => $q->where('active', true)->where('is_public', true));
        $courses = \App\Models\Course::query()->whereIn('id', LibraryDocument::query()->where('type','official_exam')->where('is_published', true)->select('course_id'))->where('active', true)->where('is_public', true)->orderBy('title')->get();
        $courseId = $request->integer('course');
        $documents = $base->with('course')->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($request->query('q'))).'%'))
            ->orderByDesc('year')->orderByDesc('id')->paginate(12)->withQueryString();
        return view('site.library.index', compact('documents', 'courses', 'courseId'));
    }

    public function show(LibraryDocument $document, string $slug)
    {
        $this->assertVisible($document);
        $canonicalSlug = $document->publicSlug();
        if ($slug !== $canonicalSlug) {
            return redirect()->route('library.exams.show', ['document' => $document->id, 'slug' => $canonicalSlug], 301);
        }
        $document->load('course');
        $related = LibraryDocument::query()->where('type','official_exam')->where('is_published',true)
            ->where('course_id',$document->course_id)->whereKeyNot($document->id)
            ->latest('year')->limit(5)->get();
        $canDownload = $this->canDownload($document, auth()->user());
        return view('site.library.show', compact('document', 'related', 'canDownload'));
    }

    private function canDownload(LibraryDocument $document, $user): bool
    {
        return app(LibraryDownloadAccess::class)->allowed($document, $user);
    }

    public function preview(LibraryDocument $document, LibraryStorage $storage): StreamedResponse
    {
        $this->assertVisible($document);
        abort_unless($document->preview_key,404);
        $disk=$storage->disk();
        abort_unless($disk->exists($document->preview_key),404);
        $stream=$disk->readStream($document->preview_key);
        abort_unless(is_resource($stream),404);
        $mime=match(strtolower(pathinfo($document->preview_key, PATHINFO_EXTENSION))) {
            'png'=>'image/png','webp'=>'image/webp',default=>'image/jpeg'
        };
        return response()->stream(function () use ($stream) { fpassthru($stream); fclose($stream); },200,[
            'Content-Type'=>$mime,'Content-Disposition'=>'inline','Cache-Control'=>'public, max-age=3600',
            'X-Content-Type-Options'=>'nosniff',
        ]);
    }

    public function download(Request $request, LibraryDocument $document, LibraryStorage $storage)
    {
        $this->assertVisible($document);
        $user=$request->user();
        abort_unless($this->canDownload($document, $user), 403,
            'Download exclusivo para assinantes pagos ativos ou acessos manuais expressamente autorizados.');
        $disk=$storage->disk();
        abort_unless($disk->exists($document->file_key),404);
        // Não expõe credenciais R2 nem gera URL pública permanente.
        $stream=$disk->readStream($document->file_key);
        abort_unless(is_resource($stream),404);
        $filename=str_replace(['"','\r','\n'], '', $document->file_name);
        return response()->streamDownload(function () use ($stream) { fpassthru($stream); fclose($stream); },$filename,[
            'Content-Type'=>'application/pdf','Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff'
        ]);
    }

    private function assertVisible(LibraryDocument $document): void
    {
        abort_unless($document->type==='official_exam' && $document->is_published &&
            $document->course && $document->course->active && $document->course->is_public,404);
    }
}
