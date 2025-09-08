<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 問い合わせ履歴モデル
 * 
 * @property int $id
 * @property int $inquiry_id
 * @property int $user_id
 * @property string $action
 * @property string|null $field_name
 * @property string|null $old_value
 * @property string|null $new_value
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon $created_at
 */
class InquiryHistory extends Model
{
    use HasFactory;

    /**
     * データベーステーブル名
     */
    protected $table = 'inquiry_histories';

    /**
     * 主キー
     */
    protected $primaryKey = 'id';

    /**
     * タイムスタンプの更新を無効化（created_atのみ使用）
     */
    public $timestamps = false;

    /**
     * アクション定数
     */
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_ASSIGNED = 'assigned';
    public const ACTION_STATUS_CHANGED = 'status_changed';
    public const ACTION_RESPONSE_ADDED = 'response_added';
    public const ACTION_COMMENT_ADDED = 'comment_added';
    public const ACTION_FAQ_LINKED = 'faq_linked';
    public const ACTION_ATTACHMENT_ADDED = 'attachment_added';

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'inquiry_id',
        'user_id',
        'action',
        'field_name',
        'old_value',
        'new_value',
        'comment',
        'created_at',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * デフォルト値
     */
    protected $attributes = [];

    /**
     * モデルの起動時処理
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    /**
     * 問い合わせとのリレーション
     */
    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class, 'inquiry_id');
    }

    /**
     * ユーザーとのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * アクション別のスコープ
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * 問い合わせ別のスコープ
     */
    public function scopeByInquiry($query, int $inquiryId)
    {
        return $query->where('inquiry_id', $inquiryId);
    }

    /**
     * ユーザー別のスコープ
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * 最新順のスコープ
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * アクションの日本語表示
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => '作成',
            self::ACTION_UPDATED => '更新',
            self::ACTION_ASSIGNED => '担当者割り当て',
            self::ACTION_STATUS_CHANGED => 'ステータス変更',
            self::ACTION_RESPONSE_ADDED => '回答追加',
            self::ACTION_COMMENT_ADDED => 'コメント追加',
            self::ACTION_FAQ_LINKED => 'FAQ紐付け',
            self::ACTION_ATTACHMENT_ADDED => '添付ファイル追加',
            default => $this->action,
        };
    }

    /**
     * 履歴の説明文を生成
     */
    public function getDescriptionAttribute(): string
    {
        $description = $this->action_label;

        if ($this->field_name) {
            $description .= "（{$this->field_name}）";
        }

        if ($this->old_value && $this->new_value) {
            $description .= "：{$this->old_value} → {$this->new_value}";
        } elseif ($this->new_value) {
            $description .= "：{$this->new_value}";
        }

        if ($this->comment) {
            $description .= " - {$this->comment}";
        }

        return $description;
    }

    /**
     * 履歴を記録
     */
    public static function record(
        int $inquiryId,
        int $userId,
        string $action,
        ?string $fieldName = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?string $comment = null
    ): self {
        return self::create([
            'inquiry_id' => $inquiryId,
            'user_id' => $userId,
            'action' => $action,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'comment' => $comment,
        ]);
    }
}
