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

        // まず指定カテゴリで検索を試行
        $faqs = $this->searchInCategory($keywords, $categoryId, $limit);

        // 指定カテゴリで結果が少ない場合は、全カテゴリから検索
        if ($faqs->count() < $limit && $categoryId) {
            $additionalFaqs = $this->searchInCategory($keywords, null, $limit - $faqs->count());
            $faqs = $faqs->merge($additionalFaqs);
        }

        return $faqs->sortByDesc('relevance_score')->take($limit);
    }

    /**
     * 指定カテゴリでFAQを検索
     */
    private function searchInCategory(array $keywords, ?int $categoryId, int $limit): Collection
    {
        $query = FAQ::with(['category', 'tagRelations'])
            ->where('is_active', true);

        // カテゴリフィルター
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // キーワード検索
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

        // 関連度スコアを計算してソート
        $faqs = $query->get()->map(function ($faq) use ($keywords, $categoryId) {
            $faq->relevance_score = $this->calculateRelevanceScore($faq, $keywords, $categoryId);
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
        $text = implode(' ', $texts);
        // 全角スペースを半角スペースに正規化
        return str_replace('　', ' ', $text);
    }

    /**
     * キーワードを抽出
     */
    private function extractKeywords(string $text): array
    {
        // 日本語の単語分割（改良版）
        $text = mb_strtolower($text);

        // 不要な文字を除去
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);

        // 空白で分割
        $words = preg_split('/\s+/', $text);

        // 全ての単語を基本キーワードとして収集
        $baseKeywords = array_filter($words, function ($word) {
            return mb_strlen($word) >= 2;
        });

        // 日本語の語幹を抽出（改良版）
        $expandedKeywords = [];
        foreach ($baseKeywords as $word) {
            $expandedKeywords[] = $word;

            // 段階的に語尾を削って語幹を抽出
            $currentWord = $word;

            // 長い語尾から順に削除
            $suffixes = [
                'がうまくいきません',
                'ができません',
                'がうまくいかない',
                'ができない',
                'について',
                'できません',
                'できない',
                'します',
                'しない',
                'ます',
                'ません',
                'です',
                'でした',
                'が出る',
                'が出',
                'が',
                'を',
                'に',
                'で',
                'は',
                'と',
                'から',
                'まで'
            ];

            foreach ($suffixes as $suffix) {
                if (mb_strlen($currentWord) > mb_strlen($suffix) + 1) {
                    $pattern = '/' . preg_quote($suffix, '/') . '$/';
                    $stem = preg_replace($pattern, '', $currentWord);
                    if ($stem !== $currentWord && mb_strlen($stem) >= 2) {
                        $expandedKeywords[] = $stem;
                        $currentWord = $stem; // 続けて短縮
                    }
                }
            }
        }

        // 頻出する単語を除外（助詞のみに限定）
        $stopWords = [
            'について',
            'です',
            'ます',
            'する',
            'した',
            'して',
            'される',
            'ある',
            'いる',
            'ない',
            'お',
            'ご',
            'の',
            'を',
            'に',
            'は',
            'で',
            'と',
            'から',
            'まで'
        ];

        $filteredKeywords = array_filter(array_unique($expandedKeywords), function ($word) use ($stopWords) {
            return !in_array($word, $stopWords) && mb_strlen($word) >= 2;
        });

        // キーワードが少ない場合は、元の単語も含める
        if (count($filteredKeywords) < 2) {
            return array_unique(array_merge($filteredKeywords, array_slice($baseKeywords, 0, 3)));
        }

        return array_values($filteredKeywords);
    }

    /**
     * 関連度スコアを計算
     */
    private function calculateRelevanceScore(FAQ $faq, array $keywords, ?int $preferredCategoryId = null): float
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

        // カテゴリマッチのボーナス
        if ($preferredCategoryId && $faq->category_id === $preferredCategoryId) {
            $score += 2;
        }

        // 閲覧回数によるボーナス
        $score += min($faq->count / 100, 1);

        // 優先度によるボーナス
        $score += $faq->priority * 0.5;

        return $score;
    }
}
