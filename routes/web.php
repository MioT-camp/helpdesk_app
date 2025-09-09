<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
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
    Volt::route('inquiries/{inquiry_id}', 'inquiries.show')->name('inquiries.show');

    // カテゴリ管理
    Volt::route('categories', 'categories.index')->name('categories.index');

    // 設定
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
