<?php

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\Response;
use function Livewire\Volt\{computed, state, mount};

// 選択された日付の問い合わせデータを取得
$selectedDateInquiries = computed(function () {
    $selectedDate = $this->selectedDate ?: now()->toDateString();

    return Inquiry::with(['category', 'assignedUser'])
        ->whereDate('received_at', $selectedDate)
        ->orderBy('received_at', 'desc')
        ->get();
});

// 選択された日付の統計情報
$selectedDateStats = computed(function () {
    $selectedDate = $this->selectedDate ?: now()->toDateString();

    return [
        'total' => Inquiry::whereDate('received_at', $selectedDate)->count(),
        'closed' => Inquiry::whereDate('received_at', $selectedDate)->where('status', 'closed')->count(),
        'completed' => Inquiry::whereDate('received_at', $selectedDate)->where('status', 'completed')->count(),
    ];
});

// 担当者一覧を取得
$users = computed(function () {
    return User::where('is_active', true)->orderBy('name')->get();
});

// フィルタ状態
state([
    'selectedDate' => '',
    'selectedStatus' => '',
    'selectedUser' => '',
    'searchKeyword' => '',
]);

// 初期化時に現在の日付を設定
mount(function () {
    $this->selectedDate = now()->toDateString();
});

// フィルタ適用後の問い合わせデータ
$filteredInquiries = computed(function () {
    $selectedDate = $this->selectedDate ?: now()->toDateString();

    $query = Inquiry::with(['category', 'assignedUser'])->whereDate('received_at', $selectedDate);

    // ステータスフィルタ
    if ($this->selectedStatus) {
        $query->where('status', $this->selectedStatus);
    }

    // 担当者フィルタ
    if ($this->selectedUser) {
        $query->where('assigned_user_id', $this->selectedUser);
    }

    // 検索キーワードフィルタ
    if ($this->searchKeyword) {
        $query->where(function ($q) {
            $q->where('subject', 'like', '%' . $this->searchKeyword . '%')
                ->orWhere('content', 'like', '%' . $this->searchKeyword . '%')
                ->orWhere('customer_id', 'like', '%' . $this->searchKeyword . '%');
        });
    }

    return $query->orderBy('received_at', 'desc')->get();
});

// CSV出力用のデータ取得
$csvInquiries = computed(function () {
    $selectedDate = $this->selectedDate ?: now()->toDateString();

    $query = Inquiry::with(['assignedUser'])->whereDate('received_at', $selectedDate);

    // フィルタを適用
    if ($this->selectedStatus) {
        $query->where('status', $this->selectedStatus);
    }

    if ($this->selectedUser) {
        $query->where('assigned_user_id', $this->selectedUser);
    }

    if ($this->searchKeyword) {
        $query->where(function ($q) {
            $q->where('subject', 'like', '%' . $this->searchKeyword . '%')
                ->orWhere('content', 'like', '%' . $this->searchKeyword . '%')
                ->orWhere('customer_id', 'like', '%' . $this->searchKeyword . '%');
        });
    }

    return $query->orderBy('received_at', 'desc')->get();
});

