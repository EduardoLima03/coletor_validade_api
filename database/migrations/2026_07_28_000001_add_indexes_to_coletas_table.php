<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coletas', function (Blueprint $table) {
            $table->index('datahora');
            $table->index('user_id');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('coletas', function (Blueprint $table) {
            $table->dropIndex(['datahora']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['deleted_at']);
        });
    }
};
