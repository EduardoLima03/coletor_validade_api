<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('cnpj', 18)->unique();
            $table->string('license_key', 64)->unique();
            $table->string('plan')->default('basic');
            $table->integer('max_stores')->default(1);
            $table->integer('max_users')->default(1);
            $table->date('valid_from');
            $table->date('valid_until');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('licenses');
    }
};
