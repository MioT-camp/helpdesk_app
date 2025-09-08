<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * FAQ閲覧履歴モデル
 * 
 * @property int $id
 * @property int $faq_id
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon $viewed_at
 */
class FaqView extends Model
{
    use HasFactory;

    /**
     * データベーステーブル名
     */
    protected $table = 'faq_views';

    /**
     * 主キー
     */
    protected $primaryKey = 'id';

    /**
     * タイムスタンプの更新を無効化（viewed_atのみ使用）
     */
    public $timestamps = false;

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'faq_id',
        'user_id',
        'ip_address',
        'user_agent',
        'viewed_at',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * モデルの起動時処理
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->viewed_at = now();
        });
    }

    /**
     * FAQとのリレーション
     */
    public function faq(): BelongsTo
    {
        return $this->belongsTo(FAQ::class, 'faq_id');
    }

    /**
     * ユーザーとのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * FAQ別のスコープ
     */
    public function scopeByFaq($query, int $faqId)
    {
        return $query->where('faq_id', $faqId);
    }

    /**
     * ユーザー別のスコープ
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * IPアドレス別のスコープ
     */
    public function scopeByIpAddress($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * 期間別のスコープ
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('viewed_at', [$startDate, $endDate]);
    }

    /**
     * 今日のスコープ
     */
    public function scopeToday($query)
    {
        return $query->whereDate('viewed_at', today());
    }

    /**
     * 今週のスコープ
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('viewed_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * 今月のスコープ
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('viewed_at', now()->month)
            ->whereYear('viewed_at', now()->year);
    }

    /**
     * ユニークビューのスコープ（同じユーザー・IPの重複を除外）
     */
    public function scopeUniqueViews($query)
    {
        return $query->select('faq_id', 'user_id', 'ip_address', 'viewed_at')
            ->groupBy('faq_id', 'user_id', 'ip_address')
            ->havingRaw('viewed_at = MAX(viewed_at)');
    }

    /**
     * 閲覧履歴を記録
     */
    public static function recordView(
        int $faqId,
        ?int $userId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return self::create([
            'faq_id' => $faqId,
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * FAQの人気度を計算（期間指定可能）
     */
    public static function getPopularFaqs(int $limit = 10, ?int $days = null)
    {
        $query = self::query()
            ->select('faq_id', \DB::raw('COUNT(*) as view_count'))
            ->groupBy('faq_id')
            ->orderBy('view_count', 'desc')
            ->limit($limit);

        if ($days) {
            $query->where('viewed_at', '>=', now()->subDays($days));
        }

        return $query->with('faq')->get();
    }
}
