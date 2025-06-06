<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id(); // 主キー
            $table->string('name'); // 店舗名
            $table->string('address')->nullable(); // 住所（任意）
            $table->string('phone')->nullable(); // 電話番号（任意）
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
