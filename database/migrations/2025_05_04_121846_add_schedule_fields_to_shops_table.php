<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('closed_days')->nullable()->after('phone'); // 例: 月,火,水
            $table->time('business_start')->nullable()->after('closed_days');
            $table->time('business_end')->nullable()->after('business_start');
            $table->time('break_start')->nullable()->after('business_end');
            $table->time('break_end')->nullable()->after('break_start');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['closed_days', 'business_start', 'business_end', 'break_start', 'break_end']);
        });
    }
};
