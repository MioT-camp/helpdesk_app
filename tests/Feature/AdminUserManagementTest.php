<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
    $this->staff = User::factory()->create(['role' => User::ROLE_STAFF]);
});

test('管理者はユーザー一覧にアクセスできる', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.index'))
        ->assertOk();
});

test('管理者以外はユーザー一覧にアクセスできない', function () {
    $this->actingAs($this->manager)
        ->get(route('admin.users.index'))
        ->assertForbidden();

    $this->actingAs($this->staff)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('管理者は新規ユーザー作成ページにアクセスできる', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.create'))
        ->assertOk();
});

test('管理者はユーザー詳細ページにアクセスできる', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.show', $this->staff->id))
        ->assertOk();
});

test('管理者はユーザー編集ページにアクセスできる', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.edit', $this->staff->id))
        ->assertOk();
});

test('管理者は自分自身を削除できない', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.show', $this->admin->id))
        ->assertOk();

    // 自分自身の削除ボタンが表示されないことを確認
    $this->actingAs($this->admin)
        ->get(route('admin.users.show', $this->admin->id))
        ->assertDontSee('削除');
});

test('管理者は新規ユーザーを作成できる', function () {
    $userData = [
        'name' => 'テストユーザー',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => User::ROLE_STAFF,
        'department' => 'テスト部署',
        'is_active' => true,
    ];

    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), $userData)
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'name' => 'テストユーザー',
        'email' => 'test@example.com',
        'role' => User::ROLE_STAFF,
    ]);
});

test('管理者はユーザー情報を更新できる', function () {
    $updateData = [
        'name' => '更新された名前',
        'email' => $this->staff->email,
        'role' => User::ROLE_MANAGER,
        'department' => '更新された部署',
        'is_active' => true,
    ];

    $this->actingAs($this->admin)
        ->put(route('admin.users.update', $this->staff->id), $updateData)
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id' => $this->staff->id,
        'name' => '更新された名前',
        'role' => User::ROLE_MANAGER,
    ]);
});

test('管理者はユーザーを削除できる', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $this->staff->id))
        ->assertRedirect();

    $this->assertSoftDeleted('users', [
        'id' => $this->staff->id,
    ]);
});

test('管理者はユーザーのアクティブ状態を変更できる', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.users.toggle-active', $this->staff->id))
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id' => $this->staff->id,
        'is_active' => false,
    ]);
});
