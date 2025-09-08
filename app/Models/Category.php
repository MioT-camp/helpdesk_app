<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * カテゴリモデル
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $color
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Category extends Model
{
    use HasFactory;

    /**
     * データベーステーブル名
     */
    protected $table = 'categories';

    /**
     * 主キー
     */
    protected $primaryKey = 'id';

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'name',
        'slug',
        'color',
        'is_active',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * デフォルト値
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * FAQとのリレーション
     */
    public function faqs(): HasMany
    {
        return $this->hasMany(FAQ::class, 'category_id');
    }

    /**
     * アクティブなFAQとのリレーション
     */
    public function activeFaqs(): HasMany
    {
        return $this->faqs()->where('is_active', true);
    }

    /**
     * 問い合わせとのリレーション
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'category_id');
    }

    /**
     * アクティブなカテゴリのスコープ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * スラッグでの検索スコープ
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
