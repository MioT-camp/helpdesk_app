<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqTags = [
            // FAQ 1 (Aシステムログイン) のタグ
            ['faq_id' => 1, 'tag_id' => 1], // ログイン
            ['faq_id' => 1, 'tag_id' => 2], // パスワード
            ['faq_id' => 1, 'tag_id' => 4], // エラー

            // FAQ 2 (Aシステムデータ表示) のタグ
            ['faq_id' => 2, 'tag_id' => 3], // データ
            ['faq_id' => 2, 'tag_id' => 4], // エラー

            // FAQ 3 (Bシステム印刷) のタグ
            ['faq_id' => 3, 'tag_id' => 8], // 印刷
            ['faq_id' => 3, 'tag_id' => 4], // エラー
            ['faq_id' => 3, 'tag_id' => 6], // 設定

            // FAQ 4 (パスワード忘れ) のタグ
            ['faq_id' => 4, 'tag_id' => 2], // パスワード
            ['faq_id' => 4, 'tag_id' => 1], // ログイン
            ['faq_id' => 4, 'tag_id' => 7], // メール

            // FAQ 5 (利用時間制限) のタグ
            ['faq_id' => 5, 'tag_id' => 6], // 設定
        ];

        DB::table('faq_tag')->insert($faqTags);
    }
}
