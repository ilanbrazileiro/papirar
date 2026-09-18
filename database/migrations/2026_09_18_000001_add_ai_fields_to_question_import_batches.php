<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Migration documental. Em producao, aplicar o SQL entregue com o lote. */
    public function up(): void
    {
        Schema::table('question_import_batches', function (Blueprint $table) {
            $table->string('import_type', 20)->default('csv')->after('user_id');
            $table->foreignId('corporation_id')->nullable()->after('import_type')->constrained('corporations')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('exam_id')->nullable()->after('corporation_id')->constrained('exams')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('exam_board_id')->nullable()->after('exam_id')->constrained('exam_boards')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('source_material_id')->nullable()->after('exam_board_id')->constrained('source_materials')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedSmallInteger('exam_year')->nullable()->after('source_material_id');
            $table->string('exam_reference', 180)->nullable()->after('exam_year');
            $table->string('source_type', 20)->default('exam')->after('exam_reference');
            $table->string('source_file_path')->nullable()->after('original_filename');
            $table->string('answer_file_path')->nullable()->after('source_file_path');
            $table->string('answer_original_filename')->nullable()->after('answer_file_path');
            $table->string('ai_model')->nullable()->after('answer_original_filename');
            $table->unsignedTinyInteger('ai_attempts')->default(0)->after('ai_model');
            $table->text('processing_error')->nullable()->after('ai_attempts');

            $table->index(['import_type', 'status'], 'idx_qib_type_status');
        });

        Schema::create('question_import_ai_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('question_import_batches')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('model');
            $table->string('status', 20)->default('started');
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->json('usage')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'created_at'], 'idx_qiai_batch_created');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('question_import_batch_id')
                ->nullable()
                ->after('created_by')
                ->constrained('question_import_batches')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('question_import_batch_id');
        });

        Schema::dropIfExists('question_import_ai_attempts');

        Schema::table('question_import_batches', function (Blueprint $table) {
            $table->dropIndex('idx_qib_type_status');
            $table->dropConstrainedForeignId('corporation_id');
            $table->dropConstrainedForeignId('exam_id');
            $table->dropConstrainedForeignId('exam_board_id');
            $table->dropConstrainedForeignId('source_material_id');
            $table->dropColumn([
                'import_type', 'exam_year', 'exam_reference', 'source_type', 'source_file_path', 'answer_file_path',
                'answer_original_filename', 'ai_model', 'ai_attempts', 'processing_error',
            ]);
        });
    }
};
