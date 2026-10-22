<?php
namespace App\Console\Commands;
use App\Models\TopicVideoLesson;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
class CheckTopicVideos extends Command {
 protected $signature='videos:check-topic';
 protected $description='Consulta disponibilidade e incorporação dos vídeos YouTube da biblioteca';
 public function handle():int{
  $key=config('services.youtube.api_key')?:env('YOUTUBE_DATA_API_KEY');
  if(!$key){$this->error('Configure YOUTUBE_DATA_API_KEY antes de executar.');return self::FAILURE;}
  TopicVideoLesson::where('origin','youtube')->whereNotNull('youtube_id')->orderBy('id')->chunk(50,function($items)use($key){
   try{$response=Http::timeout(15)->get('https://www.googleapis.com/youtube/v3/videos',['part'=>'status','id'=>$items->pluck('youtube_id')->implode(','),'key'=>$key]);if(!$response->successful()){$this->warn('Falha de API: '.$response->status());return;}
    $found=collect($response->json('items',[]))->keyBy('id');
    foreach($items as $video){$v=$found->get($video->youtube_id);$state=$v?(($v['status']['embeddable']??false)&&($v['status']['privacyStatus']??'')==='public'?'ok':'review'):'not_found';$video->update(['last_checked_at'=>now(),'availability'=>$state]);if($state!=='ok')$this->warn('Revisar vídeo #'.$video->id.' — '.$state);}
   }catch(\Throwable $e){$this->error('Falha de rede: '.$e->getMessage());}
  });
  return self::SUCCESS;
 }
}
