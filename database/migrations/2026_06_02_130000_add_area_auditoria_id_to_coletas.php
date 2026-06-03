<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("coletas", function (Blueprint $table) {
            $table->foreignId("area_auditoria_id")
                ->nullable()
                ->after("loja_id")
                ->constrained("areas_auditoria")
                ->nullOnDelete();

            $table->dropUnique("coleta_unique");
            $table->unique(["loja_id", "area_auditoria_id", "ean", "data_validade"], "coleta_unique");

            $table->dropColumn("setor");
        });
    }

    public function down(): void
    {
        Schema::table("coletas", function (Blueprint $table) {
            $table->string("setor")->nullable()->after("loja_id");

            $table->dropUnique("coleta_unique");
            $table->unique(["loja_id", "setor", "ean", "data_validade"], "coleta_unique");

            $table->dropConstrainedForeignId("area_auditoria_id");
        });
    }
};
