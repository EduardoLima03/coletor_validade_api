<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("coletas", function (Blueprint $table) {
            $table->string("quantidade", 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table("coletas", function (Blueprint $table) {
            $table->integer("quantidade")->change();
        });
    }
};
