<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InquiryFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inquiryFaqs = [
            // 問い合わせ 1 (Aシステムログインエラー) と関連FAQ
            [
                'inquiry_id' => 1,
                'faq_id' => 1, // Aシステムログイン
                'relevance' => 5,
                'linked_by' => 3, // スタッフ一郎
                'created_at' => now()->subDays(5)->addHours(1),
            ],
            [
                'inquiry_id' => 1,
                'faq_id' => 4, // パスワード忘れ
                'relevance' => 3,
                'linked_by' => 3,
                'created_at' => now()->subDays(5)->addHours(1),
            ],

            // 問い合わせ 2 (Bシステム印刷トラブル) と関連FAQ
            [
                'inquiry_id' => 2,
                'faq_id' => 3, // Bシステム印刷
                'relevance' => 5,
                'linked_by' => 4, // スタッフ二郎
                'created_at' => now()->subDays(2)->addHours(2),
            ],
        ];

        DB::table('inquiry_faq')->insert($inquiryFaqs);
    }
}
