<?php

use App\Models\FAQ;
use App\Models\Category;
use function Livewire\Volt\{state, with, computed};

state([
    'search' => '',
    'category_id' => '',
    'priority' => '',
    'sort' => 'latest',
]);

$categories = computed(fn() => Category::active()->get());

$faqStats = computed(function () {
    return [
        'total_faqs' => FAQ::count(),
        'active_faqs' => FAQ::where('is_active', true)->count(),
        'inactive_faqs' => FAQ::where('is_active', false)->count(),
    ];
});

$faqs = computed(function () {
    $query = FAQ::with(['category', 'user', 'updatedBy'])->active();

    // 検索条件
    if ($this->search) {
        $query->search($this->search);
    }

    // カテゴリフィルター
    if ($this->category_id) {
        $query->where('category_id', $this->category_id);
    }

    // 優先度フィルター
    if ($this->priority) {
        $query->byPriority($this->priority);
    }

    // ソート
    switch ($this->sort) {
        case 'popular':
            $query->popular();
            break;
        case 'latest':
        default:
            $query->latest();
            break;
    }

    return $query->paginate(10);
});

$resetFilters = function () {
    $this->search = '';
    $this->category_id = '';
    $this->priority = '';
    $this->sort = 'latest';
};

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">FAQ管理</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">よくある質問の管理と検索</p>
    </div>

    <!-- FAQ統計 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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
                        {{ number_format($this->faqStats['total_faqs']) }}</p>
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
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">公開中</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->faqStats['active_faqs']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-gray-100 dark:bg-gray-900">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">非公開</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->faqStats['inactive_faqs']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 検索・フィルター -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- 検索 -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    キーワード検索
                </label>
                <input type="text" id="search" wire:model.live.debounce.300ms="search" placeholder="質問や回答を検索..."
                    x-on:paste="$nextTick(() => $wire.set('search', $event.target.value))"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
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
                </select>
            </div>

            <!-- ソート -->
            <div>
                <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    並び順
                </label>
                <select id="sort" wire:model.live="sort"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="latest">最新順</option>
                    <option value="popular">人気順</option>
                </select>
            </div>
        </div>

        <div class="flex justify-between items-center">
            <button wire:click="resetFilters"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                フィルターをリセット
            </button>
            <a href="{{ route('faqs.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                FAQ追加
            </a>
        </div>
    </div>

    <!-- FAQ一覧 -->
    <div class="space-y-4">
        @forelse($this->faqs as $faq)
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <!-- FAQ ID -->
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                ID: {{ $faq->faq_id }}
                            </span>

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
                            @endif

                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                閲覧数: {{ number_format($faq->count) }}
                            </span>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            <a href="{{ route('faqs.show', $faq->faq_id) }}"
                                class="hover:text-blue-600 dark:hover:text-blue-400">
                                {{ $faq->question }}
                            </a>
                        </h3>

                        <p class="text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                            {{ Str::limit($faq->answer, 150) }}
                        </p>

                        @if ($faq->tags)
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach ($faq->tags_array as $tag)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        #{{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <span>作成者: {{ $faq->user->name }}</span>
                            <span class="mx-2">•</span>
                            <span>作成: {{ $faq->created_at->format('Y/m/d H:i') }}</span>
                            @if ($faq->updated_at->ne($faq->created_at))
                                <span class="mx-2">•</span>
                                <span>更新: {{ $faq->updated_at->format('Y/m/d H:i') }}</span>
                                @if ($faq->updatedBy)
                                    <span class="mx-2">•</span>
                                    <span>更新者: {{ $faq->updatedBy->name }}</span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 ml-4">
                        <a href="{{ route('faqs.edit', $faq->faq_id) }}"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="編集">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">FAQが見つかりません</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">検索条件を変更するか、新しいFAQを作成してください。</p>
            </div>
        @endforelse
    </div>

    <!-- ページネーション -->
    @if ($this->faqs->hasPages())
        <div class="mt-8">
            {{ $this->faqs->links() }}
        </div>
    @endif
</div>
