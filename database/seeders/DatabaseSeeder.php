<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 基本マスターデータのシーディング
        $this->call([
            CategorySeeder::class,      // 1. カテゴリ
            TagSeeder::class,          // 2. タグ
            SystemSettingSeeder::class, // 3. システム設定
        ]);

        // 開発・テスト用ユーザーの作成
        $users = [
            [
                'name' => '管理者 太郎',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'department' => '運用保守チーム',
                'specialties' => ['システム全体', 'Aシステム', 'Bシステム'],
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'マネージャー 花子',
                'email' => 'manager@example.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'department' => '運用保守チーム',
                'specialties' => ['制度', 'システム全体'],
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'スタッフ 一郎',
                'email' => 'staff1@example.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'department' => '運用保守チーム',
                'specialties' => ['Aシステム'],
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'スタッフ 二郎',
                'email' => 'staff2@example.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'department' => '運用保守チーム',
                'specialties' => ['Bシステム'],
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // サンプルデータのシーディング（開発環境のみ）
        if (app()->environment(['local', 'testing'])) {
            $this->call([
                FaqSeeder::class,          // 4. FAQ
                InquirySeeder::class,      // 5. 問い合わせ
                FaqTagSeeder::class,       // 6. FAQ-タグ紐付け
                InquiryFaqSeeder::class,   // 7. 問い合わせ-FAQ紐付け
                FaqViewSeeder::class,      // 8. FAQ閲覧履歴
            ]);
        }
    }
}
