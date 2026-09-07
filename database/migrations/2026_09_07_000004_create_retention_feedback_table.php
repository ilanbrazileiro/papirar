<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('retention_feedback',function(Blueprint $table){$table->id();$table->foreignId('user_id')->constrained()->cascadeOnDelete();$table->foreignId('course_id')->constrained()->cascadeOnDelete();$table->foreignId('course_access_id')->constrained('course_accesses')->cascadeOnDelete();$table->string('reason',40);$table->string('stage',40)->default('non_renewal');$table->text('notes')->nullable();$table->timestamps();$table->unique(['user_id','course_access_id','stage'],'retention_feedback_unique');}); }
    public function down(): void { Schema::dropIfExists('retention_feedback'); }
};
