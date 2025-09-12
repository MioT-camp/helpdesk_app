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

test('問い合わせ一覧で件名で検索できる', function () {
    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => 'ログインエラーについて',
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => 'パスワードリセットについて',
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('search', 'ログイン');

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('ログインエラーについて');
});

test('問い合わせ一覧で内容で検索できる', function () {
    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => 'システムエラー',
        'content' => 'ログイン時にエラーが発生します',
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => 'パスワード問題',
        'content' => 'パスワードを忘れました',
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('search', 'ログイン');

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('システムエラー');
});

test('問い合わせ一覧で顧客IDで検索できる', function () {
    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '問い合わせ1',
        'customer_id' => 'CUST001',
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => '問い合わせ2',
        'customer_id' => 'CUST002',
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('search', 'CUST001');

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('問い合わせ1');
});

test('問い合わせ一覧で担当者名で検索できる', function () {
    // 担当者ユーザーを作成
    $assignedUser = User::factory()->create([
        'name' => '田中太郎',
        'role' => 'staff',
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
        ->set('search', '田中');

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('田中担当の問い合わせ');
});

test('問い合わせ一覧で担当者の姓で検索できる', function () {
    // 担当者ユーザーを作成
    $assignedUser = User::factory()->create([
        'name' => '佐藤花子',
        'role' => 'staff',
    ]);

    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '佐藤担当の問い合わせ',
        'assigned_user_id' => $assignedUser->id,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => '田中担当の問い合わせ',
        'assigned_user_id' => null,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('search', '佐藤');

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('佐藤担当の問い合わせ');
});

test('問い合わせ一覧で担当者の名で検索できる', function () {
    // 担当者ユーザーを作成
    $assignedUser = User::factory()->create([
        'name' => '山田一郎',
        'role' => 'staff',
    ]);

    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => '山田担当の問い合わせ',
        'assigned_user_id' => $assignedUser->id,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => '山田二郎担当の問い合わせ',
        'assigned_user_id' => null,
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('search', '一郎');

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(1);
    expect($inquiries->first()->subject)->toBe('山田担当の問い合わせ');
});

test('問い合わせ一覧で複数キーワードで検索できる（OR条件）', function () {
    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => 'ログインエラーについて',
        'content' => 'システムにログインできません',
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $inquiry2 = Inquiry::factory()->create([
        'subject' => 'パスワードエラーについて',
        'content' => 'パスワードが間違っています',
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('search', 'ログイン エラー');

    $inquiries = $component->get('inquiries');

    // OR条件なので、両方の問い合わせがヒットする
    expect($inquiries)->toHaveCount(2);

    $subjects = $inquiries->pluck('subject')->toArray();
    expect($subjects)->toContain('ログインエラーについて');
    expect($subjects)->toContain('パスワードエラーについて');
});

test('問い合わせ一覧で検索結果がない場合の表示', function () {
    // テストデータ作成
    $inquiry1 = Inquiry::factory()->create([
        'subject' => 'ログインエラーについて',
        'category_id' => $this->category->id,
        'created_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('search', '存在しないキーワード');

    $inquiries = $component->get('inquiries');

    expect($inquiries)->toHaveCount(0);
});

test('問い合わせ一覧で検索フィルタのリセット機能が動作する', function () {
    $this->actingAs($this->user);

    $component = Livewire::test('inquiries.index')
        ->set('search', 'テスト検索')
        ->call('resetFilters');

    expect($component->get('search'))->toBe('');
});
