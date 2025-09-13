<?php

use App\Models\Inquiry;
use App\Models\Category;
use App\Models\User;
use function Livewire\Volt\{computed, state, mount};

// 期間選択の状態
state([
    'selectedYear' => '',
    'selectedMonth' => '',
    'reportType' => 'monthly', // monthly, yearly
]);

// 初期化時に現在の年月を設定
mount(function () {
    $this->selectedYear = now()->year;
    $this->selectedMonth = now()->month;
});

// カテゴリ別統計
$categoryStats = computed(function () {
    $year = (int) $this->selectedYear;
    $month = (int) $this->selectedMonth;

    if ($this->reportType === 'yearly') {
        $startDate = now()->setYear($year)->startOfYear();
        $endDate = now()->setYear($year)->endOfYear();
    } else {
        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();
    }

    return Inquiry::with('category')
        ->whereBetween('received_at', [$startDate, $endDate])
        ->selectRaw('category_id, COUNT(*) as count')
        ->groupBy('category_id')
        ->get()
        ->map(function ($item) {
            return [
                'category_name' => $item->category->name ?? '未分類',
                'count' => $item->count,
            ];
        })
        ->sortByDesc('count');
});

// 担当者別統計
$userStats = computed(function () {
    $year = (int) $this->selectedYear;
    $month = (int) $this->selectedMonth;

    if ($this->reportType === 'yearly') {
        $startDate = now()->setYear($year)->startOfYear();
        $endDate = now()->setYear($year)->endOfYear();
    } else {
        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();
    }

    return Inquiry::with('assignedUser')
        ->whereBetween('received_at', [$startDate, $endDate])
        ->whereNotNull('assigned_user_id')
        ->selectRaw(
            'assigned_user_id, COUNT(*) as total, 
                    SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = "closed" THEN 1 ELSE 0 END) as closed',
        )
        ->groupBy('assigned_user_id')
        ->get()
        ->map(function ($item) {
            $completionRate = $item->total > 0 ? ($item->completed / $item->total) * 100 : 0;
            return [
                'user_name' => $item->assignedUser->name ?? '未割り当て',
                'total' => $item->total,
                'completed' => $item->completed,
                'closed' => $item->closed,
                'completion_rate' => $completionRate,
            ];
        })
        ->sortByDesc('total');
});

// 時間帯別統計
$hourlyStats = computed(function () {
    $year = (int) $this->selectedYear;
    $month = (int) $this->selectedMonth;

    if ($this->reportType === 'yearly') {
        $startDate = now()->setYear($year)->startOfYear();
        $endDate = now()->setYear($year)->endOfYear();
    } else {
        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();
    }

    return Inquiry::whereBetween('received_at', [$startDate, $endDate])
        ->selectRaw('HOUR(received_at) as hour, COUNT(*) as count')
        ->groupBy('hour')
        ->orderBy('hour')
        ->get()
        ->pluck('count', 'hour')
        ->toArray();
});

// ステータス別統計
$statusStats = computed(function () {
    $year = (int) $this->selectedYear;
    $month = (int) $this->selectedMonth;

    if ($this->reportType === 'yearly') {
        $startDate = now()->setYear($year)->startOfYear();
        $endDate = now()->setYear($year)->endOfYear();
    } else {
        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();
    }

    $stats = Inquiry::whereBetween('received_at', [$startDate, $endDate])
        ->selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->get()
        ->pluck('count', 'status')
        ->toArray();

    $statusLabels = [
        'pending' => '未対応',
        'in_progress' => '対応中',
        'completed' => '回答作成済',
        'closed' => 'クローズ',
        'no_response' => '回答不要',
    ];

    return collect($statusLabels)->map(function ($label, $status) use ($stats) {
        return [
            'label' => $label,
            'count' => $stats[$status] ?? 0,
        ];
    });
});

// 都道府県別統計
$prefectureStats = computed(function () {
    $year = (int) $this->selectedYear;
    $month = (int) $this->selectedMonth;

    if ($this->reportType === 'yearly') {
        $startDate = now()->setYear($year)->startOfYear();
        $endDate = now()->setYear($year)->endOfYear();
    } else {
        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();
    }

    return Inquiry::whereBetween('received_at', [$startDate, $endDate])
        ->whereNotNull('prefecture')
        ->selectRaw('prefecture, COUNT(*) as count')
        ->groupBy('prefecture')
        ->orderByDesc('count')
        ->limit(10)
        ->get();
});

// 月別推移（年次レポート用）
$monthlyTrends = computed(function () {
    if ($this->reportType !== 'yearly') {
        return collect();
    }

    $year = (int) $this->selectedYear;

    return collect(range(1, 12))->map(function ($month) use ($year) {
        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();

        $total = Inquiry::whereBetween('received_at', [$startDate, $endDate])->count();
        $completed = Inquiry::whereBetween('received_at', [$startDate, $endDate])
            ->whereIn('status', ['completed', 'closed'])
            ->count();

        return [
            'month' => $month,
            'month_name' => $month . '月',
            'total' => $total,
            'completed' => $completed,
            'completion_rate' => $total > 0 ? ($completed / $total) * 100 : 0,
        ];
    });
});

