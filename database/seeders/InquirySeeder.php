<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inquiry;

class InquirySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inquiries = [
            [
                'status' => 'completed',
                'received_at' => now()->subDays(5),
                'sender_email' => 'user1@customer.com',
                'customer_id' => 'CUST001',
                'prefecture' => '東京都',
                'user_attribute' => '法人',
                'category_id' => 1,
                'subject' => 'Aシステムログインエラーについて',
                'summary' => 'ログイン時にエラーメッセージが表示され、システムにアクセスできない状況',
                'content' => 'いつもお世話になっております。' . "\n" .
                    '本日朝からAシステムにログインしようとすると「認証エラー」というメッセージが表示され、システムにアクセスできません。' . "\n" .
                    'ユーザーIDとパスワードは間違いないと思うのですが、解決方法を教えてください。',
                'response' => 'お問い合わせありがとうございます。' . "\n" .
                    '確認したところ、アカウントが一時的にロックされていました。' . "\n" .
                    'ロックを解除いたしましたので、再度ログインをお試しください。' . "\n\n" .
                    '今後同様の問題を避けるため、パスワード入力時はCaps Lockの状態もご確認ください。',
                'assigned_user_id' => 3,
                'created_user_id' => 1,
                'priority' => 3,
                'first_response_at' => now()->subDays(5)->addHours(2),
                'completed_at' => now()->subDays(5)->addHours(3),
                'search_keywords' => 'Aシステム ログイン エラー 認証エラー アカウント ロック',
            ],
            [
                'status' => 'in_progress',
                'received_at' => now()->subDays(2),
                'sender_email' => 'user2@customer.com',
                'customer_id' => 'CUST002',
                'prefecture' => '大阪府',
                'user_attribute' => '個人',
                'category_id' => 2,
                'subject' => 'Bシステムでの印刷トラブル',
                'summary' => 'Bシステムから印刷実行時にエラーが発生し印刷できない',
                'content' => 'Bシステムで帳票を印刷しようとすると、「プリンタに接続できません」というエラーが出ます。' . "\n" .
                    '他のアプリケーションからは正常に印刷できるのですが、Bシステムからだけ印刷できません。',
                'assigned_user_id' => 4,
                'created_user_id' => 2,
                'priority' => 2,
                'response_deadline' => now()->addDays(1),
                'first_response_at' => now()->subDays(2)->addHours(4),
                'search_keywords' => 'Bシステム 印刷 トラブル プリンタ 接続できません エラー',
            ],
            [
                'status' => 'pending',
                'received_at' => now()->subHours(3),
                'sender_email' => 'user3@customer.com',
                'prefecture' => '神奈川県',
                'user_attribute' => '代理店',
                'category_id' => 3,
                'subject' => 'システム全体の動作が重い',
                'summary' => 'システム全体の応答速度が遅く業務に支障が出ている',
                'content' => '昨日の午後から、すべてのシステムの動作が非常に重くなっています。' . "\n" .
                    '画面の切り替えに時間がかかり、業務効率が大幅に低下しています。' . "\n" .
                    '改善の見込みを教えてください。',
                'created_user_id' => 1,
                'priority' => 4,
                'response_deadline' => now()->addHours(4),
                'search_keywords' => 'システム全体 動作 重い 応答速度 遅い 業務効率',
            ],
        ];

        foreach ($inquiries as $inquiry) {
            Inquiry::create($inquiry);
        }
    }
}
