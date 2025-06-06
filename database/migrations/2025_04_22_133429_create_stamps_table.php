<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stamps', function (Blueprint $table) {
            $table->id(); // 主キー

            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // スタンプを貯めるユーザー
            $table->foreignId('shop_id')->constrained()->onDelete('cascade'); // どの店舗で押されたか

            $table->date('visit_date');           // 実際に来店した日
            $table->boolean('reward_claimed')->default(false); // 特典を使ったかどうか

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stamps');
    }
};
