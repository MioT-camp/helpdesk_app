<?php

use App\Models\FAQ;
use function Livewire\Volt\{state, mount};

state(['faq']);

mount(function ($faq_id) {
    $this->faq = FAQ::with([
        'category',
        'user',
        'tagRelations',
        'inquiries' => function ($query) {
            $query
                ->with(['category', 'assignedUser'])
                ->orderByPivot('created_at', 'desc')
                ->limit(10);
        },
    ])
        ->where('faq_id', $faq_id)
        ->firstOrFail();

    // 閲覧回数を増やす
    $this->faq->incrementViewCount();
});

?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- パンくずナビ -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('faqs.index') }}"
                    class="text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                    FAQ管理
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-gray-500 dark:text-gray-400">FAQ詳細</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <!-- ヘッダー -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            style="background-color: {{ $faq->category->color ?? '#6B7280' }}20; color: {{ $faq->category->color ?? '#6B7280' }}">
                            {{ $faq->category->name }}
                        </span>

                        <!-- 優先度 -->
                        @if ($faq->priority === 3)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                高優先度
                            </span>
                        @elseif($faq->priority === 2)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                中優先度
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                低優先度
                            </span>
                        @endif

                        <!-- 難易度 -->
                        @if ($faq->difficulty === 3)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                難しい
                            </span>
                        @elseif($faq->difficulty === 2)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                普通
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                簡単
                            </span>
                        @endif

                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            閲覧数: {{ number_format($faq->count) }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            紐付け件数: {{ number_format($faq->linked_inquiries_count) }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- 戻るボタン -->
                    <a href="{{ route('faqs.index') }}"
                        class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-3 py-2 rounded-md text-sm font-medium transition-colors flex items-center gap-1"
                        wire:navigate title="FAQ管理に戻る">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        戻る
                    </a>

                    <!-- コピーボタン群 -->
                    <div class="flex items-center gap-1 mr-2">
                        <!-- FAQ URLコピー -->
                        <button x-data="{ copied: false }"
                            @click="
                                navigator.clipboard.writeText('{{ request()->url() }}').then(() => {
                                    copied = true;
                                    setTimeout(() => copied = false, 2000);
                                })
                            "
                            class="bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 px-3 py-2 rounded-md text-sm font-medium transition-colors flex items-center gap-1"
                            title="FAQ URLをコピー">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                </path>
                            </svg>
                            <span x-show="!copied">URL</span>
                            <span x-show="copied" x-cloak class="text-green-600 dark:text-green-400">コピー完了!</span>
                        </button>

                        <!-- FAQ IDコピー -->
                        <button x-data="{ copied: false }"
                            @click="
                                navigator.clipboard.writeText('{{ $faq->faq_id }}').then(() => {
                                    copied = true;
                                    setTimeout(() => copied = false, 2000);
                                })
                            "
                            class="bg-green-100 hover:bg-green-200 dark:bg-green-900 dark:hover:bg-green-800 text-green-700 dark:text-green-300 px-3 py-2 rounded-md text-sm font-medium transition-colors flex items-center gap-1"
                            title="FAQ IDをコピー">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                </path>
                            </svg>
                            <span x-show="!copied">ID</span>
                            <span x-show="copied" x-cloak class="text-green-600 dark:text-green-400">コピー完了!</span>
                        </button>
                    </div>

                    <!-- このFAQに紐づけて問い合わせを新規登録 -->
                    <a href="{{ route('inquiries.create', ['faq_id' => $faq->faq_id]) }}"
                        class="bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center gap-1"
                        title="このFAQに紐づけて問い合わせを新規登録">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        問い合わせ登録
                    </a>

                    <a href="{{ route('faqs.edit', $faq->faq_id) }}"
                        class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        編集
                    </a>
                </div>
            </div>
        </div>

        <!-- コンテンツ -->
        <div class="px-6 py-6">
            <!-- 質問 -->
            <div class="mb-8">
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">質問</h2>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $faq->question }}</h1>
            </div>

            <!-- 回答 -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">回答</h2>
                    <x-copy-button :text="$faq->answer" label="回答をコピー" variant="primary" size="xs"
                        title="FAQ回答をクリップボードにコピー" />
                </div>
                <div class="prose dark:prose-invert max-w-none">
                    {!! nl2br(e($faq->answer)) !!}
                </div>
            </div>

            <!-- タグ -->
            @if ($faq->tags || $faq->tagRelations->isNotEmpty())
                <div class="mb-8">
                    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">タグ</h2>
                    <div class="flex flex-wrap gap-2">
                        @if ($faq->tags)
                            @foreach ($faq->tags_array as $tag)
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    #{{ $tag }}
                                </span>
                            @endforeach
                        @endif

                        @foreach ($faq->tagRelations as $tag)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm"
                                style="background-color: {{ $tag->color ?? '#6B7280' }}20; color: {{ $tag->color ?? '#6B7280' }}">
                                #{{ $tag->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- メタ情報 -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">作成者</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white">{{ $faq->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">作成日時</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white">{{ $faq->created_at->format('Y年m月d日 H:i') }}
                        </dd>
                    </div>
                    @if ($faq->updated_at->ne($faq->created_at))
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-gray-400">最終更新</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">
                                {{ $faq->updated_at->format('Y年m月d日 H:i') }}
                            </dd>
                        </div>
                    @endif
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">FAQ ID</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white flex items-center gap-2">
                            <span>#{{ $faq->faq_id }}</span>
                            <button x-data="{ copied: false }"
                                @click="
                                    navigator.clipboard.writeText('{{ $faq->faq_id }}').then(() => {
                                        copied = true;
                                        setTimeout(() => copied = false, 2000);
                                    })
                                "
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                title="IDをコピー">
                                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <svg x-show="copied" x-cloak class="w-4 h-4 text-green-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- 紐づけられた問い合わせ -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">紐づけられた問い合わせ</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ number_format($faq->linked_inquiries_count) }}件
                </span>
            </div>
        </div>
        <div class="p-6">
            @forelse($faq->inquiries as $inquiry)
                <div
                    class="flex items-start space-x-4 {{ !$loop->last ? 'mb-6 pb-6 border-b border-gray-200 dark:border-gray-700' : '' }}">
                    <!-- ステータスインジケーター -->
                    <div class="flex-shrink-0 mt-1">
                        @switch($inquiry->status)
                            @case('pending')
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            @break

                            @case('in_progress')
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            @break

                            @case('completed')
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            @break

                            @case('closed')
                                <div class="w-3 h-3 bg-gray-500 rounded-full"></div>
                            @break
                        @endswitch
                    </div>

                    <div class="flex-1 min-w-0">
                        <!-- 件名とリンク -->
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                            <a href="{{ route('inquiries.show', $inquiry->inquiry_id) }}"
                                class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                {{ $inquiry->subject }}
                            </a>
                        </h3>

                        <!-- メタ情報 -->
                        <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                            <span>ID: #{{ $inquiry->inquiry_id }}</span>

                            @if ($inquiry->category)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs"
                                    style="background-color: {{ $inquiry->category->color ?? '#6B7280' }}20; color: {{ $inquiry->category->color ?? '#6B7280' }}">
                                    {{ $inquiry->category->name }}
                                </span>
                            @endif

                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs
                                @switch($inquiry->status)
                                    @case('pending') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @break
                                    @case('in_progress') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @break
                                    @case('completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @break
                                    @case('closed') bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @break
                                @endswitch">
                                {{ $inquiry->status_label }}
                            </span>

                            <span>{{ $inquiry->received_at->format('Y/m/d H:i') }}</span>

                            @if ($inquiry->assignedUser)
                                <span>担当: {{ $inquiry->assignedUser->name }}</span>
                            @endif

                            <span>紐付け: {{ $inquiry->pivot->created_at->format('Y/m/d H:i') }}</span>
                        </div>

                        <!-- 送信者情報 -->
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            送信者: {{ $inquiry->sender_email }}
                            @if ($inquiry->customer_id)
                                ({{ $inquiry->customer_id }})
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 48 48">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 14v20c0 4.418 7.163 8 16 8 1.381 0 2.721-.087 4-.252M8 14c0 4.418 7.163 8 16 8s16-3.582 16-8M8 14c0-4.418 7.163-8 16-8s16 3.582 16 8m0 0v14m-16-4h.01M32 18h.01" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">紐づけられた問い合わせがありません</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            このFAQはまだ問い合わせと紐づけられていません。
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
