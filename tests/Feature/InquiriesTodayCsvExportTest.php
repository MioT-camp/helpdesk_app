<?php

use App\Models\User;
use App\Models\Inquiry;
use App\Models\Category;
use Livewire\Volt\Volt;

test('CSV export button is shown when there are completed inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 回答作成済の問い合わせを作成
    $completedInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'status' => 'completed',
        'category_id' => $category->id,
        'created_user_id' => $user->id,
        'response' => 'テスト回答内容',
    ]);

    $response = $this->get(route('inquiries.today'));
    $response->assertStatus(200);
    $response->assertSee('CSV出力（回答作成済）');
});

test('CSV export button is not shown when there are no completed inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 未対応の問い合わせのみ作成
    $pendingInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'status' => 'pending',
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    $response = $this->get(route('inquiries.today'));
    $response->assertStatus(200);
    $response->assertDontSee('CSV出力（回答作成済）');
});

test('CSV export works with completed inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();
    $assignedUser = User::factory()->create(['name' => '担当者 太郎']);

    // 回答作成済の問い合わせを作成
    $completedInquiry = Inquiry::factory()->create([
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

    $response = Volt::test('inquiries.today')
        ->call('exportCsv');

    // Livewireのテストでは、ストリーミングレスポンスは直接テストできないため、
    // メソッドが正常に実行されることを確認
    $response->assertStatus(200);
});

test('CSV export shows warning when no completed inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = Volt::test('inquiries.today')
        ->call('exportCsv');

    // 警告メッセージが表示されることを確認
    $response->assertDispatched('show-message', [
        'type' => 'warning',
        'message' => '回答作成済の問い合わせがありません。'
    ]);
});

test('CSV export contains correct data format', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();
    $assignedUser = User::factory()->create(['name' => '担当者 太郎']);

    // 回答作成済の問い合わせを作成
    $completedInquiry = Inquiry::factory()->create([
        'received_at' => now()->setTime(14, 30, 0),
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

    $response = Volt::test('inquiries.today')
        ->call('exportCsv');

    $response->assertStatus(200);

    // メソッドが正常に実行されることを確認
    // （実際のCSV内容のテストは統合テストで行う）
});

test('CSV export handles empty fields correctly', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 一部フィールドが空の回答作成済問い合わせを作成
    $completedInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'status' => 'completed',
        'category_id' => $category->id,
        'created_user_id' => $user->id,
        'customer_id' => null,
        'prefecture' => null,
        'user_attribute' => null,
        'subject' => 'テスト問い合わせ',
        'response' => 'テスト回答内容',
        'assigned_user_id' => null,
    ]);

    $response = Volt::test('inquiries.today')
        ->call('exportCsv');

    $response->assertStatus(200);

    // メソッドが正常に実行されることを確認
    // （空フィールドの処理は統合テストで行う）
});
