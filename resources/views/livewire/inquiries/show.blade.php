<?php

use App\Models\Inquiry;
use App\Models\User;
use function Livewire\Volt\{state, mount, computed, rules};

state(['inquiry', 'response' => '', 'status' => '', 'assigned_user_id' => '', 'priority' => 2]);

rules([
    'response' => 'required|string',
    'status' => 'required|in:pending,in_progress,completed,closed',
    'assigned_user_id' => 'nullable|exists:users,id',
    'priority' => 'required|integer|between:1,4',
]);

mount(function ($inquiry_id) {
    $this->inquiry = Inquiry::with(['category', 'assignedUser', 'createdUser'])
        ->where('inquiry_id', $inquiry_id)
        ->firstOrFail();

    $this->response = $this->inquiry->response ?? '';
    $this->status = $this->inquiry->status;
    $this->assigned_user_id = $this->inquiry->assigned_user_id;
    $this->priority = $this->inquiry->priority;
});

$users = computed(fn() => User::where('is_active', true)->get());

$updateStatus = function () {
    $this->inquiry->update([
        'status' => $this->status,
        'assigned_user_id' => $this->assigned_user_id,
        'priority' => $this->priority,
    ]);

    session()->flash('status', 'ステータスが更新されました。');
};

$saveResponse = function () {
    $this->validate(['response' => 'required|string']);

    $this->inquiry->update([
        'response' => $this->response,
        'status' => 'completed',
        'first_response_at' => $this->inquiry->first_response_at ?? now(),
        'completed_at' => now(),
    ]);

    session()->flash('status', '回答が保存されました。');
};

?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- パンくずナビ -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('inquiries.index') }}"
                    class="text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                    問い合わせ管理
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-gray-500 dark:text-gray-400">問い合わせ詳細</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- メインコンテンツ -->
        <div class="lg:col-span-2 space-y-6">
            <!-- 問い合わせ内容 -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $inquiry->subject }}</h1>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">問い合わせ ID:
                                #{{ $inquiry->inquiry_id }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @switch($inquiry->status)
                                @case('pending')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        未対応
                                    </span>
                                @break

                                @case('in_progress')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        対応中
                                    </span>
                                @break

                                @case('completed')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        完了
                                    </span>
                                @break

                                @case('closed')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        クローズ
                                    </span>
                                @break
                            @endswitch
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6">
                    <div class="prose dark:prose-invert max-w-none">
                        {!! nl2br(e($inquiry->content)) !!}
                    </div>
                </div>
            </div>

            <!-- 回答フォーム -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">回答</h2>
                </div>

                <form wire:submit="saveResponse" class="px-6 py-6">
                    <div class="mb-4">
                        <label for="response" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            回答内容 <span class="text-red-500">*</span>
                        </label>
                        <textarea id="response" wire:model="response" rows="8" placeholder="顧客への回答を入力してください..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required></textarea>
                        @error('response')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium transition-colors"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>回答を保存</span>
                        <span wire:loading>保存中...</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- サイドバー -->
        <div class="space-y-6">
            <!-- ステータス管理 -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">ステータス管理</h3>
                </div>

                <form wire:submit="updateStatus" class="px-6 py-6 space-y-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            ステータス
                        </label>
                        <select id="status" wire:model="status"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="pending">未対応</option>
                            <option value="in_progress">対応中</option>
                            <option value="completed">完了</option>
                            <option value="closed">クローズ</option>
                        </select>
                    </div>

                    <div>
                        <label for="assigned_user_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            担当者
                        </label>
                        <select id="assigned_user_id" wire:model="assigned_user_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">未割り当て</option>
                            @foreach ($this->users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            優先度
                        </label>
                        <select id="priority" wire:model="priority"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="1">低</option>
                            <option value="2">中</option>
                            <option value="3">高</option>
                            <option value="4">緊急</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        ステータスを更新
                    </button>
                </form>
            </div>

            <!-- 問い合わせ情報 -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">問い合わせ情報</h3>
                </div>

                <div class="px-6 py-6">
                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-gray-400">送信者</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ $inquiry->sender_email }}</dd>
                        </div>
                        @if ($inquiry->customer_id)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">顧客ID</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">{{ $inquiry->customer_id }}</dd>
                            </div>
                        @endif
                        @if ($inquiry->prefecture)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">都道府県</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">{{ $inquiry->prefecture }}</dd>
                            </div>
                        @endif
                        @if ($inquiry->user_attribute)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">ユーザー属性</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">{{ $inquiry->user_attribute }}</dd>
                            </div>
                        @endif
                        @if ($inquiry->category)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">カテゴリ</dt>
                                <dd class="mt-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        style="background-color: {{ $inquiry->category->color ?? '#6B7280' }}20; color: {{ $inquiry->category->color ?? '#6B7280' }}">
                                        {{ $inquiry->category->name }}
                                    </span>
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-gray-400">受信日時</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">
                                {{ $inquiry->received_at->format('Y年m月d日 H:i') }}</dd>
                        </div>
                        @if ($inquiry->response_deadline)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">回答期限</dt>
                                <dd
                                    class="mt-1 text-gray-900 dark:text-white {{ $inquiry->response_deadline->isPast() ? 'text-red-500' : '' }}">
                                    {{ $inquiry->response_deadline->format('Y年m月d日 H:i') }}
                                </dd>
                            </div>
                        @endif
                        @if ($inquiry->first_response_at)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">初回回答日時</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">
                                    {{ $inquiry->first_response_at->format('Y年m月d日 H:i') }}</dd>
                            </div>
                        @endif
                        @if ($inquiry->completed_at)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">完了日時</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">
                                    {{ $inquiry->completed_at->format('Y年m月d日 H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
