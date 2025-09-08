<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FAQ;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            // Aシステム関連
            [
                'category_id' => 1, // Aシステム
                'question' => 'Aシステムにログインできません',
                'answer' => 'ログインできない場合は以下を確認してください：' . "\n" .
                    '1. ユーザーIDとパスワードが正しいか' . "\n" .
                    '2. Caps Lockがオンになっていないか' . "\n" .
                    '3. アカウントがロックされていないか' . "\n\n" .
                    '上記を確認してもログインできない場合は、管理者にお問い合わせください。',
                'user_id' => 1,
                'tags' => 'ログイン,パスワード,エラー',
                'search_keywords' => 'Aシステム ログイン できない パスワード エラー アカウント ロック',
                'priority' => 3,
                'difficulty' => 1,
                'count' => 45,
            ],
            [
                'category_id' => 1,
                'question' => 'Aシステムでデータが表示されません',
                'answer' => 'データが表示されない場合は以下をご確認ください：' . "\n" .
                    '1. 検索条件が適切に設定されているか' . "\n" .
                    '2. 権限のあるデータかどうか' . "\n" .
                    '3. システムメンテナンス中でないか' . "\n\n" .
                    '解決しない場合は、スクリーンショットを添付してお問い合わせください。',
                'user_id' => 3,
                'tags' => 'データ,表示,エラー',
                'search_keywords' => 'Aシステム データ 表示されない 検索 権限 メンテナンス',
                'priority' => 2,
                'difficulty' => 2,
                'count' => 32,
            ],

            // Bシステム関連
            [
                'category_id' => 2, // Bシステム
                'question' => 'Bシステムで印刷ができません',
                'answer' => '印刷できない場合は以下を確認してください：' . "\n" .
                    '1. プリンタの電源が入っているか' . "\n" .
                    '2. 用紙がセットされているか' . "\n" .
                    '3. プリンタドライバが正しくインストールされているか' . "\n" .
                    '4. 印刷権限があるかどうか' . "\n\n" .
                    '解決しない場合は、IT部門にお問い合わせください。',
                'user_id' => 4,
                'tags' => '印刷,エラー,設定',
                'search_keywords' => 'Bシステム 印刷 できない プリンタ ドライバ 権限',
                'priority' => 2,
                'difficulty' => 1,
                'count' => 28,
            ],

            // システム全体
            [
                'category_id' => 3, // システム全体
                'question' => 'パスワードを忘れました',
                'answer' => 'パスワードをお忘れの場合：' . "\n" .
                    '1. ログイン画面の「パスワードを忘れた方」をクリック' . "\n" .
                    '2. 登録されているメールアドレスを入力' . "\n" .
                    '3. 送信されたメールのリンクからパスワードを再設定' . "\n\n" .
                    'メールが届かない場合は、迷惑メールフォルダもご確認ください。',
                'user_id' => 2,
                'tags' => 'パスワード,ログイン,メール',
                'search_keywords' => 'パスワード 忘れた リセット メール 再設定 迷惑メール',
                'priority' => 3,
                'difficulty' => 1,
                'count' => 67,
            ],

            // 制度関連
            [
                'category_id' => 4, // 制度
                'question' => '利用時間に制限はありますか？',
                'answer' => 'システム利用時間について：' . "\n" .
                    '• 平日：8:00 - 20:00' . "\n" .
                    '• 土日祝日：利用不可' . "\n" .
                    '• メンテナンス：毎月第2土曜日 13:00-17:00' . "\n\n" .
                    '緊急時のアクセスが必要な場合は、事前に管理者までご連絡ください。',
                'user_id' => 2,
                'tags' => '制度,時間,メンテナンス',
                'search_keywords' => '利用時間 制限 平日 土日祝日 メンテナンス 緊急',
                'priority' => 1,
                'difficulty' => 1,
                'count' => 15,
            ],
        ];

        foreach ($faqs as $faq) {
            FAQ::create($faq);
        }
    }
}
