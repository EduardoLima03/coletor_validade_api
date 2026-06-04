<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('coleta_edit')->default(true)->after('position');
            $table->boolean('coleta_delete')->default(true)->after('coleta_edit');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['coleta_edit', 'coleta_delete']);
        });
    }
};
