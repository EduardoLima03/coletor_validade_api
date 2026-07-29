<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_auditoria_loja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_auditoria_id')->constrained('areas_auditoria')->onDelete('cascade');
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->unique(['area_auditoria_id', 'loja_id']);
        });

        DB::statement('INSERT INTO area_auditoria_loja (area_auditoria_id, loja_id) SELECT id, loja_id FROM areas_auditoria');

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('areas_auditoria', function (Blueprint $table) {
                $table->dropForeign(['loja_id']);
                $table->dropUnique(['loja_id', 'nome']);
                $table->dropColumn('loja_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('areas_auditoria', function (Blueprint $table) {
            $table->foreignId('loja_id')->nullable()->after('id');
            $table->unique(['loja_id', 'nome']);
        });

        DB::statement('ALTER TABLE areas_auditoria ADD CONSTRAINT areas_auditoria_loja_id_foreign FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE');

        DB::statement('UPDATE areas_auditoria a SET loja_id = (SELECT loja_id FROM area_auditoria_loja WHERE area_auditoria_id = a.id LIMIT 1)');

        Schema::dropIfExists('area_auditoria_loja');
    }
};
