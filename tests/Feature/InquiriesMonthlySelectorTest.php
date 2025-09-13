<?php

use App\Models\User;
use App\Models\Inquiry;
use App\Models\Category;
use Livewire\Volt\Volt;

test('monthly inquiries page shows current month by default', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('inquiries.monthly'));
    $response->assertStatus(200);
    $response->assertSee('月別問い合わせ一覧');
    $response->assertSee(now()->year . '年' . now()->month . '月に受信した問い合わせ一覧');
});

test('monthly inquiries page has year and month selectors', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('inquiries.monthly'));
    $response->assertStatus(200);
    $response->assertSee('年');
    $response->assertSee('月');

    // 年選択肢の確認（過去5年分）
    for ($year = now()->year; $year >= now()->year - 5; $year--) {
        $response->assertSee($year . '年');
    }

    // 月選択肢の確認（1-12月）
    for ($month = 1; $month <= 12; $month++) {
        $response->assertSee($month . '月');
    }
});

test('monthly inquiries page shows different month data when month is selected', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 今月の問い合わせを作成
    $thisMonthInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    // 先月の問い合わせを作成
    $lastMonthInquiry = Inquiry::factory()->create([
        'received_at' => now()->subMonth(),
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    // 先月を選択
    $response = Volt::test('inquiries.monthly')
        ->set('selectedYear', now()->subMonth()->year)
        ->set('selectedMonth', now()->subMonth()->month);

    $response->assertSee($lastMonthInquiry->subject);
    $response->assertDontSee($thisMonthInquiry->subject);
});

test('monthly inquiries page shows correct statistics for selected month', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 今月の問い合わせを作成
    $thisMonthInquiry = Inquiry::factory()->create([
        'received_at' => now(),
        'status' => 'pending',
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    // 先月の問い合わせを作成
    $lastMonthInquiry = Inquiry::factory()->create([
        'received_at' => now()->subMonth(),
        'status' => 'completed',
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    // 先月を選択
    $response = Volt::test('inquiries.monthly')
        ->set('selectedYear', now()->subMonth()->year)
        ->set('selectedMonth', now()->subMonth()->month);

    // 先月の統計が表示されることを確認
    $response->assertSee('1'); // 先月の総数
});

test('monthly inquiries CSV export uses selected month', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    // 先月の問い合わせを作成
    $lastMonthInquiry = Inquiry::factory()->create([
        'received_at' => now()->subMonth(),
        'category_id' => $category->id,
        'created_user_id' => $user->id,
    ]);

    // 先月を選択してCSV出力
    $response = Volt::test('inquiries.monthly')
        ->set('selectedYear', now()->subMonth()->year)
        ->set('selectedMonth', now()->subMonth()->month)
        ->call('exportCsv');

    $response->assertStatus(200);
});

test('monthly inquiries filter reset includes year and month', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = Volt::test('inquiries.monthly')
        ->set('selectedYear', now()->subYear()->year)
        ->set('selectedMonth', 6)
        ->set('selectedStatus', 'pending')
        ->set('selectedUser', '1')
        ->set('searchKeyword', 'test')
        ->call('resetFilters');

    $response->assertSet('selectedYear', now()->year);
    $response->assertSet('selectedMonth', now()->month);
    $response->assertSet('selectedStatus', '');
    $response->assertSet('selectedUser', '');
    $response->assertSet('searchKeyword', '');
});

test('monthly inquiries page shows empty state for month with no inquiries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // 来月を選択（データがない月）
    $nextMonth = now()->addMonth();

    $response = Volt::test('inquiries.monthly')
        ->set('selectedYear', $nextMonth->year)
        ->set('selectedMonth', $nextMonth->month);

    $response->assertSee('問い合わせはありません');
    $response->assertSee('0'); // 総数
});
