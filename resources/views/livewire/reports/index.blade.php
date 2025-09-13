<?php

use function Livewire\Volt\{computed};

// レポート用の統計データを取得
$reportStats = computed(function () {
    $today = now()->toDateString();
    $thisMonth = now()->startOfMonth();
    $lastMonth = now()->subMonth()->startOfMonth();

    return [
        'today' => [
            'total' => \App\Models\Inquiry::whereDate('received_at', $today)->count(),
            'completed' => \App\Models\Inquiry::whereDate('received_at', $today)->where('status', 'completed')->count(),
        ],
        'thisMonth' => [
            'total' => \App\Models\Inquiry::whereBetween('received_at', [$thisMonth, now()->endOfMonth()])->count(),
            'completed' => \App\Models\Inquiry::whereBetween('received_at', [$thisMonth, now()->endOfMonth()])
                ->where('status', 'completed')
                ->count(),
        ],
        'lastMonth' => [
            'total' => \App\Models\Inquiry::whereBetween('received_at', [$lastMonth, $lastMonth->copy()->endOfMonth()])->count(),
            'completed' => \App\Models\Inquiry::whereBetween('received_at', [$lastMonth, $lastMonth->copy()->endOfMonth()])
                ->where('status', 'completed')
                ->count(),
        ],
    ];
});

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">レポート</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
            問い合わせの統計情報とレポートを確認できます
        </p>
    </div>

    <!-- 統計サマリー -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">本日の問い合わせ</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->reportStats['today']['total']) }}件
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        完了: {{ number_format($this->reportStats['today']['completed']) }}件
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">今月の問い合わせ</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->reportStats['thisMonth']['total']) }}件
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        完了: {{ number_format($this->reportStats['thisMonth']['completed']) }}件
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">前月比</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        @php
                            $thisMonthTotal = $this->reportStats['thisMonth']['total'];
                            $lastMonthTotal = $this->reportStats['lastMonth']['total'];
                            $change =
                                $lastMonthTotal > 0 ? (($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100 : 0;
                        @endphp
                        {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 1) }}%
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        前月: {{ number_format($lastMonthTotal) }}件
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- レポートカード -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- 日別問い合わせ一覧 -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">日別</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">日別問い合わせ一覧</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                指定した日付の問い合わせを確認できます。日付フィルタ機能付きで詳細な分析が可能です。
            </p>
            <a href="{{ route('inquiries.today') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                日別一覧を見る
            </a>
        </div>

        <!-- 月別問い合わせ一覧 -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">月別</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">月別問い合わせ一覧</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                指定した月の問い合わせを確認できます。月単位での傾向分析とフィルタ機能を提供します。
            </p>
            <a href="{{ route('inquiries.monthly') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                月別一覧を見る
            </a>
        </div>

        <!-- 問い合わせ傾向レポート -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">分析</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">問い合わせ傾向レポート</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                問い合わせの傾向分析、カテゴリ別統計、担当者別パフォーマンスなどの詳細レポートを確認できます。
            </p>
            <a href="{{ route('reports.trends') }}"
                class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                傾向レポートを見る
            </a>
        </div>
    </div>

    <!-- 最近の活動 -->
    <div class="mt-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">最近の活動</h2>
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="p-2 rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-900 dark:text-white">本日の問い合わせ受信数:
                            {{ number_format($this->reportStats['today']['total']) }}件</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ now()->format('Y年n月j日 H:i') }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="p-2 rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-900 dark:text-white">今月の完了率:
                            @php
                                $completionRate =
                                    $this->reportStats['thisMonth']['total'] > 0
                                        ? ($this->reportStats['thisMonth']['completed'] /
                                                $this->reportStats['thisMonth']['total']) *
                                            100
                                        : 0;
                            @endphp
                            {{ number_format($completionRate, 1) }}%
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ now()->format('Y年n月') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
