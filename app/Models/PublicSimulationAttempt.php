<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PublicSimulationAttempt extends Model {
 protected $fillable=['public_simulation_id','user_id','visitor_hash','attempt_token_hash','answers_json','started_at','completed_at','correct_answers','question_count','source'];
 protected $casts=['started_at'=>'datetime','completed_at'=>'datetime','correct_answers'=>'integer','question_count'=>'integer','answers_json'=>'array'];
 public function simulation():BelongsTo{return $this->belongsTo(PublicSimulation::class,'public_simulation_id');}
}
