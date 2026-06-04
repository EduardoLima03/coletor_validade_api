<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 100)->default('Medeiros');
            $table->string('company_icon', 255)->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'company_name' => 'Medeiros',
            'company_icon' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
