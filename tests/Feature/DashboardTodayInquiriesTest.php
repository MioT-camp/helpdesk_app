<?php

use App\Models\User;
use App\Models\Inquiry;
use App\Models\Category;

test('dashboard shows today inquiries statistics', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 今日の問い合わせを作成（クローズ済み）
    $closedInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'status' => 'closed',
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    // 今日の問い合わせを作成（未対応）
    $pendingInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'status' => 'pending',
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('本日の対応状況');
    $response->assertSee('1'); // クローズ数
    $response->assertSee('2'); // 本日総数
});

test('dashboard today inquiries card links to today inquiries page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('href="' . route('inquiries.today') . '"', false);
});

test('dashboard shows correct today statistics when no inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('本日の対応状況');
    $response->assertSee('0'); // クローズ数
    $response->assertSee('0'); // 本日総数
});
