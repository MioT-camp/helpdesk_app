<?php

declare(strict_types=1);

namespace App\Livewire\Actions;

use App\Models\FAQ;
use Illuminate\Support\Collection;

/**
 * 関連FAQ検索アクション
 */
class SearchRelatedFaqs
{
    /**
     * 問い合わせ内容から関連FAQを検索
     *
     * @param string $subject 件名
     * @param string|null $summary 要約
     * @param string $content 詳細内容
     * @param int|null $categoryId カテゴリID
     * @param int $limit 取得件数
     * @return Collection
     */
    public function execute(
        string $subject,
        ?string $summary,
        string $content,
        ?int $categoryId = null,
        int $limit = 10
    ): Collection {
        // 検索キーワードを生成
        $searchText = $this->buildSearchText($subject, $summary, $content);

        // キーワードを分割
        $keywords = $this->extractKeywords($searchText);

        if (empty($keywords)) {
            return collect();
        }

        $query = FAQ::with(['category', 'tagRelations'])
            ->where('is_active', true);

        // カテゴリフィルター
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // キーワード検索（より緩い条件）
        if (!empty($keywords)) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere(function ($subQ) use ($keyword) {
                        $subQ->where('question', 'like', "%{$keyword}%")
                            ->orWhere('answer', 'like', "%{$keyword}%")
                            ->orWhere('search_keywords', 'like', "%{$keyword}%")
                            ->orWhere('tags', 'like', "%{$keyword}%");
                    });
                }
            });
        } else {
            // キーワードがない場合は、カテゴリのみで検索
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
        }

        // 関連度スコアを計算してソート
        $faqs = $query->get()->map(function ($faq) use ($keywords) {
            $faq->relevance_score = $this->calculateRelevanceScore($faq, $keywords);
            return $faq;
        });

        return $faqs->sortByDesc('relevance_score')->take($limit);
    }

    /**
     * 検索テキストを構築
     */
    private function buildSearchText(string $subject, ?string $summary, string $content): string
    {
        $texts = array_filter([$subject, $summary, $content]);
        return implode(' ', $texts);
    }

    /**
     * キーワードを抽出
     */
    private function extractKeywords(string $text): array
    {
        // 日本語の単語分割（簡易版）
        $text = mb_strtolower($text);

        // 不要な文字を除去
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);

        // 空白で分割
        $words = preg_split('/\s+/', $text);

        // 2文字以上の単語のみを抽出
        $keywords = array_filter($words, function ($word) {
            return mb_strlen($word) >= 2;
        });

        // 頻出する単語を除外
        $stopWords = [
            'について',
            'です',
            'ます',
            'です',
            'でした',
            'でした',
            'です',
            'ます',
            'する',
            'した',
            'して',
            'される',
            'される',
            'する',
            'した',
            'して',
            'ある',
            'いる',
            'ない',
            'ない',
            'ある',
            'いる',
            'ない',
            'ない',
            'です',
            'ます',
            'です',
            'ます',
            'です',
            'ます',
            'です',
            'ます',
            'お',
            'ご',
            'の',
            'を',
            'に',
            'は',
            'が',
            'で',
            'と',
            'から',
            'まで'
        ];

        $filteredKeywords = array_filter($keywords, function ($word) use ($stopWords) {
            return !in_array($word, $stopWords);
        });

        // キーワードが少ない場合は、より緩い条件で検索
        if (count($filteredKeywords) < 2) {
            return array_slice($keywords, 0, 3); // 最初の3つの単語を使用
        }

        return $filteredKeywords;
    }

    /**
     * 関連度スコアを計算
     */
    private function calculateRelevanceScore(FAQ $faq, array $keywords): float
    {
        $score = 0;
        $text = mb_strtolower($faq->question . ' ' . $faq->answer . ' ' . $faq->search_keywords . ' ' . $faq->tags);

        foreach ($keywords as $keyword) {
            // 質問文でのマッチ
            if (str_contains($faq->question, $keyword)) {
                $score += 3;
            }

            // 回答文でのマッチ
            if (str_contains($faq->answer, $keyword)) {
                $score += 2;
            }

            // タグでのマッチ
            if (str_contains($faq->tags ?? '', $keyword)) {
                $score += 4;
            }

            // 検索キーワードでのマッチ
            if (str_contains($faq->search_keywords ?? '', $keyword)) {
                $score += 2;
            }
        }

        // 閲覧回数によるボーナス
        $score += min($faq->count / 100, 1);

        // 優先度によるボーナス
        $score += $faq->priority * 0.5;

        return $score;
    }
}
