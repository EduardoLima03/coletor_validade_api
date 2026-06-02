<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('users')->where('position', 'Adim')->update(['position' => 'ADMIN']);
        DB::table('users')->where('position', 'controladoria')->update(['position' => 'GERENCIA']);
        DB::table('users')->whereNotIn('position', ['ADMIN', 'GERENCIA'])->update(['position' => 'COLETOR']);
    }

    public function down()
    {
    }
};
