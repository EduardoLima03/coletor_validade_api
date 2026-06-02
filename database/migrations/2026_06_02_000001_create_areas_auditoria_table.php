<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->timestamps();

            $table->unique(['loja_id', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas_auditoria');
    }
};
