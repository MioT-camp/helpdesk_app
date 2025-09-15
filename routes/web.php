<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    // ダッシュボード
    Volt::route('dashboard', 'dashboard.index')->name('dashboard');

    // FAQ管理
    Volt::route('faqs', 'faqs.index')->name('faqs.index');
    Volt::route('faqs/create', 'faqs.create')->name('faqs.create');
    Volt::route('faqs/{faq_id}', 'faqs.show')->name('faqs.show');
    Volt::route('faqs/{faq_id}/edit', 'faqs.edit')->name('faqs.edit');

    // 問い合わせ管理
    Volt::route('inquiries', 'inquiries.index')->name('inquiries.index');
    Volt::route('inquiries/create', 'inquiries.create')->name('inquiries.create');
    Volt::route('inquiries/today', 'inquiries.today')->name('inquiries.today');
    Volt::route('inquiries/monthly', 'inquiries.monthly')->name('inquiries.monthly');
    Volt::route('inquiries/{inquiry_id}', 'inquiries.show')->name('inquiries.show');

    // カテゴリ管理
    Volt::route('categories', 'categories.index')->name('categories.index');

    // レポート
    Volt::route('reports', 'reports.index')->name('reports.index');
    Volt::route('reports/trends', 'reports.trends')->name('reports.trends');

    // ToDo管理
    Volt::route('todos', 'todos.index')->name('todos.index');
    Volt::route('todos/create', 'todos.create')->name('todos.create');
    Volt::route('todos/{todo}/edit', 'todos.edit')->name('todos.edit');

    // 設定
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

// 管理者専用ルート
Route::middleware(['auth', 'admin'])->group(function () {
    // ユーザー管理
    Volt::route('admin/users', 'admin.users.index')->name('admin.users.index');
    Volt::route('admin/users/create', 'admin.users.create')->name('admin.users.create');
    Volt::route('admin/users/{user_id}', 'admin.users.show')->name('admin.users.show');
    Volt::route('admin/users/{user_id}/edit', 'admin.users.edit')->name('admin.users.edit');
    Volt::route('admin/users-test', 'admin.users.test')->name('admin.users.test');
});

require __DIR__ . '/auth.php';
