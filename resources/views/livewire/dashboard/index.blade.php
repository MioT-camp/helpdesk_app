<?php

use App\Models\FAQ;
use App\Models\Inquiry;
use App\Models\Category;
use App\Models\Todo;
use function Livewire\Volt\{computed, state, mount};

$stats = computed(function () {
    $now = now();
    $startOfMonth = $now->copy()->startOfMonth();
    $endOfMonth = $now->copy()->endOfMonth();

    return [
        // 本日の問い合わせ総数
        'today_total_inquiries' => Inquiry::whereDate('received_at', $now->toDateString())->count(),
        // 本日のクローズ数
        'today_closed_inquiries' => Inquiry::where('status', 'closed')->whereDate('received_at', $now->toDateString())->count(),
        // 未対応（クローズ以外）
        'unclosed_inquiries' => Inquiry::whereNotIn('status', ['closed'])->count(),
        // 回答作成済（completed）
        'completed_inquiries' => Inquiry::where('status', 'completed')->count(),
        // 今月の問い合わせ総数（全ステータス）
        'monthly_total_inquiries' => Inquiry::whereBetween('received_at', [$startOfMonth, $endOfMonth])->count(),
        // 新しい問い合わせ（今日受信）
        'new_inquiries_today' => Inquiry::whereDate('received_at', $now->toDateString())->count(),
        // 緊急問い合わせ（優先度4）
        'urgent_inquiries' => Inquiry::where('priority', 4)
            ->whereNotIn('status', ['closed'])
            ->count(),
        // 期限切れ問い合わせ（回答期限が過ぎていて、クローズされていない）
        'overdue_inquiries' => Inquiry::where('response_deadline', '<', $now)
            ->whereNotIn('status', ['closed'])
            ->count(),
        // ToDo統計
        'total_todos' => Todo::where('user_id', auth()->id())->count(),
        'pending_todos' => Todo::where('user_id', auth()->id())
            ->where('is_completed', false)
            ->count(),
        'completed_todos' => Todo::where('user_id', auth()->id())
            ->where('is_completed', true)
            ->count(),
        'high_priority_todos' => Todo::where('user_id', auth()->id())
            ->where('is_completed', false)
            ->where('priority', 'high')
            ->count(),
        'overdue_todos' => Todo::where('user_id', auth()->id())
            ->where('is_completed', false)
            ->where('due_date', '<', $now->toDateString())
            ->count(),
    ];
});

$recentInquiries = computed(function () {
    return Inquiry::with(['category', 'assignedUser'])
        ->orderBy('received_at', 'desc')
        ->limit(5)
        ->get();
});

$popularFaqs = computed(function () {
    return FAQ::with(['category', 'user'])
        ->where('is_active', true)
        ->orderBy('count', 'desc')
        ->limit(5)
        ->get();
});

// ホットFAQは状態として管理し、毎回更新
state(['hotFaqs' => []]);

mount(function () {
    $this->loadHotFaqs();
});

