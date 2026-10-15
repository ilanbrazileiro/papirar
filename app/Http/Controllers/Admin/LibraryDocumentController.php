<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LibraryDocument;
use App\Services\Library\LibraryStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LibraryDocumentController extends Controller
{
    public function index()
    {
        return view('admin.library.index', [
            'courses' => Course::query()->orderBy('title')->get(['id','title']),
            'documents' => LibraryDocument::query()->where('type','official_exam')->with('course')->latest()->paginate(20),
        ]);
    }

    public function store(Request $request, LibraryStorage $storage)
    {
        $data = $request->validate([
            'course_id'=>['required','exists:courses,id'], 'title'=>['required','string','max:240'],
            'year'=>['nullable','integer','between:1900,2100'], 'exam_board'=>['nullable','string','max:160'],
            'description'=>['nullable','string','max:5000'], 'summary'=>['nullable','string','max:12000'],
            'source_url'=>['nullable','url','max:1500'], 'pdf'=>['required','file','mimes:pdf','max:51200'],
            'preview'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'is_published'=>['nullable','boolean'],
        ]);
        $disk = $storage->disk();
        $key = 'official-exams/'.Str::uuid().'.pdf';
        $previewKey = null;
        $disk->put($key, fopen($request->file('pdf')->getRealPath(), 'rb'));
        try {
            if ($request->hasFile('preview')) {
                $ext = $request->file('preview')->extension();
                $previewKey = 'previews/'.Str::uuid().'.'.$ext;
                $disk->put($previewKey, fopen($request->file('preview')->getRealPath(), 'rb'));
            }
            LibraryDocument::query()->create([
                'course_id'=>$data['course_id'],'type'=>'official_exam','title'=>$data['title'],
                'year'=>$data['year']??null,'exam_board'=>$data['exam_board']??null,
                'description'=>$data['description']??null,'summary'=>$data['summary']??null,
                'source_url'=>$data['source_url']??null,'file_key'=>$key,
                'file_name'=>$request->file('pdf')->getClientOriginalName(),
                'file_size'=>$request->file('pdf')->getSize(),'preview_key'=>$previewKey,
                'is_published'=> (bool)($data['is_published']??false),
            ]);
        } catch (\Throwable $e) {
            $disk->delete(array_filter([$key,$previewKey]));
            throw $e;
        }
        return back()->with('success','Prova cadastrada no R2.');
    }

    public function update(Request $request, LibraryDocument $document)
    {
        abort_unless($document->type==='official_exam',404);
        $data=$request->validate([
            'title'=>['required','string','max:240'],'description'=>['nullable','string','max:5000'],
            'summary'=>['nullable','string','max:12000'],'is_published'=>['required','boolean'],
        ]);
        $document->update($data);
        return back()->with('success','Registro atualizado.');
    }

    public function destroy(LibraryDocument $document, LibraryStorage $storage)
    {
        abort_unless($document->type==='official_exam',404);
        // Despublicação segura: arquivos retidos no R2 para evitar exclusão acidental.
        $document->update(['is_published'=>false]);
        return back()->with('success','Prova despublicada, arquivo preservado.');
    }
}
