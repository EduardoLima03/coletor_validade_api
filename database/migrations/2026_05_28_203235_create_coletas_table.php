<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("coletas", function (Blueprint $table) {
            $table->id();
            $table->foreignId("loja_id")->constrained("lojas")->onDelete("cascade");
            $table->string("auditor");
            $table->string("setor")->nullable();
            $table->string("descricao");
            $table->string("ean", 20);
            $table->integer("quantidade");
            $table->date("data_validade");
            $table->timestamp("datahora")->useCurrent();
            $table->timestamps();

            $table->unique(["loja_id", "ean", "data_validade"], "coleta_unique");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("coletas");
    }
};
