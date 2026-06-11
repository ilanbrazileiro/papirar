<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration documental para consulta futura.
     *
     * Observação: o projeto Papirar não está usando migrations como mecanismo
     * principal de alteração em produção. Este arquivo deve ser mantido no
     * repositório como referência da estrutura necessária para o Lote 08.
     */
    public function up(): void
    {
        Schema::table('simulated_exams', function (Blueprint $table) {
            $table->unsignedInteger('duration_minutes')->nullable()->after('total_questions');
            $table->timestamp('ends_at')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('simulated_exams', function (Blueprint $table) {
            $table->dropColumn(['duration_minutes', 'ends_at']);
        });
    }
};
