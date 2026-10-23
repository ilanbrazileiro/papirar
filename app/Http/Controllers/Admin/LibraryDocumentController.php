<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LibraryDocument;
use App\Services\Library\LibraryStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class LibraryDocumentController extends Controller {
 private function courses(){return Course::query()->orderBy('title')->get(['id','title']);}
 public function index(){return view('admin.library.index',['documents'=>LibraryDocument::query()->where('type','official_exam')->with('course')->latest()->paginate(20)]);}
 public function create(){return view('admin.library.form',['document'=>null,'courses'=>$this->courses()]);}
 public function edit(LibraryDocument $document){abort_unless($document->type==='official_exam',404);return view('admin.library.form',['document'=>$document,'courses'=>$this->courses()]);}
 public function store(Request $request,LibraryStorage $storage){
  $data=$request->validate(['course_id'=>['required','exists:courses,id'],'title'=>['required','string','max:240'],'year'=>['nullable','integer','between:1900,2100'],'exam_board'=>['nullable','string','max:160'],'description'=>['nullable','string','max:5000'],'summary'=>['nullable','string','max:12000'],'source_url'=>['nullable','url','max:1500'],'pdf'=>['required','file','mimes:pdf,zip','max:51200'],'preview'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],'is_published'=>['nullable','boolean']]);
  $disk=$storage->disk();$extension=strtolower($request->file('pdf')->getClientOriginalExtension());$key='official-exams/'.Str::uuid().'.'.($extension==='zip'?'zip':'pdf');$previewKey=null;
  $stream=fopen($request->file('pdf')->getRealPath(),'rb');try{$saved=$disk->put($key,$stream);}finally{if(is_resource($stream))fclose($stream);}if(!$saved)throw new \RuntimeException('Falha ao enviar prova ao R2.');
  try{
   if($request->hasFile('preview')){$previewKey='previews/'.Str::uuid().'.'.$request->file('preview')->extension();$stream=fopen($request->file('preview')->getRealPath(),'rb');try{$saved=$disk->put($previewKey,$stream);}finally{if(is_resource($stream))fclose($stream);}if(!$saved)throw new \RuntimeException('Falha ao enviar prévia ao R2.');}
   LibraryDocument::query()->create(['course_id'=>$data['course_id'],'type'=>'official_exam','title'=>$data['title'],'year'=>$data['year']??null,'exam_board'=>$data['exam_board']??null,'description'=>$data['description']??null,'summary'=>$data['summary']??null,'source_url'=>$data['source_url']??null,'file_key'=>$key,'file_name'=>$request->file('pdf')->getClientOriginalName(),'file_size'=>$request->file('pdf')->getSize(),'preview_key'=>$previewKey,'is_published'=>(bool)($data['is_published']??false)]);
  }catch(\Throwable $e){$disk->delete(array_filter([$key,$previewKey]));throw $e;}
  return redirect()->route('admin.library.exams.index')->with('success','Arquivo da prova cadastrado no R2.');
 }
 public function update(Request $request,LibraryDocument $document,LibraryStorage $storage){
  abort_unless($document->type==='official_exam',404);
  $data=$request->validate(['course_id'=>['required','integer','exists:courses,id'],'title'=>['required','string','max:240'],'year'=>['nullable','integer','between:1900,2100'],'exam_board'=>['nullable','string','max:160'],'description'=>['nullable','string','max:5000'],'summary'=>['nullable','string','max:12000'],'source_url'=>['nullable','url','max:1500'],'is_published'=>['required','boolean'],'preview'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120']]);
  $newPreviewKey=null;
  if($request->hasFile('preview')){$newPreviewKey='previews/'.Str::uuid().'.'.$request->file('preview')->extension();$stream=fopen($request->file('preview')->getRealPath(),'rb');try{$saved=$storage->disk()->put($newPreviewKey,$stream);}finally{if(is_resource($stream))fclose($stream);}if(!$saved)return back()->withErrors(['preview'=>'Não foi possível armazenar a prévia no R2.']);$data['preview_key']=$newPreviewKey;}
  unset($data['preview']);try{$document->update($data);}catch(\Throwable $e){if($newPreviewKey)$storage->disk()->delete($newPreviewKey);throw $e;}
  return redirect()->route('admin.library.exams.index')->with('success','Prova atualizada.');
 }
 public function destroy(LibraryDocument $document,LibraryStorage $storage){abort_unless($document->type==='official_exam',404);$document->update(['is_published'=>false]);return back()->with('success','Prova despublicada, arquivo preservado.');}
}
