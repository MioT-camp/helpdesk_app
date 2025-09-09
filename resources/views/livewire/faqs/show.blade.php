<?php

use App\Models\FAQ;
use function Livewire\Volt\{state, mount};

state(['faq']);

mount(function ($faq_id) {
    $this->faq = FAQ::with(['category', 'user', 'tagRelations'])
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
                    </div>
                </div>

                <div class="flex items-center gap-2">
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
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">回答</h2>
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
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ $faq->updated_at->format('Y年m月d日 H:i') }}
                            </dd>
                        </div>
                    @endif
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">FAQ ID</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white">#{{ $faq->faq_id }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- 関連FAQ（今後実装予定） -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">関連するFAQ</h2>
        <p class="text-gray-500 dark:text-gray-400">関連するFAQの表示機能は今後実装予定です。</p>
    </div>
</div>
