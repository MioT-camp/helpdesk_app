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

test('問い合わせ一覧で担当者フィルタが動作する', function () {
    // 担当者ユーザーを作成
    $assignedUser = User::factory()->create([
        'name' => '田中太郎',
        'role' => 'staff',
        'is_active' => true,
    ]);

    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '田中担当の問い合わせ',
        'assigned_user_id' => $assignedUser->id,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => '未割り当ての問い合わせ',
        'assigned_user_id' => null,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('assigned_user_id', $assignedUser->id);

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('田中担当の問い合わせ');
});

test('問い合わせ一覧で未割り当てフィルタが動作する', function () {
    // 担当者ユーザーを作成
    $assignedUser = User::factory()->create([
        'name' => '田中太郎',
        'role' => 'staff',
        'is_active' => true,
    ]);

    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '田中担当の問い合わせ',
        'assigned_user_id' => $assignedUser->id,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => '未割り当ての問い合わせ',
        'assigned_user_id' => null,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('assigned_user_id', 'unassigned');

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('未割り当ての問い合わせ');
});

test('問い合わせ一覧で今月フィルタが動作する', function () {
    // 今月の問い合わせ
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '今月の問い合わせ',
        'received_at' => now()->startOfMonth()->addDays(5),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 先月の問い合わせ
    $inquiry2 = Inquiry::factory()->create([
        'subject' => '先月の問い合わせ',
        'received_at' => now()->subMonth()->addDays(5),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('date_period', 'this_month');

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('今月の問い合わせ');
});

test('問い合わせ一覧で先月フィルタが動作する', function () {
    // 今月の問い合わせ
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '今月の問い合わせ',
        'received_at' => now()->startOfMonth()->addDays(5),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 先月の問い合わせ
    $inquiry2 = Inquiry::factory()->create([
        'subject' => '先月の問い合わせ',
        'received_at' => now()->subMonth()->addDays(5),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('date_period', 'last_month');

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('先月の問い合わせ');
});

test('問い合わせ一覧で今年フィルタが動作する', function () {
    // 今年の問い合わせ
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '今年の問い合わせ',
        'received_at' => now()->startOfYear()->addMonths(3),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 去年の問い合わせ
    $inquiry2 = Inquiry::factory()->create([
        'subject' => '去年の問い合わせ',
        'received_at' => now()->subYear()->addMonths(3),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('date_period', 'this_year');

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('今年の問い合わせ');
});

test('問い合わせ一覧でカスタム期間フィルタが動作する', function () {
    $startDate = now()->subDays(10)->format('Y-m-d');
    $endDate = now()->subDays(5)->format('Y-m-d');

    // 期間内の問い合わせ
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '期間内の問い合わせ',
        'received_at' => now()->subDays(7),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 期間外の問い合わせ
    $inquiry2 = Inquiry::factory()->create([
        'subject' => '期間外の問い合わせ',
        'received_at' => now()->subDays(15),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('date_period', 'custom')
        ->set('date_from', $startDate)
        ->set('date_to', $endDate);

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('期間内の問い合わせ');
});

test('問い合わせ一覧でカスタム期間フィルタ（開始日のみ）が動作する', function () {
    $startDate = now()->subDays(10)->format('Y-m-d');

    // 開始日以降の問い合わせ
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '開始日以降の問い合わせ',
        'received_at' => now()->subDays(5),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 開始日より前の問い合わせ
    $inquiry2 = Inquiry::factory()->create([
        'subject' => '開始日より前の問い合わせ',
        'received_at' => now()->subDays(15),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('date_period', 'custom')
        ->set('date_from', $startDate);

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('開始日以降の問い合わせ');
});

test('問い合わせ一覧でカスタム期間フィルタ（終了日のみ）が動作する', function () {
    $endDate = now()->subDays(5)->format('Y-m-d');

    // 終了日以前の問い合わせ
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '終了日以前の問い合わせ',
        'received_at' => now()->subDays(7),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 終了日より後の問い合わせ
    $inquiry2 = Inquiry::factory()->create([
        'subject' => '終了日より後の問い合わせ',
        'received_at' => now()->subDays(2),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('date_period', 'custom')
        ->set('date_to', $endDate);

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('終了日以前の問い合わせ');
});

test('問い合わせ一覧で複数フィルタの組み合わせが動作する', function () {
    // 担当者ユーザーを作成
    $assignedUser = User::factory()->create([
        'name' => '田中太郎',
        'role' => 'staff',
        'is_active' => true,
    ]);

    // 今月の田中担当の問い合わせ
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '今月の田中担当の問い合わせ',
        'assigned_user_id' => $assignedUser->id,
        'received_at' => now()->startOfMonth()->addDays(5),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 先月の田中担当の問い合わせ
    $inquiry2 = Inquiry::factory()->create([
        'subject' => '先月の田中担当の問い合わせ',
        'assigned_user_id' => $assignedUser->id,
        'received_at' => now()->subMonth()->addDays(5),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    // 今月の未割り当ての問い合わせ
    $inquiry3 = Inquiry::factory()->create([
        'subject' => '今月の未割り当ての問い合わせ',
        'assigned_user_id' => null,
        'received_at' => now()->startOfMonth()->addDays(5),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('assigned_user_id', $assignedUser->id)
        ->set('date_period', 'this_month');

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('今月の田中担当の問い合わせ');
});

test('フィルタのリセット機能が動作する', function () {
    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('assigned_user_id', '1')
        ->set('date_period', 'this_month')
        ->set('date_from', '2024-01-01')
        ->set('date_to', '2024-01-31')
        ->call('resetFilters');

    expect($component->get('assigned_user_id'))->toBe('');
    expect($component->get('date_period'))->toBe('');
    expect($component->get('date_from'))->toBe('');
    expect($component->get('date_to'))->toBe('');
});
