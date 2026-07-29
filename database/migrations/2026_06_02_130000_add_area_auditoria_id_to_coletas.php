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
        });

        Schema::table("coletas", function (Blueprint $table) {
            $table->dropUnique("coleta_unique");
            $table->unique(["loja_id", "area_auditoria_id", "ean", "data_validade"], "coleta_unique");
        });

        if (Schema::hasColumn('coletas', 'setor')) {
            Schema::table("coletas", function (Blueprint $table) {
                $table->dropColumn("setor");
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('coletas', 'setor')) {
            Schema::table("coletas", function (Blueprint $table) {
                $table->string("setor")->nullable()->after("loja_id");
            });
        }

        Schema::table("coletas", function (Blueprint $table) {
            $table->dropUnique("coleta_unique");
            $table->unique(["loja_id", "setor", "ean", "data_validade"], "coleta_unique");
        });

        Schema::table("coletas", function (Blueprint $table) {
            $table->dropConstrainedForeignId("area_auditoria_id");
        });
    }
};
