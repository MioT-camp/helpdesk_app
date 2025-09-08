<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * システム設定モデル
 * 
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string|null $description
 * @property bool $is_public
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class SystemSetting extends Model
{
    use HasFactory;

    /**
     * データベーステーブル名
     */
    protected $table = 'system_settings';

    /**
     * 主キー
     */
    protected $primaryKey = 'id';

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * デフォルト値
     */
    protected $attributes = [
        'is_public' => false,
    ];

    /**
     * キーでの検索スコープ
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * 公開設定のスコープ
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * 型別のスコープ
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 設定値を型に応じて変換して取得
     */
    public function getTypedValueAttribute()
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'array' => json_decode($this->value, true),
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    /**
     * 設定値を設定（型に応じて変換）
     */
    public function setTypedValue($value): void
    {
        $this->value = match ($this->type) {
            'boolean' => $value ? '1' : '0',
            'array', 'json' => json_encode($value),
            default => (string) $value,
        };
        $this->save();
    }

    /**
     * キーで設定値を取得
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::byKey($key)->first();
        return $setting ? $setting->typed_value : $default;
    }

    /**
     * キーで設定値を設定
     */
    public static function setValue(string $key, $value, string $type = 'string'): void
    {
        $setting = self::firstOrCreate(['key' => $key], ['type' => $type]);
        $setting->setTypedValue($value);
    }
}
