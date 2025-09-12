<?php

use App\Models\Inquiry;
use App\Models\Category;
use function Livewire\Volt\{state, computed};

state([
    'search' => '',
    'status' => '',
    'category_id' => '',
    'priority' => '',
    'assigned_user_id' => '',
    'sort' => 'latest',
]);

$categories = computed(fn() => Category::active()->get());

$inquiries = computed(function () {
    $query = Inquiry::with(['category', 'assignedUser', 'createdUser'])->orderBy('received_at', 'desc');

    // 検索条件
    if ($this->search) {
        $query->search($this->search);
    }

    // ステータスフィルター
    if ($this->status) {
        $query->where('status', $this->status);
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
        $query->where('assigned_user_id', $this->assigned_user_id);
    }

    // ソート
    switch ($this->sort) {
        case 'priority':
            $query->orderBy('priority', 'desc')->orderBy('received_at', 'desc');
            break;
        case 'deadline':
            $query->orderByRaw('response_deadline IS NULL, response_deadline ASC');
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
    </div>

    <!-- 検索・フィルター -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-4">
            <!-- 検索 -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    キーワード検索
                </label>
                <input type="text" id="search" wire:model.live.debounce.300ms="search"
                    placeholder="件名、内容、顧客IDで検索..."
                    x-on:paste="$nextTick(() => $wire.set('search', $event.target.value))"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- ステータスフィルター -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    ステータス
                </label>
                <select id="status" wire:model.live="status"
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

            <!-- ソート -->
            <div>
                <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    並び順
                </label>
                <select id="sort" wire:model.live="sort"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="latest">受信日時順</option>
                    <option value="priority">優先度順</option>
                    <option value="deadline">期限順</option>
                </select>
            </div>
        </div>

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
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
