<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('coletas', 'descricao')) {
            Schema::table('coletas', function (Blueprint $table) {
                $table->string('descricao')->nullable()->after('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('coletas', function (Blueprint $table) {
            $table->dropColumn('descricao');
        });
    }
};
