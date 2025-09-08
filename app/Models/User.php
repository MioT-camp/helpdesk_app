<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * ユーザーモデル（ヘルプデスク拡張版）
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string|null $department
 * @property array|null $specialties
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * 権限定数
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_STAFF = 'staff';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department',
        'specialties',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'specialties' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * デフォルト値
     */
    protected $attributes = [
        'role' => self::ROLE_STAFF,
        'is_active' => true,
    ];

    /**
     * 作成したFAQとのリレーション
     */
    public function createdFaqs(): HasMany
    {
        return $this->hasMany(FAQ::class, 'user_id');
    }

    /**
     * 担当している問い合わせとのリレーション
     */
    public function assignedInquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'assigned_user_id');
    }

    /**
     * 作成した問い合わせとのリレーション
     */
    public function createdInquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'created_user_id');
    }

    /**
     * アップロードした添付ファイルとのリレーション
     */
    public function uploadedAttachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'uploaded_by');
    }

    /**
     * 問い合わせ履歴とのリレーション
     */
    public function inquiryHistories(): HasMany
    {
        return $this->hasMany(InquiryHistory::class, 'user_id');
    }

    /**
     * FAQ閲覧履歴とのリレーション
     */
    public function faqViews(): HasMany
    {
        return $this->hasMany(FaqView::class, 'user_id');
    }

    /**
     * アクティブなユーザーのスコープ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 権限別のスコープ
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * 部署別のスコープ
     */
    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    /**
     * 専門分野での検索スコープ
     */
    public function scopeBySpecialty($query, string $specialty)
    {
        return $query->whereJsonContains('specialties', $specialty);
    }

    /**
     * 管理者権限のスコープ
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    /**
     * マネージャー権限のスコープ
     */
    public function scopeManagers($query)
    {
        return $query->where('role', self::ROLE_MANAGER);
    }

    /**
     * スタッフ権限のスコープ
     */
    public function scopeStaff($query)
    {
        return $query->where('role', self::ROLE_STAFF);
    }

    /**
     * 権限の日本語表示
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => '管理者',
            self::ROLE_MANAGER => 'マネージャー',
            self::ROLE_STAFF => 'スタッフ',
            default => '不明',
        };
    }

    /**
     * 管理者かどうか
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * マネージャーかどうか
     */
    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    /**
     * スタッフかどうか
     */
    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    /**
     * 管理者またはマネージャーかどうか
     */
    public function canManage(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    /**
     * 専門分野を持っているかどうか
     */
    public function hasSpecialty(string $specialty): bool
    {
        return in_array($specialty, $this->specialties ?? []);
    }

    /**
     * 最終ログイン時刻を更新
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
