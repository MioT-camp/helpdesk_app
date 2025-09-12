<?php

use App\Models\Category;
use App\Models\Inquiry;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    // 既存のデータをクリア（外部キー制約を考慮）
    Inquiry::query()->delete();
    Category::query()->delete();
    User::where('id', '>', 1)->delete(); // システムユーザー以外を削除

    $this->user = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->category = Category::factory()->create([
        'name' => 'テストカテゴリ',
        'slug' => 'test-category',
        'color' => '#3B82F6',
        'is_active' => true,
    ]);
});

test('期限切れの問い合わせがある場合、期限切れボタンが表示される', function () {
    // 期限切れの問い合わせを作成
    $overdueInquiry = Inquiry::factory()->create([
        'subject' => '期限切れの問い合わせ',
        'response_deadline' => now()->subDays(1), // 昨日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index');

    // 期限切れボタンが表示されることを確認
    $component->assertSee('期限切れ (1)');
});

test('期限切れの問い合わせがない場合、期限切れボタンが表示されない', function () {
    // 期限切れではない問い合わせのみ作成
    $notOverdueInquiry = Inquiry::factory()->create([
        'subject' => '期限切れではない問い合わせ',
        'response_deadline' => now()->addDays(1), // 明日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index');

    // 期限切れボタンが表示されないことを確認（ボタンのテキストを確認）
    $component->assertDontSee('期限切れ (');
});

test('期限切れボタンをクリックすると期限切れの問い合わせのみ表示される', function () {
    // 期限切れの問い合わせ
    $overdueInquiry = Inquiry::factory()->create([
        'subject' => '期限切れの問い合わせ',
        'response_deadline' => now()->subDays(1), // 昨日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 期限切れではない問い合わせ
    $notOverdueInquiry = Inquiry::factory()->create([
        'subject' => '期限切れではない問い合わせ',
        'response_deadline' => now()->addDays(1), // 明日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('overdue_only', true);

    $inquiries = $component->get('inquiries');

    // 期限切れの問い合わせのみ表示される
    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('期限切れの問い合わせ');
});

test('期限切れボタンをクリックすると期限切れフィルタが適用される', function () {
    // 期限切れの問い合わせ
    $overdueInquiry = Inquiry::factory()->create([
        'subject' => '期限切れの問い合わせ',
        'response_deadline' => now()->subDays(1), // 昨日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('overdue_only', true);

    // 期限切れフィルタが適用されていることを確認
    expect($component->get('overdue_only'))->toBe(true);

    // フィルタ状態表示に「期限切れのみ」が表示される
    $component->assertSee('期限切れのみ');
});

test('期限切れフィルタが適用されている時、期限切れボタンがアクティブ状態になる', function () {
    // 期限切れの問い合わせ
    $overdueInquiry = Inquiry::factory()->create([
        'subject' => '期限切れの問い合わせ',
        'response_deadline' => now()->subDays(1), // 昨日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('overdue_only', true);

    // 期限切れボタンがアクティブ状態（ring-2 ring-red-300クラス）になっていることを確認
    $component->assertSee('ring-2 ring-red-300');
});

test('期限切れフィルタが適用されている時、他のステータスフィルタは無効になる', function () {
    // 期限切れの問い合わせ（pending）
    $overdueInquiry = Inquiry::factory()->create([
        'subject' => '期限切れの問い合わせ',
        'response_deadline' => now()->subDays(1), // 昨日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 期限切れではない問い合わせ（in_progress）
    $notOverdueInquiry = Inquiry::factory()->create([
        'subject' => '期限切れではない問い合わせ',
        'response_deadline' => now()->addDays(1), // 明日が期限
        'status' => 'in_progress', // 対応中
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('overdue_only', true)
        ->set('status', 'in_progress'); // ステータスフィルタを設定

    $inquiries = $component->get('inquiries');

    // 期限切れフィルタが優先され、期限切れの問い合わせのみ表示される
    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('期限切れの問い合わせ');
});

test('期限切れフィルタが適用されている時、未対応フィルタは無効になる', function () {
    // 期限切れの問い合わせ
    $overdueInquiry = Inquiry::factory()->create([
        'subject' => '期限切れの問い合わせ',
        'response_deadline' => now()->subDays(1), // 昨日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('overdue_only', true)
        ->set('unclosed_only', true); // 未対応フィルタを設定

    $inquiries = $component->get('inquiries');

    // 期限切れフィルタが優先され、期限切れの問い合わせのみ表示される
    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('期限切れの問い合わせ');
});

test('期限切れフィルタのリセット機能が動作する', function () {
    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('overdue_only', true)
        ->call('resetFilters');

    expect($component->get('overdue_only'))->toBe(false);
});

test('期限切れの問い合わせ件数が正しく表示される', function () {
    // 複数の期限切れ問い合わせを作成
    $overdueInquiry1 = Inquiry::factory()->create([
        'subject' => '期限切れの問い合わせ1',
        'response_deadline' => now()->subDays(2), // 2日前が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $overdueInquiry2 = Inquiry::factory()->create([
        'subject' => '期限切れの問い合わせ2',
        'response_deadline' => now()->subDays(1), // 昨日が期限
        'status' => 'in_progress', // 対応中
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index');

    // 期限切れボタンに正しい件数が表示される
    $component->assertSee('期限切れ (2)');
});

test('期限切れだがクローズ済みの問い合わせは期限切れボタンにカウントされない', function () {
    // 期限切れだがクローズ済みの問い合わせ
    $overdueClosedInquiry = Inquiry::factory()->create([
        'subject' => '期限切れだがクローズ済みの問い合わせ',
        'response_deadline' => now()->subDays(1), // 昨日が期限
        'status' => 'closed', // クローズ済み
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 期限切れで未対応の問い合わせ
    $overduePendingInquiry = Inquiry::factory()->create([
        'subject' => '期限切れで未対応の問い合わせ',
        'response_deadline' => now()->subDays(1), // 昨日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index');

    // 期限切れボタンに1件のみカウントされる（クローズ済みは除外）
    $component->assertSee('期限切れ (1)');
});
