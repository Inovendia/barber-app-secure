<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calender_marks', function (Blueprint $table) {
            $table->boolean('is_manual')->default(false)->after('symbol');
        });

        // 既存データをすべて手動設定扱いにする
        DB::table('calender_marks')->update(['is_manual' => true]);
    }

    public function down(): void
    {
        Schema::table('calender_marks', function (Blueprint $table) {
            $table->dropColumn('is_manual');
        });
    }
};
