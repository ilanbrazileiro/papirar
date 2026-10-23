<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\LibraryDocument;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\Library\LibraryStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class CommentedMaterialController extends Controller {
 private function fields():array{return ['subjects'=>Subject::query()->where('active',true)->orderBy('name')->get(['id','name']),'topics'=>Topic::query()->where('active',true)->orderBy('name')->get(['id','subject_id','name'])];}
 public function index(){return view('admin.commented-materials.index',['documents'=>LibraryDocument::query()->where('type','commented_pdf')->with(['subject','topics'])->latest('id')->paginate(20)]);}
 public function create(){return view('admin.commented-materials.form',array_merge($this->fields(),['document'=>null]));}
 public function edit(LibraryDocument $document){$this->assertType($document);return view('admin.commented-materials.form',array_merge($this->fields(),['document'=>$document->load('topics')]));}
 public function store(Request $request,LibraryStorage $storage){
  $data=$request->validate($this->rules(true));$disk=$storage->disk();$fileKey='commented-materials/'.Str::uuid().'.pdf';$previewKey=null;
  $this->upload($disk,$request->file('pdf'),$fileKey);
  try{
   if($request->hasFile('preview')){$previewKey='previews/'.Str::uuid().'.'.$request->file('preview')->extension();$this->upload($disk,$request->file('preview'),$previewKey);}
   DB::transaction(function()use($data,$request,$fileKey,$previewKey){
    $document=LibraryDocument::query()->create(['course_id'=>null,'subject_id'=>$data['subject_id'],'type'=>'commented_pdf','title'=>$data['title'],'year'=>$data['year']??null,'exam_board'=>null,'description'=>$data['description'],'summary'=>$data['summary']??null,'source_url'=>$data['source_url']??null,'file_key'=>$fileKey,'file_name'=>$request->file('pdf')->getClientOriginalName(),'file_size'=>$request->file('pdf')->getSize(),'preview_key'=>$previewKey,'is_published'=>(bool)($data['is_published']??false)]);
    $document->topics()->sync($data['topic_ids']);
   });
  }catch(\Throwable $e){$disk->delete(array_filter([$fileKey,$previewKey]));throw $e;}
  return redirect()->route('admin.library.materials.index')->with('success','Material acadêmico cadastrado.');
 }
 public function update(Request $request,LibraryDocument $document,LibraryStorage $storage){
  $this->assertType($document);$data=$request->validate($this->rules(false));$previewKey=null;
  if($request->hasFile('preview')){$previewKey='previews/'.Str::uuid().'.'.$request->file('preview')->extension();$this->upload($storage->disk(),$request->file('preview'),$previewKey);$data['preview_key']=$previewKey;}
  unset($data['preview'],$data['pdf']);
  try{DB::transaction(function()use($document,$data){$topics=$data['topic_ids'];unset($data['topic_ids']);$data['course_id']=null;$document->update($data);$document->topics()->sync($topics);});}
  catch(\Throwable $e){if($previewKey)$storage->disk()->delete($previewKey);throw $e;}
  return redirect()->route('admin.library.materials.index')->with('success','Material atualizado.');
 }
 public function unpublish(LibraryDocument $document){$this->assertType($document);$document->update(['is_published'=>false]);return back()->with('success','Material despublicado; arquivo preservado no R2.');}
 private function assertType(LibraryDocument $document):void{abort_unless($document->type==='commented_pdf',404);}
 private function rules(bool $creating):array{return [
  'subject_id'=>['required','integer',Rule::exists('subjects','id')->where('active',true)],
  'topic_ids'=>['required','array','min:1'],
  'topic_ids.*'=>['required','integer','distinct',Rule::exists('topics','id')->where(fn($q)=>$q->where('subject_id',request()->input('subject_id'))->where('active',true))],
  'title'=>['required','string','max:240'],'year'=>['nullable','integer','between:1900,2100'],
  'description'=>['required','string','min:40','max:5000'],'summary'=>['nullable','string','max:12000'],
  'source_url'=>['nullable','url','max:1500'],'pdf'=>$creating?['required','file','mimes:pdf','max:51200']:['prohibited'],
  'preview'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
  'is_published'=>[$creating?'nullable':'required','boolean'],
 ];}
 private function upload($disk,$file,string $key):void{$stream=fopen($file->getRealPath(),'rb');try{$saved=$disk->put($key,$stream);}finally{if(is_resource($stream))fclose($stream);}if(!$saved)throw new \RuntimeException('Falha ao enviar arquivo para o R2.');}
}
