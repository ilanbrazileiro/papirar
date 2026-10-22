<?php
namespace App\Http\Controllers\Site;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LibraryDocument;
use App\Services\Library\CommentedMaterialScope;
use App\Services\Library\LibraryDownloadAccess;
use App\Services\Library\LibraryStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
class CommentedMaterialController extends Controller
{
    public function index(Request $request)
    {
        $courseId=$request->integer('course');
        $courses=Course::query()->where('active',true)->where('is_public',true)->orderBy('title')->get();
        $documents=$this->visible()->with(['subject','topics'])
            ->when($courseId, function ($query) use ($courseId) {
                $course=Course::query()->where('active',true)->where('is_public',true)->find($courseId);
                if (!$course) { $query->whereRaw('1=0');return; }
                $scope=app(CommentedMaterialScope::class)->forCourse($course);
                $query->whereIn('subject_id',$scope['subjects'])
                    ->whereHas('topics',fn($q)=>$q->whereIn('topics.id',$scope['topics']));
            })
            ->when($request->filled('q'),fn($q)=>$q->where('title','like','%'.addcslashes(trim((string)$request->query('q')),'%_\\').'%'))
            ->latest('id')->paginate(12)->withQueryString();
        return view('site.commented-materials.index',compact('documents','courses','courseId'));
    }
    public function show(LibraryDocument $document,string $slug)
    {
        $this->assertVisible($document);
        $expected=Str::slug($document->title)?:'material-comentado';
        if ($slug!==$expected) return redirect()->route('library.materials.show',[$document->id,$expected],301);
        $document->load(['subject','topics']);
        $related=$this->visible()->where('subject_id',$document->subject_id)->whereKeyNot($document->id)->latest('id')->limit(4)->get();
        $canDownload=app(LibraryDownloadAccess::class)->allowed($document,auth()->user());
        $courseIds=app(CommentedMaterialScope::class)->matchingCourseIds($document);
        $relatedCourses=Course::query()->whereIn('id',$courseIds)->orderBy('title')->get();
        return view('site.commented-materials.show',compact('document','related','canDownload','relatedCourses'));
    }
    public function preview(LibraryDocument $document,LibraryStorage $storage):StreamedResponse
    {
        $this->assertVisible($document);abort_unless($document->preview_key,404);
        $disk=$storage->disk();abort_unless($disk->exists($document->preview_key),404);
        $stream=$disk->readStream($document->preview_key);abort_unless(is_resource($stream),404);
        $mime=match(strtolower(pathinfo($document->preview_key,PATHINFO_EXTENSION))){'png'=>'image/png','webp'=>'image/webp',default=>'image/jpeg'};
        return response()->stream(function()use($stream){fpassthru($stream);fclose($stream);},200,[
            'Content-Type'=>$mime,'Content-Disposition'=>'inline','Cache-Control'=>'public, max-age=3600','X-Content-Type-Options'=>'nosniff',
        ]);
    }
    public function download(Request $request,LibraryDocument $document,LibraryStorage $storage)
    {
        $this->assertVisible($document);
        abort_unless(app(LibraryDownloadAccess::class)->allowed($document,$request->user()),403);
        $disk=$storage->disk();abort_unless($disk->exists($document->file_key),404);
        $stream=$disk->readStream($document->file_key);abort_unless(is_resource($stream),404);
        $fileName=str_replace(["\r","\n",'"'],'',basename($document->file_name));
        return response()->streamDownload(function()use($stream){fpassthru($stream);fclose($stream);},$fileName,[
            'Content-Type'=>'application/pdf','Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff',
        ]);
    }
    private function visible()
    {
        return LibraryDocument::query()->where('type','commented_pdf')->where('is_published',true)
            ->whereNotNull('subject_id')->whereHas('topics');
    }
    private function assertVisible(LibraryDocument $document):void
    {
        abort_unless($document->type==='commented_pdf'&&$document->is_published&&$document->subject_id&&$document->topics()->exists(),404);
    }
}
