<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id(); // 主キー

            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ユーザーに紐づく
            $table->foreignId('shop_id')->constrained()->onDelete('cascade'); // 店舗に紐づく

            $table->string('menu')->nullable();        // カット・カラーなどのメニュー名
            $table->timestamp('reserved_at');          // 予約日時
            $table->string('status')->default('pending'); // 状態（pending / confirmed / cancelled）

            $table->timestamps(); // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
