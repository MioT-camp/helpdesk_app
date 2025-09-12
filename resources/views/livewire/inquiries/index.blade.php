<?php

use App\Models\Inquiry;
use App\Models\Category;
use App\Models\User;
use function Livewire\Volt\{state, computed, mount};

state([
    'search' => '',
    'status' => '',
    'category_id' => '',
    'priority' => '',
    'assigned_user_id' => '',
    'sort' => 'latest',
    'unclosed_only' => false,
    'overdue_only' => false,
    'date_from' => '',
    'date_to' => '',
    'date_period' => '', // 'this_month', 'last_month', 'this_year', 'custom'
]);

// ページ読み込み時にURLパラメータからフィルタ値を復元
mount(function () {
    // 直接フィルタ値を設定
    if (request()->has('filter_status')) {
        $this->status = request()->get('filter_status');
    }
    if (request()->has('filter_priority')) {
        $this->priority = request()->get('filter_priority');
    }
    if (request()->has('filter_unclosed')) {
        $this->unclosed_only = true;
    }
});

$categories = computed(fn() => Category::active()->get());

$users = computed(fn() => User::where('is_active', true)->orderBy('name')->get());

$overdueCount = computed(
    fn() => Inquiry::where('response_deadline', '<', now())
        ->whereNotIn('status', ['closed'])
        ->count(),
);