// CSV出力メソッド
$exportCsv = function () {
    $inquiries = $this->csvInquiries;

    if ($inquiries->isEmpty()) {
        $this->dispatch('show-message', [
            'type' => 'warning',
            'message' => '出力する問い合わせがありません。',
        ]);
        return;
    }

    $selectedDate = $this->selectedDate ?: now()->toDateString();
    $filename = 'inquiries_' . $selectedDate . '.csv';

    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ];

    $callback = function () use ($inquiries) {
        $file = fopen('php://output', 'w');

        // BOMを追加してExcelで文字化けを防ぐ
        fwrite($file, "\xEF\xBB\xBF");

        // ヘッダー行
        fputcsv($file, ['受信日時', '問い合わせID', '顧客ID', '都道府県', '属性', '件名', '回答内容', 'ステータス', '担当者']);

        // データ行
        foreach ($inquiries as $inquiry) {
            // ステータスを日本語に変換
            $statusLabels = [
                'pending' => '未対応',
                'in_progress' => '対応中',
                'completed' => '回答作成済',
                'closed' => '回答送付済',
                'no_response' => '回答不要',
            ];
            $statusLabel = $statusLabels[$inquiry->status] ?? $inquiry->status;

            fputcsv($file, [$inquiry->received_at->format('Y-m-d H:i:s'), $inquiry->inquiry_id, $inquiry->customer_id ?? '', $inquiry->prefecture ?? '', $inquiry->user_attribute ?? '', $inquiry->subject, $inquiry->response ?? '', $statusLabel, $inquiry->assignedUser->name ?? '']);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
};

// フィルタリセット
$resetFilters = function () {
    $this->selectedDate = now()->toDateString();
    $this->selectedStatus = '';
    $this->selectedUser = '';
    $this->searchKeyword = '';
};

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">日別問い合わせ一覧</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ \Carbon\Carbon::parse($this->selectedDate ?: now()->toDateString())->format('Y年n月j日') }}に受信した問い合わせ一覧
                </p>
            </div>
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                ダッシュボードに戻る
            </a>
        </div>
    </div>

    <!-- 統計情報 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">総数</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->selectedDateStats['total']) }}件
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
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">クローズ数</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->selectedDateStats['closed']) }}件
                    </p>
                </div>
            </div>
        </div>

        <!-- 回答作成済 -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">回答作成済</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->selectedDateStats['completed']) }}件
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- フィルタ -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">フィルタ</h2>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- 日付選択 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">日付</label>
                <input type="date" wire:model.live="selectedDate"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>

            <!-- ステータスフィルタ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ステータス</label>
                <select wire:model.live="selectedStatus"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">すべて</option>
                    <option value="pending">未対応</option>
                    <option value="in_progress">対応中</option>
                    <option value="completed">回答作成済</option>
                    <option value="closed">クローズ</option>
                    <option value="no_response">回答不要</option>
                </select>
            </div>

            <!-- 担当者フィルタ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">担当者</label>
                <select wire:model.live="selectedUser"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">すべて</option>
                    @foreach ($this->users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 検索キーワード -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">検索</label>
                <input type="text" wire:model.live.debounce.300ms="searchKeyword" placeholder="件名、内容、顧客IDで検索"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>

            <!-- リセットボタン -->
            <div class="flex items-end">
                <button wire:click="resetFilters"
                    class="w-full px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">
                    フィルタリセット
                </button>
            </div>
        </div>
    </div>

    <!-- 問い合わせ一覧 -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    問い合わせ一覧 ({{ $this->filteredInquiries->count() }}件)
                </h2>
                <div class="flex items-center space-x-3">
                    @if ($this->filteredInquiries->count() > 0)
                        <button wire:click="exportCsv"
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            CSV出力
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @if ($this->filteredInquiries->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                受信日時
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                顧客ID
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                都道府県
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                属性
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                件名
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                ステータス
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                担当者
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($this->filteredInquiries as $inquiry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $inquiry->received_at->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $inquiry->customer_id ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $inquiry->prefecture ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $inquiry->user_attribute ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('inquiries.show', $inquiry->inquiry_id) }}"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 truncate block max-w-xs">
                                        {{ $inquiry->subject }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @switch($inquiry->status)
                                        @case('pending')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                未対応
                                            </span>
                                        @break

                                        @case('in_progress')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                対応中
                                            </span>
                                        @break

                                        @case('completed')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                回答済
                                            </span>
                                        @break

                                        @case('closed')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                クローズ
                                            </span>
                                        @break

                                        @case('no_response')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                                無回答
                                            </span>
                                        @break
                                    @endswitch
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $inquiry->assignedUser->name ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                    @if ($this->selectedStatus || $this->selectedUser || $this->searchKeyword)
                        フィルタ条件に一致する問い合わせがありません
                    @else
                        選択された日付の問い合わせはありません
                    @endif
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if ($this->selectedStatus || $this->selectedUser || $this->searchKeyword)
                        フィルタをリセットして再度お試しください。
                    @else
                        選択された日付にはまだ問い合わせが受信されていません。
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
