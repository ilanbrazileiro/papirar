<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('library_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();
            $table->string('type', 30)->default('official_exam');
            $table->string('title', 240);
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('exam_board', 160)->nullable();
            $table->text('description')->nullable();
            $table->text('summary')->nullable();
            $table->string('source_url', 1500)->nullable();
            $table->string('file_key', 512);
            $table->string('file_name', 255);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('preview_key', 512)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->index(['course_id', 'type', 'is_published']);
        });
    }
    public function down(): void { Schema::dropIfExists('library_documents'); }
};
