<?php

use App\Models\User;
use App\Models\Inquiry;
use App\Models\Category;
use Livewire\Volt\Volt;

test('CSV export button is shown when there are monthly inquiries', function () {
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

test('CSV export button is not shown when there are no monthly inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('inquiries.monthly'));
    $response->assertStatus(200);
    $response->assertDontSee('CSV出力');
});

test('CSV export works with monthly inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();
    $assignedUser = User::factory()->create(['name' => '担当者 太郎']);

    // 今月の問い合わせを作成
    $inquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'status' => 'completed',
        'category_id' => $category->id,
        'created_user_id' => $user->id,
        'assigned_user_id' => $assignedUser->id,
        'customer_id' => 'CUST001',
        'prefecture' => '東京都',
        'user_attribute' => '法人',
        'subject' => 'テスト問い合わせ',
        'response' => 'テスト回答内容',
    ]);

    $response = Volt::test('inquiries.monthly')
        ->call('exportCsv');

    // メソッドが正常に実行されることを確認
    $response->assertStatus(200);
});

test('CSV export shows warning when no inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = Volt::test('inquiries.monthly')
        ->call('exportCsv');

    // 警告メッセージが表示されることを確認
    $response->assertDispatched('show-message', [
        'type' => 'warning',
        'message' => '出力する問い合わせがありません。'
    ]);
});

test('CSV export works with filters applied', function () {
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

    $response = Volt::test('inquiries.monthly')
        ->set('selectedStatus', 'completed')
        ->call('exportCsv');

    $response->assertStatus(200);
});

test('filter reset functionality works', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = Volt::test('inquiries.monthly')
        ->set('selectedStatus', 'pending')
        ->set('selectedUser', '1')
        ->set('searchKeyword', 'test')
        ->call('resetFilters');

    $response->assertSet('selectedStatus', '');
    $response->assertSet('selectedUser', '');
    $response->assertSet('searchKeyword', '');
});
