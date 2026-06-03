<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("coletas", function (Blueprint $table) {
            $table->index("loja_id", "coletas_loja_id_index");
            $table->dropUnique("coleta_unique");
            $table->unique(["loja_id", "setor", "ean", "data_validade"], "coleta_unique");
        });
    }

    public function down(): void
    {
        Schema::table("coletas", function (Blueprint $table) {
            $table->dropUnique("coleta_unique");
            $table->dropIndex("coletas_loja_id_index");
            $table->unique(["loja_id", "ean", "data_validade"], "coleta_unique");
        });
    }
};
