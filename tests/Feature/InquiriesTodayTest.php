<?php

use App\Models\User;
use App\Models\Inquiry;
use App\Models\Category;

test('guests are redirected to the login page when accessing today inquiries', function () {
    $response = $this->get(route('inquiries.today'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit today inquiries page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('inquiries.today'));
    $response->assertStatus(200);
    $response->assertSee('本日の問い合わせ');
});

test('today inquiries page shows today inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 今日の問い合わせを作成
    $todayInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    // 昨日の問い合わせを作成（表示されないはず）
    $yesterdayInquiry = Inquiry::factory()->create([
        'received_at' => now()->subDay(),
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    $response = $this->get(route('inquiries.today'));
    $response->assertStatus(200);
    $response->assertSee($todayInquiry->subject);
    $response->assertDontSee($yesterdayInquiry->subject);
});

test('today inquiries page shows correct statistics', function () {
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

    $response = $this->get(route('inquiries.today'));
    $response->assertStatus(200);
    $response->assertSee('2件'); // 本日総数
    $response->assertSee('1件'); // クローズ数
});

test('today inquiries page shows empty state when no inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('inquiries.today'));
    $response->assertStatus(200);
    $response->assertSee('本日の問い合わせはありません');
    $response->assertSee('0件'); // 本日総数
    $response->assertSee('0件'); // クローズ数
});