$inquiries = computed(function () {
    $query = Inquiry::with(['category', 'assignedUser', 'createdUser']);

    // 検索条件
    if ($this->search) {
        $query->search($this->search);
    }

    // ステータスフィルター
    if ($this->overdue_only) {
        // 期限切れフィルター - 最優先
        $query->where('response_deadline', '<', now())->whereNotIn('status', ['closed']);
    } elseif ($this->status) {
        $query->where('status', $this->status);
    } elseif ($this->unclosed_only) {
        // 未対応フィルター（クローズ以外）- ステータスフィルタが指定されていない場合のみ適用
        $query->whereNotIn('status', ['closed']);
    }

    // カテゴリフィルター
    if ($this->category_id) {
        $query->where('category_id', $this->category_id);
    }

    // 優先度フィルター
    if ($this->priority) {
        $query->where('priority', $this->priority);
    }

    // 担当者フィルター
    if ($this->assigned_user_id) {
        if ($this->assigned_user_id === 'unassigned') {
            $query->whereNull('assigned_user_id');
        } else {
            $query->where('assigned_user_id', $this->assigned_user_id);
        }
    }

    // 日付期間フィルター
    if ($this->date_period) {
        switch ($this->date_period) {
            case 'this_month':
                $query->whereBetween('received_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
            case 'last_month':
                $query->whereBetween('received_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                break;
            case 'this_year':
                $query->whereBetween('received_at', [now()->startOfYear(), now()->endOfYear()]);
                break;
            case 'custom':
                if ($this->date_from) {
                    $query->where('received_at', '>=', $this->date_from);
                }
                if ($this->date_to) {
                    $query->where('received_at', '<=', $this->date_to . ' 23:59:59');
                }
                break;
        }
    }

    // ソート
    switch ($this->sort) {
        case 'priority':
            $query->orderBy('priority', 'desc')->orderBy('received_at', 'desc');
            break;
        case 'priority_asc':
            $query->orderBy('priority', 'asc')->orderBy('received_at', 'desc');
            break;
        case 'deadline':
            $query->orderByRaw('response_deadline IS NULL, response_deadline ASC');
            break;
        case 'deadline_desc':
            $query->orderByRaw('response_deadline IS NULL DESC, response_deadline DESC');
            break;
        case 'status':
            $query
                ->orderByRaw(
                    "CASE 
                WHEN status = 'pending' THEN 1 
                WHEN status = 'in_progress' THEN 2 
                WHEN status = 'completed' THEN 3 
                WHEN status = 'closed' THEN 4 
                ELSE 5 
            END",
                )
                ->orderBy('received_at', 'desc');
            break;
        case 'assigned_user':
            $query->orderByRaw('assigned_user_id IS NULL, assigned_user_id ASC')->orderBy('received_at', 'desc');
            break;
        case 'category':
            $query->orderByRaw('category_id IS NULL, category_id ASC')->orderBy('received_at', 'desc');
            break;
        case 'oldest':
            $query->orderBy('received_at', 'asc');
            break;
        case 'latest':
        default:
            $query->orderBy('received_at', 'desc');
            break;
    }

    return $query->paginate(15);
});

$resetFilters = function () {
    $this->search = '';
    $this->status = '';
    $this->category_id = '';
    $this->priority = '';
    $this->assigned_user_id = '';
    $this->sort = 'latest';
    $this->unclosed_only = false;
    $this->overdue_only = false;
    $this->date_from = '';
    $this->date_to = '';
    $this->date_period = '';
};

// ステータスフィルタが変更された時にunclosed_onlyとoverdue_onlyをリセット
$onStatusChange = function () {
    if ($this->status) {
        $this->unclosed_only = false;
        $this->overdue_only = false;
    }
};

// 期限切れフィルタが変更された時に他のフィルタをリセット
$onOverdueChange = function () {
    if ($this->overdue_only) {
        $this->status = '';
        $this->unclosed_only = false;
    }
};

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">問い合わせ管理</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">顧客からの問い合わせの管理と対応</p>
            </div>
            <div class="flex gap-2">
                @if ($this->overdueCount > 0)
                    <button wire:click="$set('overdue_only', true)" wire:change="onOverdueChange"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 {{ $this->overdue_only ? 'ring-2 ring-red-300' : '' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        期限切れ ({{ $this->overdueCount }})
                    </button>
                @endif
                <a href="{{ route('inquiries.create') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    新規問い合わせ
                </a>
            </div>
        </div>

        <!-- フィルタ状態表示 -->
        @if (
            $this->unclosed_only ||
                $this->overdue_only ||
                $this->status ||
                $this->priority ||
                $this->category_id ||
                $this->assigned_user_id ||
                $this->date_period ||
                $this->search)
            <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span class="text-sm font-medium text-blue-900 dark:text-blue-100">フィルタ適用中:</span>
                        <div class="ml-2 flex flex-wrap gap-2">
                            @if ($this->unclosed_only)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    未対応のみ
                                </span>
                            @endif
                            @if ($this->overdue_only)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    期限切れのみ
                                </span>
                            @endif
                            @if ($this->status)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    ステータス:
                                    {{ match ($this->status) {
                                        'pending' => '未対応',
                                        'in_progress' => '対応中',
                                        'completed' => '回答作成済',
                                        'closed' => 'メール送信済',
                                        default => $this->status,
                                    } }}
                                </span>
                            @endif
                            @if ($this->priority)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    優先度:
                                    {{ match ($this->priority) {
                                        '1' => '低',
                                        '2' => '中',
                                        '3' => '高',
                                        '4' => '緊急',
                                        default => $this->priority,
                                    } }}
                                </span>
                            @endif
                            @if ($this->category_id)
                                @php $category = $this->categories->firstWhere('id', $this->category_id) @endphp
                                @if ($category)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        style="background-color: {{ $category->color ?? '#6B7280' }}20; color: {{ $category->color ?? '#6B7280' }}">
                                        カテゴリ: {{ $category->name }}
                                    </span>
                                @endif
                            @endif
                            @if ($this->assigned_user_id)
                                @php $assignedUser = $this->users->firstWhere('id', $this->assigned_user_id) @endphp
                                @if ($assignedUser)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                        担当者: {{ $assignedUser->name }}
                                    </span>
                                @endif
                            @endif
                            @if ($this->date_period)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                    期間:
                                    {{ match ($this->date_period) {
                                        'this_month' => '今月',
                                        'last_month' => '先月',
                                        'this_year' => '今年',
                                        'custom' => ($this->date_from ? $this->date_from : '開始日未設定') .
                                            ' ～ ' .
                                            ($this->date_to ? $this->date_to : '終了日未設定'),
                                        default => $this->date_period,
                                    } }}
                                </span>
                            @endif
                            @if ($this->search)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    検索: "{{ $this->search }}"
                                </span>
                            @endif
                        </div>
                    </div>
                    <button wire:click="resetFilters"
                        class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                        フィルタをクリア
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- 検索・フィルター -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-4">
            <!-- 検索 -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    キーワード検索
                </label>
                <input type="text" id="search" wire:model.live.debounce.300ms="search"
                    placeholder="件名、内容、顧客ID、担当者名で検索..."
                    x-on:paste="$nextTick(() => $wire.set('search', $event.target.value))"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- ステータスフィルター -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    ステータス
                </label>
                <select id="status" wire:model.live="status" wire:change="onStatusChange"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">すべてのステータス</option>
                    <option value="pending">未対応</option>
                    <option value="in_progress">対応中</option>
                    <option value="completed">回答作成済</option>
                    <option value="closed">メール送信済</option>
                </select>
            </div>

            <!-- カテゴリフィルター -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    カテゴリ
                </label>
                <select id="category" wire:model.live="category_id"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">すべてのカテゴリ</option>
                    @foreach ($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 優先度フィルター -->
            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    優先度
                </label>
                <select id="priority" wire:model.live="priority"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">すべての優先度</option>
                    <option value="1">低</option>
                    <option value="2">中</option>
                    <option value="3">高</option>
                    <option value="4">緊急</option>
                </select>
            </div>

            <!-- 担当者フィルター -->
            <div>
                <label for="assigned_user" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    担当者
                </label>
                <select id="assigned_user" wire:model.live="assigned_user_id"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">すべての担当者</option>
                    <option value="unassigned">未割り当て</option>
                    @foreach ($this->users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- ソート -->
            <div>
                <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    並び順
                </label>
                <select id="sort" wire:model.live="sort"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <optgroup label="受信日時">
                        <option value="latest">受信日時順（新しい順）</option>
                        <option value="oldest">受信日時順（古い順）</option>
                    </optgroup>
                    <optgroup label="優先度">
                        <option value="priority">優先度順（高い順）</option>
                        <option value="priority_asc">優先度順（低い順）</option>
                    </optgroup>
                    <optgroup label="期限">
                        <option value="deadline">期限順（近い順）</option>
                        <option value="deadline_desc">期限順（遠い順）</option>
                    </optgroup>
                    <optgroup label="その他">
                        <option value="status">ステータス順</option>
                        <option value="assigned_user">担当者順</option>
                        <option value="category">カテゴリ順</option>
                    </optgroup>
                </select>
            </div>

            <!-- 日付期間フィルター -->
            <div>
                <label for="date_period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    受信期間
                </label>
                <select id="date_period" wire:model.live="date_period"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">すべての期間</option>
                    <option value="this_month">今月</option>
                    <option value="last_month">先月</option>
                    <option value="this_year">今年</option>
                    <option value="custom">カスタム期間</option>
                </select>
            </div>
        </div>

        <!-- カスタム期間フィルター -->
        @if ($this->date_period === 'custom')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        開始日
                    </label>
                    <input type="date" id="date_from" wire:model.live="date_from"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        終了日
                    </label>
                    <input type="date" id="date_to" wire:model.live="date_to"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
        @endif

        <div class="flex justify-between items-center">
            <button wire:click="resetFilters"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                フィルターをリセット
            </button>
        </div>
    </div>

    <!-- 問い合わせ一覧 -->
    <div class="space-y-4">
        @forelse($this->inquiries as $inquiry)
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <!-- ステータス -->
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
                                        回答作成済
                                    </span>
                                @break

                                @case('closed')
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        メール送信済
                                    </span>
                                @break
                            @endswitch

                            @if ($inquiry->category)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    style="background-color: {{ $inquiry->category->color ?? '#6B7280' }}20; color: {{ $inquiry->category->color ?? '#6B7280' }}">
                                    {{ $inquiry->category->name }}
                                </span>
                            @endif

                            <!-- 優先度 -->
                            @if ($inquiry->priority === 4)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    緊急
                                </span>
                            @elseif($inquiry->priority === 3)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                    高
                                </span>
                            @endif

                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                ID: #{{ $inquiry->inquiry_id }}
                            </span>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            <a href="{{ route('inquiries.show', $inquiry->inquiry_id) }}"
                                class="hover:text-blue-600 dark:hover:text-blue-400">
                                {{ $inquiry->subject }}
                            </a>
                        </h3>

                        @if ($inquiry->summary)
                            <p class="text-gray-600 dark:text-gray-400 mb-3">
                                {{ Str::limit($inquiry->summary, 150) }}
                            </p>
                        @else
                            <p class="text-gray-600 dark:text-gray-400 mb-3">
                                {{ Str::limit($inquiry->content, 150) }}
                            </p>
                        @endif

                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 space-x-4">
                            <span>送信者: {{ $inquiry->sender_email }}</span>
                            @if ($inquiry->customer_id)
                                <span>顧客ID: {{ $inquiry->customer_id }}</span>
                            @endif
                            @if ($inquiry->assignedUser)
                                <span>担当者: {{ $inquiry->assignedUser->name }}</span>
                            @endif
                            <span>受信: {{ $inquiry->received_at->format('Y/m/d H:i') }}</span>
                            @if ($inquiry->response_deadline)
                                <span class="{{ $inquiry->response_deadline->isPast() ? 'text-red-500' : '' }}">
                                    期限: {{ $inquiry->response_deadline->format('Y/m/d H:i') }}
                                </span>
                            @endif
                            @if ($inquiry->status === 'closed' && $inquiry->email_sent_at)
                                <span class="text-green-600 dark:text-green-400">
                                    メール送信: {{ $inquiry->email_sent_at->format('Y/m/d H:i') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">問い合わせが見つかりません</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">検索条件を変更してください。</p>
                </div>
            @endforelse
        </div>

        <!-- ページネーション -->
        @if ($this->inquiries->hasPages())
            <div class="mt-8">
                {{ $this->inquiries->links() }}
            </div>
        @endif
    </div>
