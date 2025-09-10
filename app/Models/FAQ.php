<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * FAQモデル
 * 
 * @property int $faq_id
 * @property int $category_id
 * @property string $question
 * @property string $answer
 * @property int $user_id
 * @property int $count
 * @property bool $is_active
 * @property string|null $tags
 * @property string|null $search_keywords
 * @property int $priority
 * @property int $difficulty
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class FAQ extends Model
{
    use HasFactory;

    /**
     * データベーステーブル名
     */
    protected $table = 'faqs';

    /**
     * 主キー
     */
    protected $primaryKey = 'faq_id';

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'category_id',
        'question',
        'answer',
        'user_id',
        'count',
        'is_active',
        'tags',
        'search_keywords',
        'priority',
        'difficulty',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'is_active' => 'boolean',
        'count' => 'integer',
        'priority' => 'integer',
        'difficulty' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * デフォルト値
     */
    protected $attributes = [
        'count' => 0,
        'is_active' => true,
        'priority' => 1,
        'difficulty' => 1,
    ];

    /**
     * カテゴリとのリレーション
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * 登録者とのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * タグとのリレーション
     */
    public function tagRelations(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'faq_tag', 'faq_id', 'tag_id');
    }


    /**
     * 問い合わせとのリレーション
     */
    public function inquiries(): BelongsToMany
    {
        return $this->belongsToMany(Inquiry::class, 'inquiry_faq', 'faq_id', 'inquiry_id')
            ->withPivot('relevance', 'linked_by')
            ->withTimestamps();
    }

    /**
     * 添付ファイルとのリレーション
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * FAQ閲覧履歴とのリレーション
     */
    public function views(): HasMany
    {
        return $this->hasMany(FaqView::class, 'faq_id');
    }

    /**
     * アクティブなFAQのスコープ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 優先度でのフィルタリングスコープ
     */
    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * 難易度でのフィルタリングスコープ
     */
    public function scopeByDifficulty($query, int $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * 人気順のスコープ
     */
    public function scopePopular($query)
    {
        return $query->orderBy('count', 'desc');
    }

    /**
     * 最新順のスコープ
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * キーワード検索のスコープ
     */
    public function scopeSearch($query, string $keyword)
    {
        // キーワードを空白で分割して複数キーワード検索をサポート
        $keywords = preg_split('/\s+/', trim($keyword));
        $keywords = array_filter($keywords, fn($k) => mb_strlen($k) >= 1);

        if (empty($keywords)) {
            return $query;
        }

        return $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $k) {
                $q->orWhere(function ($subQ) use ($k) {
                    $subQ->where('question', 'like', "%{$k}%")
                        ->orWhere('answer', 'like', "%{$k}%")
                        ->orWhere('tags', 'like', "%{$k}%")
                        ->orWhere('search_keywords', 'like', "%{$k}%");
                });
            }
        });
    }

    /**
     * 閲覧回数をインクリメント
     */
    public function incrementViewCount(): void
    {
        $this->increment('count');
    }

    /**
     * タグの配列を取得
     */
    public function getTagsArrayAttribute(): array
    {
        if (empty($this->tags)) {
            return [];
        }
        return array_map('trim', explode(',', $this->tags));
    }
}
