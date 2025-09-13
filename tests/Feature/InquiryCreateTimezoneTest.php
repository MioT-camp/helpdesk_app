<?php

use App\Models\User;
use App\Models\Category;
use Livewire\Volt\Volt;

test('inquiry creation with today date works with Asia/Tokyo timezone', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 今日の日付で問い合わせを作成
    $today = now()->format('Y-m-d H:i:s');

    $response = Volt::test('inquiries.create')
        ->set('sender_email', 'test@example.com')
        ->set('subject', 'テスト問い合わせ')
        ->set('content', 'テスト内容')
        ->set('received_at', $today)
        ->set('category_id', $category->id)
        ->call('save');

    $response->assertHasNoErrors();
    $response->assertRedirect();
});

test('inquiry creation with future date fails validation', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 未来の日付で問い合わせを作成（エラーになるはず）
    $futureDate = now()->addDay()->format('Y-m-d H:i:s');

    $response = Volt::test('inquiries.create')
        ->set('sender_email', 'test@example.com')
        ->set('subject', 'テスト問い合わせ')
        ->set('content', 'テスト内容')
        ->set('received_at', $futureDate)
        ->set('category_id', $category->id)
        ->call('save');

    $response->assertHasErrors(['received_at']);
});

test('inquiry creation with yesterday date works', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 昨日の日付で問い合わせを作成
    $yesterday = now()->subDay()->format('Y-m-d H:i:s');

    $response = Volt::test('inquiries.create')
        ->set('sender_email', 'test@example.com')
        ->set('subject', 'テスト問い合わせ')
        ->set('content', 'テスト内容')
        ->set('received_at', $yesterday)
        ->set('category_id', $category->id)
        ->call('save');

    $response->assertHasNoErrors();
    $response->assertRedirect();
});
