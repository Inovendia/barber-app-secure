<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id(); // 主キー

            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 対象ユーザー
            $table->foreignId('shop_id')->constrained()->onDelete('cascade'); // 店舗（どの店で書いたか）

            $table->text('content');              // メモ本文（フリーテキスト）
            $table->string('created_by')->nullable(); // 書いた人（店主名など）

            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};