$loadHotFaqs = function () {
    $this->hotFaqs = FAQ::with(['category', 'user'])
        ->where('is_active', true)
        ->hot()
        ->limit(5)
        ->get()
        ->map(function ($faq) {
            if (!isset($faq->hot_count)) {
                $faq->hot_count = 0;
            }
            return $faq;
        });
};

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">ダッシュボード</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">ヘルプデスクシステムの概要</p>
    </div>

    <!-- 統計カード -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- 本日の対応状況 -->
        <a href="{{ route('inquiries.today') }}"
            class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">本日の対応状況</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->stats['today_closed_inquiries']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">本日総数:
                        {{ number_format($this->stats['today_total_inquiries']) }}</p>
                </div>
                <div class="ml-auto">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </a>

        <!-- 今月の対応状況 -->
        <a href="{{ route('inquiries.monthly') }}"
            class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">今月の対応状況</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->stats['monthly_total_inquiries']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ now()->format('Y年n月') }}の総数</p>
                </div>
                <div class="ml-auto">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </a>

        <!-- ToDo状況 -->
        <a href="{{ route('todos.index') }}"
            class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ToDo状況</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->stats['pending_todos']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">完了済み:
                        {{ number_format($this->stats['completed_todos']) }}</p>
                    <p class="text-xs text-red-600 dark:text-red-400 font-medium">期限切れ:
                        {{ number_format($this->stats['overdue_todos']) }}</p>
                </div>
                <div class="ml-auto">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </a>
    </div>

    <!-- クイックアクション -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">クイックアクション</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 新しい問い合わせを登録 -->
            <a href="{{ route('inquiries.create') }}"
                class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors group">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100">新しい問い合わせ</p>
                    <p class="text-xs text-blue-600 dark:text-blue-300">問い合わせ登録</p>
                </div>
                <div class="ml-auto">
                    <svg class="w-4 h-4 text-blue-400 group-hover:text-blue-600 dark:group-hover:text-blue-300"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            <!-- 緊急対応が必要 -->
            <a href="{{ route('inquiries.index', ['filter_priority' => '4']) }}"
                class="flex items-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors group">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-900 dark:text-red-100">緊急対応</p>
                    <p class="text-xs text-red-600 dark:text-red-300">
                        {{ number_format($this->stats['urgent_inquiries']) }}件</p>
                </div>
                <div class="ml-auto">
                    <svg class="w-4 h-4 text-red-400 group-hover:text-red-600 dark:group-hover:text-red-300"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            <!-- 未対応一覧 -->
            <a href="{{ route('inquiries.index', ['filter_unclosed' => '1']) }}"
                class="flex items-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800 hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors group">
                <div class="flex-shrink-0">
                    <div
                        class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-yellow-900 dark:text-yellow-100">未対応一覧</p>
                    <p class="text-xs text-yellow-600 dark:text-yellow-300">
                        {{ number_format($this->stats['unclosed_inquiries']) }}件</p>
                </div>
                <div class="ml-auto">
                    <svg class="w-4 h-4 text-yellow-400 group-hover:text-yellow-600 dark:group-hover:text-yellow-300"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            <!-- FAQ追加 -->
            <a href="{{ route('faqs.create') }}"
                class="flex items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors group">
                <div class="flex-shrink-0">
                    <div
                        class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-900 dark:text-green-100">FAQ追加</p>
                    <p class="text-xs text-green-600 dark:text-green-300">新しいFAQ</p>
                </div>
                <div class="ml-auto">
                    <svg class="w-4 h-4 text-green-400 group-hover:text-green-600 dark:group-hover:text-green-300"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- 最近の問い合わせ -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">最近の問い合わせ</h2>
                    <a href="{{ route('inquiries.index') }}"
                        class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        すべて表示
                    </a>
                </div>
            </div>
            <div class="p-6">
                @forelse($this->recentInquiries as $inquiry)
                    <div
                        class="flex items-start space-x-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-gray-200 dark:border-gray-700' : '' }}">
                        <div class="flex-shrink-0">
                            @switch($inquiry->status)
                                @case('pending')
                                    <div class="w-2 h-2 bg-red-500 rounded-full mt-2"></div>
                                @break

                                @case('in_progress')
                                    <div class="w-2 h-2 bg-yellow-500 rounded-full mt-2"></div>
                                @break

                                @case('completed')
                                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                @break

                                @case('closed')
                                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                @break
                            @endswitch
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                <a href="{{ route('inquiries.show', $inquiry->inquiry_id) }}"
                                    class="hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ $inquiry->subject }}
                                </a>
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $inquiry->sender_email }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $inquiry->received_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">最近の問い合わせはありません</p>
                    @endforelse
                </div>
            </div>

            <!-- ホットなFAQ -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-8">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                            </svg>
                            ホットなFAQ
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-normal ml-2">（直近1ヶ月の紐付け件数）</span>
                        </h2>
                        <div class="flex space-x-2">
                            <button wire:click="loadHotFaqs"
                                class="text-sm text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
                                更新
                            </button>
                            <a href="{{ route('faqs.index') }}"
                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                すべて表示
                            </a>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    @forelse($hotFaqs as $faq)
                        <div
                            class="flex items-start space-x-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-gray-200 dark:border-gray-700' : '' }}">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                    <span
                                        class="text-xs font-medium text-red-600 dark:text-red-400">{{ $loop->iteration }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    <a href="{{ route('faqs.show', $faq->faq_id) }}"
                                        class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $faq->question }}
                                    </a>
                                </p>
                                <div class="flex items-center mt-1">
                                    @if ($faq->category)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs"
                                            style="background-color: {{ $faq->category->color ?? '#6B7280' }}20; color: {{ $faq->category->color ?? '#6B7280' }}">
                                            {{ $faq->category->name }}
                                        </span>
                                    @endif
                                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                        {{ number_format($faq->hot_count ?? 0) }} 件の紐付け
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">ホットなFAQがありません</p>
                    @endforelse
                </div>
            </div>

            <!-- 人気のFAQ -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            人気のFAQ
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-normal ml-2">（総閲覧数）</span>
                        </h2>
                        <a href="{{ route('faqs.index') }}"
                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            すべて表示
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @forelse($this->popularFaqs as $faq)
                        <div
                            class="flex items-start space-x-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-gray-200 dark:border-gray-700' : '' }}">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <span
                                        class="text-xs font-medium text-blue-600 dark:text-blue-400">{{ $loop->iteration }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    <a href="{{ route('faqs.show', $faq->faq_id) }}"
                                        class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $faq->question }}
                                    </a>
                                </p>
                                <div class="flex items-center mt-1">
                                    @if ($faq->category)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs"
                                            style="background-color: {{ $faq->category->color ?? '#6B7280' }}20; color: {{ $faq->category->color ?? '#6B7280' }}">
                                            {{ $faq->category->name }}
                                        </span>
                                    @endif
                                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                        {{ number_format($faq->count) }} 回閲覧
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">FAQがありません</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
