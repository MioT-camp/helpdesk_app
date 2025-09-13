<?php

namespace Database\Seeders;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TodoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('ユーザーが存在しません。先にUserSeederを実行してください。');
            return;
        }

        $todos = [
            [
                'title' => 'システムメンテナンスの準備',
                'description' => '来週のメンテナンス作業に必要な資料を準備する',
                'due_date' => now()->addDays(3),
                'priority' => 'high',
                'is_completed' => false,
            ],
            [
                'title' => 'FAQの更新',
                'description' => '新機能に関するFAQを追加する',
                'due_date' => now()->addDays(7),
                'priority' => 'medium',
                'is_completed' => false,
            ],
            [
                'title' => '問い合わせ対応の確認',
                'description' => '未対応の問い合わせがないか確認する',
                'due_date' => now()->addDays(1),
                'priority' => 'high',
                'is_completed' => true,
            ],
            [
                'title' => '月次レポートの作成',
                'description' => '今月の問い合わせ統計レポートを作成する',
                'due_date' => now()->addDays(10),
                'priority' => 'low',
                'is_completed' => false,
            ],
            [
                'title' => 'ユーザートレーニング資料の準備',
                'description' => '新入社員向けのシステム操作資料を作成する',
                'due_date' => now()->addDays(14),
                'priority' => 'medium',
                'is_completed' => false,
            ],
        ];

        foreach ($todos as $todoData) {
            Todo::create([
                ...$todoData,
                'user_id' => $users->random()->id,
            ]);
        }
    }
}
