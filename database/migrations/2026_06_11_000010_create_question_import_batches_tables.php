<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration documental: criada para consulta futura.
     * O projeto Papirar está aplicando as alterações estruturais via SQL direto.
     */
    public function up(): void
    {
        Schema::create('question_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('filename')->nullable();
            $table->string('original_filename')->nullable();
            $table->enum('status', ['uploaded', 'validating', 'ready', 'importing', 'imported', 'partial', 'failed', 'cancelled'])->default('uploaded');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('draft_rows')->default(0);
            $table->unsignedInteger('duplicate_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->unsignedInteger('ignored_rows')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'idx_qib_status_created_at');
            $table->index('user_id', 'idx_qib_user_id');
        });

        Schema::create('question_import_batch_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('question_import_batches')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('row_number');
            $table->enum('status', ['pending', 'valid', 'imported', 'duplicate', 'error', 'ignored'])->default('pending');
            $table->json('raw_data')->nullable();
            $table->text('normalized_statement')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('duplicate_question_id')->nullable()->constrained('questions')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('created_question_id')->nullable()->constrained('questions')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->unique(['batch_id', 'row_number'], 'uq_qibr_batch_row');
            $table->index(['batch_id', 'status'], 'idx_qibr_batch_status');
            $table->index('created_question_id', 'idx_qibr_created_question_id');
            $table->index('duplicate_question_id', 'idx_qibr_duplicate_question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_import_batch_rows');
        Schema::dropIfExists('question_import_batches');
    }
};
