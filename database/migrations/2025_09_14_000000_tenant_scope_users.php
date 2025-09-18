<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void
    {
        // 1) NOT NULL に変更（テーブルが空なので安全）
        DB::statement("ALTER TABLE `users` MODIFY `line_user_id` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE `users` MODIFY `shop_id` BIGINT UNSIGNED NOT NULL");

        // 2) 既存の UNIQUE(line_user_id) を“ある場合のみ”DROP
        if ($this->indexExists('users', 'users_line_user_id_unique')) {
            DB::statement("ALTER TABLE `users` DROP INDEX `users_line_user_id_unique`");
        } elseif ($this->indexExists('users', 'line_user_id')) {
            // 環境によってはインデックス名が列名だけになっていることがある
            DB::statement("ALTER TABLE `users` DROP INDEX `line_user_id`");
        }

        // 3) shops へのFK（なければ追加）
        if (!$this->foreignKeyExists('users', 'fk_users_shop')) {
            DB::statement("ALTER TABLE `users`
                ADD CONSTRAINT `fk_users_shop`
                FOREIGN KEY (`shop_id`) REFERENCES `shops`(`id`) ON DELETE CASCADE
            ");
        }

        // 4) 複合UNIQUE & 補助INDEX（なければ追加）
        if (!$this->indexExists('users', 'uniq_shop_line_user')) {
            DB::statement("ALTER TABLE `users`
                ADD UNIQUE KEY `uniq_shop_line_user` (`shop_id`,`line_user_id`)
            ");
        }
        if (!$this->indexExists('users', 'idx_users_shop')) {
            DB::statement("ALTER TABLE `users`
                ADD INDEX `idx_users_shop` (`shop_id`)
            ");
        }
    }

    public function down(): void
    {
        // 逆順で安全に削除
        if ($this->indexExists('users', 'idx_users_shop')) {
            DB::statement("ALTER TABLE `users` DROP INDEX `idx_users_shop`");
        }
        if ($this->indexExists('users', 'uniq_shop_line_user')) {
            DB::statement("ALTER TABLE `users` DROP INDEX `uniq_shop_line_user`");
        }
        if ($this->foreignKeyExists('users', 'fk_users_shop')) {
            DB::statement("ALTER TABLE `users` DROP FOREIGN KEY `fk_users_shop`");
        }

        // 元の状態に戻す（NULL許可・単体UNIQUEを復活）
        DB::statement("ALTER TABLE `users` MODIFY `line_user_id` VARCHAR(255) NULL");
        DB::statement("ALTER TABLE `users` MODIFY `shop_id` BIGINT UNSIGNED NULL");

        if (!$this->indexExists('users', 'users_line_user_id_unique')) {
            DB::statement("ALTER TABLE `users` ADD UNIQUE KEY `users_line_user_id_unique` (`line_user_id`)");
        }
    }

    /** インデックス存在確認 */
    private function indexExists(string $table, string $index): bool
    {
        $db = DB::getDatabaseName();
        $count = DB::table('information_schema.statistics')
            ->where('table_schema', $db)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->count();
        return $count > 0;
    }

    /** 外部キー存在確認 */
    private function foreignKeyExists(string $table, string $fk): bool
    {
        $db = DB::getDatabaseName();
        $count = DB::table('information_schema.table_constraints')
            ->where('table_schema', $db)
            ->where('table_name', $table)
            ->where('constraint_type', 'FOREIGN KEY')
            ->where('constraint_name', $fk)
            ->count();
        return $count > 0;
    }
};
