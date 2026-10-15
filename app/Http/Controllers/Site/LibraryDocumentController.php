<?php
namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CourseAccess;
use App\Models\LibraryDocument;
use App\Services\Library\LibraryStorage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LibraryDocumentController extends Controller
{
    public function index()
    {
        return view('site.library.index', ['documents'=>LibraryDocument::query()
            ->where('type','official_exam')->where('is_published',true)
            ->whereHas('course',fn($q)=>$q->where('active',true)->where('is_public',true))
            ->with('course')->orderByDesc('year')->paginate(15)]);
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
        abort_unless($user && CourseAccess::query()->where('user_id',$user->id)
            ->where('course_id',$document->course_id)->where('access_type',CourseAccess::TYPE_PAID)
            ->where('status',CourseAccess::STATUS_ACTIVE)
            ->where(fn($q)=>$q->whereNull('ends_at')->orWhere('ends_at','>=',now()))->exists(),403,
            'Download exclusivo para assinantes com acesso pago ativo ao curso.');
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
