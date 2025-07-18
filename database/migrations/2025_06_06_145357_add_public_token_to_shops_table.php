<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('public_token')->unique()->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }
};