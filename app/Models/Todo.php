<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Todo extends Model
{
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'priority',
        'is_completed',
        'user_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_completed' => 'boolean',
    ];

    /**
     * 作成者とのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 優先度のラベルを取得
     */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'high' => '高',
            'medium' => '中',
            'low' => '低',
            default => '中',
        };
    }

    /**
     * 優先度の色を取得
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'high' => 'red',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'yellow',
        };
    }
}
