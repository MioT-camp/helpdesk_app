<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * 添付ファイルモデル
 * 
 * @property int $id
 * @property string $attachable_type
 * @property int $attachable_id
 * @property string $filename
 * @property string $original_name
 * @property string $mime_type
 * @property int $size
 * @property string $path
 * @property int $uploaded_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Attachment extends Model
{
    use HasFactory;

    /**
     * データベーステーブル名
     */
    protected $table = 'attachments';

    /**
     * 主キー
     */
    protected $primaryKey = 'id';

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'filename',
        'original_name',
        'mime_type',
        'size',
        'path',
        'uploaded_by',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 関連モデルとのポリモーフィックリレーション
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * アップロード者とのリレーション
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * ファイル形式での検索スコープ
     */
    public function scopeByMimeType($query, string $mimeType)
    {
        return $query->where('mime_type', $mimeType);
    }

    /**
     * 画像ファイルのスコープ
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * PDFファイルのスコープ
     */
    public function scopePdfs($query)
    {
        return $query->where('mime_type', 'application/pdf');
    }

    /**
     * ファイルサイズでの検索スコープ
     */
    public function scopeBySizeRange($query, int $minSize = null, int $maxSize = null)
    {
        if ($minSize !== null) {
            $query->where('size', '>=', $minSize);
        }
        if ($maxSize !== null) {
            $query->where('size', '<=', $maxSize);
        }
        return $query;
    }

    /**
     * ファイルサイズを人間が読みやすい形式で取得
     */
    public function getHumanReadableSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * ファイル拡張子を取得
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    /**
     * 画像ファイルかどうか
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * PDFファイルかどうか
     */
    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
