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
 * 問い合わせモデル
 * 
 * @property int $inquiry_id
 * @property string $status
 * @property \Illuminate\Support\Carbon $received_at
 * @property string $sender_email
 * @property string|null $customer_id
 * @property string|null $prefecture
 * @property string|null $user_attribute
 * @property int|null $category_id
 * @property string $subject
 * @property string|null $summary
 * @property string $content
 * @property string|null $response
 * @property array|null $linked_faq_ids
 * @property int|null $assigned_user_id
 * @property int $created_user_id
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $response_deadline
 * @property \Illuminate\Support\Carbon|null $first_response_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $search_keywords
 * @property array|null $attachments
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Inquiry extends Model
{
    use HasFactory;

    /**
     * データベーステーブル名
     */
    protected $table = 'inquiries';

    /**
     * 主キー
     */
    protected $primaryKey = 'inquiry_id';

    /**
     * ステータス定数
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CLOSED = 'closed';

    /**
     * 優先度定数
     */
    public const PRIORITY_LOW = 1;
    public const PRIORITY_NORMAL = 2;
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_URGENT = 4;

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'status',
        'received_at',
        'sender_email',
        'customer_id',
        'prefecture',
        'user_attribute',
        'category_id',
        'subject',
        'summary',
        'content',
        'response',
        'linked_faq_ids',
        'assigned_user_id',
        'created_user_id',
        'priority',
        'response_deadline',
        'first_response_at',
        'completed_at',
        'search_keywords',
        'attachments',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'received_at' => 'datetime',
        'response_deadline' => 'datetime',
        'first_response_at' => 'datetime',
        'completed_at' => 'datetime',
        'linked_faq_ids' => 'array',
        'attachments' => 'array',
        'priority' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * デフォルト値
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'priority' => self::PRIORITY_NORMAL,
    ];

    /**
     * カテゴリとのリレーション
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * 担当者とのリレーション
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * 登録者とのリレーション
     */
    public function createdUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    /**
     * FAQとのリレーション
     */
    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(FAQ::class, 'inquiry_faq', 'inquiry_id', 'faq_id')
            ->withPivot('relevance', 'linked_by')
            ->withTimestamps();
    }

    /**
     * 添付ファイルとのリレーション
     */
    public function attachmentFiles(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * 履歴とのリレーション
     */
    public function histories(): HasMany
    {
        return $this->hasMany(InquiryHistory::class, 'inquiry_id');
    }

    /**
     * ステータス別のスコープ
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 優先度別のスコープ
     */
    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * 担当者別のスコープ
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }

    /**
     * 期限切れのスコープ
     */
    public function scopeOverdue($query)
    {
        return $query->where('response_deadline', '<', now())
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CLOSED]);
    }

    /**
     * 未対応のスコープ
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * 対応中のスコープ
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * 完了済みのスコープ
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * キーワード検索のスコープ
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('subject', 'like', "%{$keyword}%")
                ->orWhere('content', 'like', "%{$keyword}%")
                ->orWhere('summary', 'like', "%{$keyword}%")
                ->orWhere('search_keywords', 'like', "%{$keyword}%")
                ->orWhere('sender_email', 'like', "%{$keyword}%");
        });
    }

    /**
     * ステータスの日本語表示
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => '未対応',
            self::STATUS_IN_PROGRESS => '対応中',
            self::STATUS_COMPLETED => '完了',
            self::STATUS_CLOSED => 'クローズ',
            default => '不明',
        };
    }

    /**
     * 優先度の日本語表示
     */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => '低',
            self::PRIORITY_NORMAL => '中',
            self::PRIORITY_HIGH => '高',
            self::PRIORITY_URGENT => '緊急',
            default => '不明',
        };
    }

    /**
     * 期限切れかどうか
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->response_deadline &&
            $this->response_deadline->isPast() &&
            !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CLOSED]);
    }

    /**
     * 初回回答を記録
     */
    public function recordFirstResponse(): void
    {
        if (!$this->first_response_at) {
            $this->update(['first_response_at' => now()]);
        }
    }

    /**
     * ステータスを完了に変更
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }
}
