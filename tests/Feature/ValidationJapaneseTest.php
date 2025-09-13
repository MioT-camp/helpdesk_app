<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Category;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create([
        'role' => 'admin',
    ]);

    // テスト用のカテゴリを作成
    $this->category = Category::factory()->create([
        'name' => 'テストカテゴリ',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $this->actingAs($this->user);
});

test('バリデーションメッセージが日本語で表示される', function () {
    // FAQ作成フォームでバリデーションエラーを発生させる
    $component = Livewire::test('faqs.create')
        ->set('question', '') // 必須項目を空にする
        ->set('answer', '') // 必須項目を空にする
        ->set('category_id', '') // 必須項目を空にする
        ->call('save');

    // 日本語のバリデーションメッセージが表示されることを確認
    $component->assertHasErrors(['question', 'answer', 'category_id']);

    // 具体的なメッセージを確認
    $errors = $component->errors();

    expect($errors->get('question')[0])->toContain('必須項目');
    expect($errors->get('answer')[0])->toContain('必須項目');
    expect($errors->get('category_id')[0])->toContain('必須項目');
});

test('文字数制限のバリデーションメッセージが日本語で表示される', function () {
    // 文字数制限を超える文字列でバリデーションエラーを発生させる
    $longString = str_repeat('a', 1000);

    $component = Livewire::test('faqs.create')
        ->set('question', $longString)
        ->set('answer', 'テスト回答')
        ->set('category_id', $this->category->id)
        ->call('save');

    $component->assertHasErrors(['question']);

    $errors = $component->errors();
    expect($errors->get('question')[0])->toContain('文字以下');
});

test('数値のバリデーションメッセージが日本語で表示される', function () {
    // 無効な数値でバリデーションエラーを発生させる
    $component = Livewire::test('faqs.create')
        ->set('question', 'テスト質問')
        ->set('answer', 'テスト回答')
        ->set('category_id', 'invalid-number') // 無効な数値
        ->call('save');

    $component->assertHasErrors(['category_id']);

    $errors = $component->errors();
    expect($errors->get('category_id')[0])->toContain('正しくありません');
});

test('優先度の範囲外の値でバリデーションメッセージが日本語で表示される', function () {
    // 範囲外の値でバリデーションエラーを発生させる
    $component = Livewire::test('faqs.create')
        ->set('question', 'テスト質問')
        ->set('answer', 'テスト回答')
        ->set('category_id', $this->category->id)
        ->set('priority', 5) // 範囲外の値（1-3の範囲外）
        ->call('save');

    $component->assertHasErrors(['priority']);

    $errors = $component->errors();
    expect($errors->get('priority')[0])->toContain('間で入力してください');
});

test('認証フォームのバリデーションメッセージが日本語で表示される', function () {
    // ユーザー登録フォームでバリデーションエラーを発生させる
    $component = Livewire::test('auth.register')
        ->set('name', '') // 必須項目を空にする
        ->set('email', 'invalid-email') // 無効なメールアドレス
        ->set('password', '123') // 短すぎるパスワード
        ->set('password_confirmation', '456') // 不一致
        ->call('register');

    $component->assertHasErrors(['name', 'email', 'password']);

    $errors = $component->errors();

    expect($errors->get('name')[0])->toContain('必須項目');
    expect($errors->get('email')[0])->toContain('メールアドレス');
    expect($errors->get('password')[0])->toContain('確認が一致しません');
});
