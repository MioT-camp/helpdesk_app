<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FaqView;

class FaqViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqViews = [];

        // 各FAQに対してランダムな閲覧履歴を生成
        for ($faqId = 1; $faqId <= 5; $faqId++) {
            // 過去30日間でランダムな閲覧履歴を生成
            $viewCount = rand(10, 50);

            for ($i = 0; $i < $viewCount; $i++) {
                $faqViews[] = [
                    'faq_id' => $faqId,
                    'user_id' => rand(1, 4), // ユーザー1-4のいずれか
                    'ip_address' => $this->generateRandomIp(),
                    'user_agent' => $this->getRandomUserAgent(),
                    'viewed_at' => now()->subDays(rand(0, 30))->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                ];
            }

            // 匿名閲覧（user_id = null）も追加
            for ($i = 0; $i < rand(5, 15); $i++) {
                $faqViews[] = [
                    'faq_id' => $faqId,
                    'user_id' => null,
                    'ip_address' => $this->generateRandomIp(),
                    'user_agent' => $this->getRandomUserAgent(),
                    'viewed_at' => now()->subDays(rand(0, 30))->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                ];
            }
        }

        foreach ($faqViews as $view) {
            FaqView::create($view);
        }
    }

    /**
     * ランダムなIPアドレスを生成
     */
    private function generateRandomIp(): string
    {
        return rand(192, 255) . '.' . rand(168, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
    }

    /**
     * ランダムなユーザーエージェントを取得
     */
    private function getRandomUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 Edg/91.0.864.59',
        ];

        return $userAgents[array_rand($userAgents)];
    }
}
