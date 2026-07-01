<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recolhimento_regras', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('dia_semana')->comment('0=Domingo, 1=Segunda, ..., 6=Sábado');
            $table->unsignedTinyInteger('dias_antecedencia')->comment('Quantos dias antes do vencimento');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['dia_semana', 'dias_antecedencia']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('recolhimento_regras');
    }
};
