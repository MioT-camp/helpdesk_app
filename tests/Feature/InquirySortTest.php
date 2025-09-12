<?php

use App\Models\Category;
use App\Models\Inquiry;
use App\Models\User;
use Livewire\Livewire;
use Livewire\Volt\Volt;

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

test('問い合わせ一覧で受信日時順（新しい順）でソートできる', function () {
    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '古い問い合わせ',
        'received_at' => now()->subDays(2),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => '新しい問い合わせ',
        'received_at' => now()->subDays(1),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('sort', 'latest');

    $inquiries = $component->get('inquiries');

    expect($inquiries->first()->subject)->toBe('新しい問い合わせ');
    expect($inquiries->last()->subject)->toBe('古い問い合わせ');
});

test('問い合わせ一覧で受信日時順（古い順）でソートできる', function () {
    // テストデータ作成（明示的に時間を設定）
    $oldTime = now()->subDays(2);
    $newTime = now()->subDays(1);

    $inquiry1 = Inquiry::factory()->create([
        'subject' => '古い問い合わせ',
        'received_at' => $oldTime,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => '新しい問い合わせ',
        'received_at' => $newTime,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('sort', 'oldest');

    $inquiries = $component->get('inquiries');

    // 古い順なので、古い問い合わせが最初に来るはず
    // 実際の順序を確認
    $subjects = $inquiries->pluck('subject')->toArray();
    expect($subjects[0])->toBe('古い問い合わせ');
    expect($subjects[1])->toBe('新しい問い合わせ');
});

test('問い合わせ一覧で優先度順（高い順）でソートできる', function () {
    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '低優先度の問い合わせ',
        'priority' => 1,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => '高優先度の問い合わせ',
        'priority' => 3,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('sort', 'priority');

    $inquiries = $component->get('inquiries');

    expect($inquiries->first()->subject)->toBe('高優先度の問い合わせ');
    expect($inquiries->last()->subject)->toBe('低優先度の問い合わせ');
});

test('問い合わせ一覧で優先度順（低い順）でソートできる', function () {
    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '低優先度の問い合わせ',
        'priority' => 1,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => '高優先度の問い合わせ',
        'priority' => 3,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('sort', 'priority_asc');

    $inquiries = $component->get('inquiries');

    expect($inquiries->first()->subject)->toBe('低優先度の問い合わせ');
    expect($inquiries->last()->subject)->toBe('高優先度の問い合わせ');
});

test('問い合わせ一覧で期限順（近い順）でソートできる', function () {
    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '期限の遠い問い合わせ',
        'response_deadline' => now()->addDays(3),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => '期限の近い問い合わせ',
        'response_deadline' => now()->addDays(1),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('sort', 'deadline');

    $inquiries = $component->get('inquiries');

    expect($inquiries->first()->subject)->toBe('期限の近い問い合わせ');
    expect($inquiries->last()->subject)->toBe('期限の遠い問い合わせ');
});

test('問い合わせ一覧で期限順（遠い順）でソートできる', function () {
    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '期限の遠い問い合わせ',
        'response_deadline' => now()->addDays(3),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => '期限の近い問い合わせ',
        'response_deadline' => now()->addDays(1),
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('sort', 'deadline_desc');

    $inquiries = $component->get('inquiries');

    expect($inquiries->first()->subject)->toBe('期限の遠い問い合わせ');
    expect($inquiries->last()->subject)->toBe('期限の近い問い合わせ');
});

test('問い合わせ一覧でステータス順でソートできる', function () {
    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '完了済みの問い合わせ',
        'status' => 'closed',
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => '未対応の問い合わせ',
        'status' => 'pending',
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('sort', 'status');

    $inquiries = $component->get('inquiries');

    expect($inquiries->first()->subject)->toBe('未対応の問い合わせ');
    expect($inquiries->last()->subject)->toBe('完了済みの問い合わせ');
});

test('問い合わせ一覧で担当者順でソートできる', function () {
    $user1 = User::factory()->create(['name' => '田中太郎']);
    $user2 = User::factory()->create(['name' => '佐藤花子']);

    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '佐藤担当の問い合わせ',
        'assigned_user_id' => $user2->id,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => '田中担当の問い合わせ',
        'assigned_user_id' => $user1->id,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('sort', 'assigned_user');

    $inquiries = $component->get('inquiries');

    expect($inquiries->first()->subject)->toBe('田中担当の問い合わせ');
    expect($inquiries->last()->subject)->toBe('佐藤担当の問い合わせ');
});

test('問い合わせ一覧でカテゴリ順でソートできる', function () {
    $category1 = Category::factory()->create(['name' => 'Aシステム']);
    $category2 = Category::factory()->create(['name' => 'Bシステム']);

    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => 'Bシステムの問い合わせ',
        'category_id' => $category2->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => 'Aシステムの問い合わせ',
        'category_id' => $category1->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('sort', 'category');

    $inquiries = $component->get('inquiries');

    expect($inquiries->first()->subject)->toBe('Aシステムの問い合わせ');
    expect($inquiries->last()->subject)->toBe('Bシステムの問い合わせ');
});

test('並び順のリセット機能が動作する', function () {
    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('sort', 'priority')
        ->call('resetFilters');

    expect($component->get('sort'))->toBe('latest');
});
