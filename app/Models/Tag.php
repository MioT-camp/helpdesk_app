<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * タグモデル
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $color
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Tag extends Model
{
    use HasFactory;

    /**
     * データベーステーブル名
     */
    protected $table = 'tags';

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
    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(FAQ::class, 'faq_tag', 'tag_id', 'faq_id');
    }

    /**
     * アクティブなタグのスコープ
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

    /**
     * 名前での検索スコープ
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }
}
