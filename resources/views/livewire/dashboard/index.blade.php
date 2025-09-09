<?php

use App\Models\FAQ;
use App\Models\Inquiry;
use App\Models\Category;
use function Livewire\Volt\{computed};

$stats = computed(function () {
    return [
        'total_faqs' => FAQ::count(),
        'active_faqs' => FAQ::where('is_active', true)->count(),
        'total_inquiries' => Inquiry::count(),
        'pending_inquiries' => Inquiry::where('status', 'pending')->count(),
        'in_progress_inquiries' => Inquiry::where('status', 'in_progress')->count(),
        'completed_inquiries' => Inquiry::where('status', 'completed')->count(),
        'categories' => Category::count(),
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

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">ダッシュボード</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">ヘルプデスクシステムの概要</p>
    </div>

    <!-- 統計カード -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- FAQ統計 -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">FAQ総数</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->stats['total_faqs']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">公開中:
                        {{ number_format($this->stats['active_faqs']) }}</p>
                </div>
            </div>
        </div>

        <!-- 問い合わせ統計 -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">問い合わせ総数</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->stats['total_inquiries']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">完了:
                        {{ number_format($this->stats['completed_inquiries']) }}</p>
                </div>
            </div>
        </div>

        <!-- 未対応問い合わせ -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">未対応</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->stats['pending_inquiries']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">対応中:
                        {{ number_format($this->stats['in_progress_inquiries']) }}</p>
                </div>
            </div>
        </div>

        <!-- カテゴリ数 -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">カテゴリ数</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->stats['categories']) }}</p>
                </div>
            </div>
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
                                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                @break

                                @case('closed')
                                    <div class="w-2 h-2 bg-gray-500 rounded-full mt-2"></div>
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

            <!-- 人気のFAQ -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">人気のFAQ</h2>
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
