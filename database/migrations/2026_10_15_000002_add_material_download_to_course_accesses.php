<?php
// APENAS REFERÊNCIA. Não executar php artisan migrate para este lote.
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('course_accesses',function(Blueprint $table) {
        $table->boolean('allow_material_download')->default(false);
    }); }
    public function down(): void { Schema::table('course_accesses',function(Blueprint $table) {
        $table->dropColumn('allow_material_download');
    }); }
};
