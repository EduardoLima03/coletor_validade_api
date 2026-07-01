<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('coletas', function (Blueprint $table) {
            $table->timestamp('recolhido_em')->nullable()->after('datahora');
            $table->decimal('recolhido_quantidade', 10, 2)->nullable()->after('recolhido_em');
            $table->foreignId('recolhido_user_id')->nullable()->constrained('users')->nullOnDelete()->after('recolhido_quantidade');
        });
    }

    public function down()
    {
        Schema::table('coletas', function (Blueprint $table) {
            $table->dropForeign(['recolhido_user_id']);
            $table->dropColumn(['recolhido_em', 'recolhido_quantidade', 'recolhido_user_id']);
        });
    }
};
