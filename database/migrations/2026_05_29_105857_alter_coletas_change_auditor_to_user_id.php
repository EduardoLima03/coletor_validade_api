<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("coletas", function (Blueprint $table) {
            if (Schema::hasColumn("coletas", "auditor")) {
                $table->dropColumn("auditor");
            }
        });
        Schema::table("coletas", function (Blueprint $table) {
            if (!Schema::hasColumn("coletas", "user_id")) {
                $table->foreignId("user_id")->after("loja_id")->constrained("users")->onDelete("cascade");
            }
        });
    }

    public function down(): void
    {
        Schema::table("coletas", function (Blueprint $table) {
            if (Schema::hasColumn("coletas", "user_id")) {
                if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                    $table->dropForeign(["user_id"]);
                }
                $table->dropColumn("user_id");
            }
        });
        Schema::table("coletas", function (Blueprint $table) {
            if (!Schema::hasColumn("coletas", "auditor")) {
                $table->string("auditor")->after("loja_id");
            }
        });
    }
};
