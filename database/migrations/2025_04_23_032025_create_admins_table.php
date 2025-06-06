<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id(); // 主キー
            $table->foreignId('shop_id')->constrained()->onDelete('cascade'); // 店舗に紐づく
            $table->string('name'); // 管理者名
            $table->string('email')->unique(); // ログイン用メール
            $table->string('password'); // ハッシュ化されたパスワード
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
