<?php

use App\Models\User;
use App\Models\Inquiry;
use App\Models\Category;

test('guests are redirected to the login page when accessing monthly inquiries', function () {
    $response = $this->get(route('inquiries.monthly'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit monthly inquiries page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('inquiries.monthly'));
    $response->assertStatus(200);
    $response->assertSee('今月の問い合わせ');
});

test('monthly inquiries page shows this month inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 今月の問い合わせを作成
    $thisMonthInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    // 先月の問い合わせを作成（表示されないはず）
    $lastMonthInquiry = Inquiry::factory()->create([
        'received_at' => now()->subMonth(),
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    $response = $this->get(route('inquiries.monthly'));
    $response->assertStatus(200);
    $response->assertSee($thisMonthInquiry->subject);
    $response->assertDontSee($lastMonthInquiry->subject);
});

test('monthly inquiries page shows correct statistics', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 今月の問い合わせを作成（各ステータス）
    $pendingInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'status' => 'pending',
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    $completedInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'status' => 'completed',
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    $response = $this->get(route('inquiries.monthly'));
    $response->assertStatus(200);
    $response->assertSee('2'); // 総数
    $response->assertSee('1'); // 未対応
    $response->assertSee('1'); // 回答済
});

test('monthly inquiries page shows empty state when no inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('inquiries.monthly'));
    $response->assertStatus(200);
    $response->assertSee('今月の問い合わせはありません');
    $response->assertSee('0'); // 総数
});

test('monthly inquiries page has filter functionality', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 異なるステータスの問い合わせを作成
    $pendingInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'status' => 'pending',
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    $completedInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'status' => 'completed',
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    $response = $this->get(route('inquiries.monthly'));
    $response->assertStatus(200);
    $response->assertSee('ステータス');
    $response->assertSee('担当者');
    $response->assertSee('検索');
    $response->assertSee('フィルタリセット');
});

test('monthly inquiries page has CSV export button', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 今月の問い合わせを作成
    $inquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    $response = $this->get(route('inquiries.monthly'));
    $response->assertStatus(200);
    $response->assertSee('CSV出力');
});
