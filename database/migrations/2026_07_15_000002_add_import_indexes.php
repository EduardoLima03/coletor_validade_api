<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('areas_auditoria', function (Blueprint $table) {
            $table->index('nome');
        });

        Schema::table('coletas', function (Blueprint $table) {
            $table->index(['loja_id', 'ean', 'data_validade'], 'coletas_import_lookup');
            $table->index('data_validade');
        });
    }

    public function down(): void
    {
        Schema::table('areas_auditoria', function (Blueprint $table) {
            $table->dropIndex(['nome']);
        });

        Schema::table('coletas', function (Blueprint $table) {
            $table->dropIndex('coletas_import_lookup');
            $table->dropIndex(['data_validade']);
        });
    }
};
