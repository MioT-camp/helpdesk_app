<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * ユーザー管理のPolicy
 * 管理者のみがユーザー管理機能にアクセス可能
 */
class UserPolicy
{
    /**
     * ユーザー一覧の表示権限
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * ユーザー詳細の表示権限
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * ユーザー作成権限
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * ユーザー更新権限
     */
    public function update(User $user, User $model): bool
    {
        // 管理者のみ更新可能
        // 自分自身の削除は禁止
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * ユーザー削除権限
     */
    public function delete(User $user, User $model): bool
    {
        // 管理者のみ削除可能
        // 自分自身の削除は禁止
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * ユーザー復元権限
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * ユーザー完全削除権限
     */
    public function forceDelete(User $user, User $model): bool
    {
        // 管理者のみ完全削除可能
        // 自分自身の削除は禁止
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * 権限変更権限
     */
    public function changeRole(User $user, User $model): bool
    {
        // 管理者のみ権限変更可能
        // 自分自身の権限変更は禁止
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * アクティブ状態変更権限
     */
    public function toggleActive(User $user, User $model): bool
    {
        // 管理者のみアクティブ状態変更可能
        // 自分自身の状態変更は禁止
        return $user->isAdmin() && $user->id !== $model->id;
    }
}