// 総合統計
$overallStats = computed(function () {
    $year = (int) $this->selectedYear;
    $month = (int) $this->selectedMonth;

    if ($this->reportType === 'yearly') {
        $startDate = now()->setYear($year)->startOfYear();
        $endDate = now()->setYear($year)->endOfYear();
    } else {
        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();
    }

    $total = Inquiry::whereBetween('received_at', [$startDate, $endDate])->count();
    $completed = Inquiry::whereBetween('received_at', [$startDate, $endDate])
        ->whereIn('status', ['completed', 'closed'])
        ->count();
    $avgResponseTime = Inquiry::whereBetween('received_at', [$startDate, $endDate])
        ->whereNotNull('first_response_at')
        ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, received_at, first_response_at)) as avg_hours')
        ->value('avg_hours');

    return [
        'total' => $total,
        'completed' => $completed,
        'completion_rate' => $total > 0 ? ($completed / $total) * 100 : 0,
        'avg_response_time' => $avgResponseTime ? round($avgResponseTime, 1) : 0,
    ];
});

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">問い合わせ傾向レポート</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    問い合わせの詳細な分析と傾向を確認できます
                </p>
            </div>
            <a href="{{ route('reports.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                レポート一覧に戻る
            </a>
        </div>
    </div>

    <!-- 期間選択 -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">期間選択</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">レポート種別</label>
                <select wire:model.live="reportType"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="monthly">月次レポート</option>
                    <option value="yearly">年次レポート</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">年</label>
                <select wire:model.live="selectedYear"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    @for ($year = now()->year; $year >= now()->year - 5; $year--)
                        <option value="{{ $year }}">{{ $year }}年</option>
                    @endfor
                </select>
            </div>
            @if ($this->reportType === 'monthly')
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">月</label>
                    <select wire:model.live="selectedMonth"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        @for ($month = 1; $month <= 12; $month++)
                            <option value="{{ $month }}">{{ $month }}月</option>
                        @endfor
                    </select>
                </div>
            @endif
        </div>
    </div>

    <!-- 総合統計 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">総問い合わせ数</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($this->overallStats['total']) }}
                </p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">完了数</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                    {{ number_format($this->overallStats['completed']) }}
                </p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">完了率</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                    {{ number_format($this->overallStats['completion_rate'], 1) }}%
                </p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">平均回答時間</p>
                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                    {{ $this->overallStats['avg_response_time'] }}時間
                </p>
            </div>
        </div>
    </div>

    <!-- カテゴリ別統計 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">カテゴリ別問い合わせ数</h3>
            <div class="space-y-3">
                @foreach ($this->categoryStats as $stat)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-900 dark:text-white">{{ $stat['category_name'] }}</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full"
                                    style="width: {{ $this->overallStats['total'] > 0 ? ($stat['count'] / $this->overallStats['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                            <span
                                class="text-sm font-medium text-gray-900 dark:text-white w-8">{{ $stat['count'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ステータス別統計 -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">ステータス別分布</h3>
            <div class="space-y-3">
                @foreach ($this->statusStats as $stat)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-900 dark:text-white">{{ $stat['label'] }}</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full"
                                    style="width: {{ $this->overallStats['total'] > 0 ? ($stat['count'] / $this->overallStats['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                            <span
                                class="text-sm font-medium text-gray-900 dark:text-white w-8">{{ $stat['count'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 担当者別パフォーマンス -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">担当者別パフォーマンス</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            担当者</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            総数</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            完了</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            クローズ</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            完了率</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($this->userStats as $stat)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $stat['user_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $stat['total'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $stat['completed'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $stat['closed'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $stat['completion_rate'] >= 80
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : ($stat['completion_rate'] >= 60
                                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                    {{ number_format($stat['completion_rate'], 1) }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 時間帯別統計 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">時間帯別問い合わせ数</h3>
            <div class="space-y-2">
                @for ($hour = 0; $hour < 24; $hour++)
                    @php
                        $count = $this->hourlyStats[$hour] ?? 0;
                        $maxCount = max($this->hourlyStats);
                        $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                    @endphp
                    <div class="flex items-center space-x-3">
                        <span
                            class="text-sm text-gray-600 dark:text-gray-400 w-8">{{ sprintf('%02d', $hour) }}:00</span>
                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white w-8">{{ $count }}</span>
                    </div>
                @endfor
            </div>
        </div>

        <!-- 都道府県別統計 -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">都道府県別問い合わせ数（上位10位）</h3>
            <div class="space-y-3">
                @foreach ($this->prefectureStats as $stat)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-900 dark:text-white">{{ $stat->prefecture }}</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-purple-600 h-2 rounded-full"
                                    style="width: {{ $this->overallStats['total'] > 0 ? ($stat->count / $this->overallStats['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                            <span
                                class="text-sm font-medium text-gray-900 dark:text-white w-8">{{ $stat->count }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 月別推移（年次レポートのみ） -->
    @if ($this->reportType === 'yearly')
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">月別推移</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                月</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                総数</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                完了数</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                完了率</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($this->monthlyTrends as $trend)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $trend['month_name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $trend['total'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $trend['completed'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $trend['completion_rate'] >= 80
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : ($trend['completion_rate'] >= 60
                                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                        {{ number_format($trend['completion_rate'], 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
