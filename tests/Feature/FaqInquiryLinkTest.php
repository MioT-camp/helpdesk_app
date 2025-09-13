<?php

declare(strict_types=1);

use App\Models\FAQ;
use App\Models\Category;
use App\Models\User;
use App\Models\Inquiry;

beforeEach(function () {
    $this->user = User::factory()->create([
        'role' => 'staff',
        'is_active' => true,
    ]);

    $this->category = Category::factory()->create([
        'name' => 'テストカテゴリ',
        'slug' => 'test-category',
        'color' => '#3B82F6',
        'is_active' => true,
    ]);

    $this->faq = FAQ::factory()->create([
        'category_id' => $this->category->id,
        'question' => 'テスト質問',
        'answer' => 'テスト回答',
        'user_id' => $this->user->id,
        'priority' => 2,
        'difficulty' => 1,
        'is_active' => true,
    ]);
});

test('FAQ詳細画面に問い合わせ登録ボタンが表示される', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('faqs.show', $this->faq->faq_id));

    $response->assertStatus(200);
    $response->assertSee('問い合わせ登録');
    $response->assertSee(route('inquiries.create', ['faq_id' => $this->faq->faq_id]));
});

test('FAQ ID付きで問い合わせ新規登録画面にアクセスできる', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('inquiries.create', ['faq_id' => $this->faq->faq_id]));

    $response->assertStatus(200);
    $response->assertSee('この問い合わせは以下のFAQに紐づけられます');
    $response->assertSee($this->faq->question);
    $response->assertSee("FAQ #{$this->faq->faq_id}");
});

test('FAQ ID付きで問い合わせ新規登録画面でFAQ情報が表示される', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('inquiries.create', ['faq_id' => $this->faq->faq_id]));

    $response->assertStatus(200);

    // FAQ情報が正しく表示されることを確認
    $response->assertSee('この問い合わせは以下のFAQに紐づけられます');
    $response->assertSee($this->faq->question);
    $response->assertSee("FAQ #{$this->faq->faq_id}");
    $response->assertSee($this->faq->category->name);

    // フォームの初期値が設定されていることを確認
    $response->assertSee('value="' . $this->faq->category_id . '"', false);
});

test('存在しないFAQ IDで問い合わせ新規登録画面にアクセスしてもエラーにならない', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('inquiries.create', ['faq_id' => 99999]));

    $response->assertStatus(200);
    $response->assertDontSee('この問い合わせは以下のFAQに紐づけられます');
});

test('FAQ詳細画面の問い合わせ登録ボタンが正しいリンクを持つ', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('faqs.show', $this->faq->faq_id));

    $response->assertStatus(200);
    $response->assertSee('href="' . route('inquiries.create', ['faq_id' => $this->faq->faq_id]) . '"', false);
});
