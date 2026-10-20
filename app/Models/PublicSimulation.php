<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class PublicSimulation extends Model {
 protected $fillable=['course_id','title','slug','description','duration_minutes','is_published'];
 protected $casts=['is_published'=>'boolean','duration_minutes'=>'integer'];
 public function attempts():HasMany{return $this->hasMany(PublicSimulationAttempt::class,'public_simulation_id');}
 public function course():BelongsTo{return $this->belongsTo(Course::class);}
 public function questions():BelongsToMany{return $this->belongsToMany(Question::class,'public_simulation_questions')->withPivot('position')->orderBy('public_simulation_questions.position');}
}
