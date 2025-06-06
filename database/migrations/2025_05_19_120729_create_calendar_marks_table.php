<?php

// database/migrations/xxxx_xx_xx_create_calender_marks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('calender_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->date('date');              // 例: 2025-05-17
            $table->time('time');              // 例: 14:00:00
            $table->string('symbol');          // 例: ×, tel, △, ○
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('calender_marks');
    }
};
