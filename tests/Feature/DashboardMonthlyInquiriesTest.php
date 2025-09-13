<?php

use App\Models\User;
use App\Models\Inquiry;
use App\Models\Category;

test('dashboard shows monthly inquiries statistics', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 今月の問い合わせを作成
    $thisMonthInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    // 先月の問い合わせを作成（カウントされないはず）
    $lastMonthInquiry = Inquiry::factory()->create([
        'received_at' => now()->subMonth(),
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('今月の対応状況');
    $response->assertSee('1'); // 今月の総数
});

test('dashboard monthly inquiries card links to monthly inquiries page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('href="' . route('inquiries.monthly') . '"', false);
});

test('dashboard shows correct monthly statistics when no inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('今月の対応状況');
    $response->assertSee('0'); // 今月の総数
});

test('dashboard monthly card shows current month in Japanese format', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee(now()->format('Y年n月') . 'の総数');
});
