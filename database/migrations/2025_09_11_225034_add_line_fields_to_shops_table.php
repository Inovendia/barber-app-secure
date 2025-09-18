<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // LINE Messaging API 情報
            $table->string('line_channel_id')->default('')->after('phone');
            $table->string('line_channel_secret')->default('')->after('line_channel_id');
            $table->text('line_access_token')->after('line_channel_secret'); // 暗号化保存を想定

            // リッチメニュー関連
            $table->string('active_richmenu_id')->default('')->after('line_access_token');
            $table->string('richmenu_image_path')->default('')->after('active_richmenu_id');
            $table->string('richmenu_json_path')->default('')->after('richmenu_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'line_channel_id',
                'line_channel_secret',
                'line_access_token',
                'active_richmenu_id',
                'richmenu_image_path',
                'richmenu_json_path',
            ]);
        });
    }
};
