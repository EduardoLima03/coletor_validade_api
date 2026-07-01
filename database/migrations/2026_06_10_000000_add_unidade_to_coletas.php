<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coletas', function (Blueprint $table) {
            $table->string('unidade', 10)->nullable()->default('un')->after('quantidade');
        });
    }

    public function down(): void
    {
        Schema::table('coletas', function (Blueprint $table) {
            $table->dropColumn('unidade');
        });
    }
};
