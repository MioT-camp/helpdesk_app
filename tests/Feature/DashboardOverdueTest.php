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

test('ダッシュボードで期限切れの問い合わせ件数が正しく表示される', function () {
    // 期限切れの問い合わせ（期限が過ぎていて、クローズされていない）
    $overdueInquiry = Inquiry::factory()->create([
        'subject' => '期限切れの問い合わせ',
        'response_deadline' => now()->subDays(1), // 昨日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 期限切れだがクローズ済みの問い合わせ（カウントされない）
    $overdueClosedInquiry = Inquiry::factory()->create([
        'subject' => '期限切れだがクローズ済みの問い合わせ',
        'response_deadline' => now()->subDays(1), // 昨日が期限
        'status' => 'closed', // クローズ済み
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 期限がまだ来ていない問い合わせ（カウントされない）
    $notOverdueInquiry = Inquiry::factory()->create([
        'subject' => '期限がまだ来ていない問い合わせ',
        'response_deadline' => now()->addDays(1), // 明日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 期限が設定されていない問い合わせ（カウントされない）
    $noDeadlineInquiry = Inquiry::factory()->create([
        'subject' => '期限が設定されていない問い合わせ',
        'response_deadline' => null, // 期限なし
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('dashboard.index');

    $stats = $component->get('stats');

    // 期限切れの問い合わせは1件のみ
    expect($stats['overdue_inquiries'])->toBe(1);
});

test('ダッシュボードで期限切れの問い合わせ件数が0件の場合', function () {
    // 期限切れではない問い合わせのみ作成
    $notOverdueInquiry = Inquiry::factory()->create([
        'subject' => '期限がまだ来ていない問い合わせ',
        'response_deadline' => now()->addDays(1), // 明日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('dashboard.index');

    $stats = $component->get('stats');

    // 期限切れの問い合わせは0件
    expect($stats['overdue_inquiries'])->toBe(0);
});

test('ダッシュボードで期限切れの問い合わせ件数が複数件の場合', function () {
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

    $overdueInquiry3 = Inquiry::factory()->create([
        'subject' => '期限切れの問い合わせ3',
        'response_deadline' => now()->subHours(2), // 2時間前が期限
        'status' => 'completed', // 回答作成済
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('dashboard.index');

    $stats = $component->get('stats');

    // 期限切れの問い合わせは3件
    expect($stats['overdue_inquiries'])->toBe(3);
});

test('ダッシュボードで期限切れの問い合わせ件数がクローズ済みは除外される', function () {
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

    $component = Livewire::test('dashboard.index');

    $stats = $component->get('stats');

    // 期限切れの問い合わせは1件のみ（クローズ済みは除外）
    expect($stats['overdue_inquiries'])->toBe(1);
});

test('ダッシュボードで期限切れの問い合わせ件数が期限なしは除外される', function () {
    // 期限が設定されていない問い合わせ
    $noDeadlineInquiry = Inquiry::factory()->create([
        'subject' => '期限が設定されていない問い合わせ',
        'response_deadline' => null, // 期限なし
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 期限切れの問い合わせ
    $overdueInquiry = Inquiry::factory()->create([
        'subject' => '期限切れの問い合わせ',
        'response_deadline' => now()->subDays(1), // 昨日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('dashboard.index');

    $stats = $component->get('stats');

    // 期限切れの問い合わせは1件のみ（期限なしは除外）
    expect($stats['overdue_inquiries'])->toBe(1);
});

test('ダッシュボードで期限切れの問い合わせ件数が期限がまだ来ていない場合は除外される', function () {
    // 期限がまだ来ていない問い合わせ
    $notOverdueInquiry = Inquiry::factory()->create([
        'subject' => '期限がまだ来ていない問い合わせ',
        'response_deadline' => now()->addDays(1), // 明日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 期限切れの問い合わせ
    $overdueInquiry = Inquiry::factory()->create([
        'subject' => '期限切れの問い合わせ',
        'response_deadline' => now()->subDays(1), // 昨日が期限
        'status' => 'pending', // 未対応
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('dashboard.index');

    $stats = $component->get('stats');

    // 期限切れの問い合わせは1件のみ（期限がまだ来ていない場合は除外）
    expect($stats['overdue_inquiries'])->toBe(1);
});
