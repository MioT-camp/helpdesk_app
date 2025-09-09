<?php

use App\Models\FAQ;
use App\Models\Category;
use function Livewire\Volt\{state, mount, computed, rules};

state(['faq', 'question' => '', 'answer' => '', 'category_id' => '', 'tags' => '', 'priority' => 1, 'difficulty' => 1, 'is_active' => true]);

rules([
    'question' => 'required|string|max:500',
    'answer' => 'required|string',
    'category_id' => 'required|exists:categories,id',
    'tags' => 'nullable|string|max:255',
    'priority' => 'required|integer|between:1,3',
    'difficulty' => 'required|integer|between:1,3',
    'is_active' => 'boolean',
]);

mount(function ($faq_id) {
    $this->faq = FAQ::with(['category', 'user'])
        ->where('faq_id', $faq_id)
        ->firstOrFail();

    // フォームにデータを設定
    $this->question = $this->faq->question;
    $this->answer = $this->faq->answer;
    $this->category_id = $this->faq->category_id;
    $this->tags = $this->faq->tags ?? '';
    $this->priority = $this->faq->priority;
    $this->difficulty = $this->faq->difficulty;
    $this->is_active = $this->faq->is_active;
});

$categories = computed(fn() => Category::active()->get());

$update = function () {
    $this->validate();

    $this->faq->update([
        'question' => $this->question,
        'answer' => $this->answer,
        'category_id' => $this->category_id,
        'tags' => $this->tags,
        'priority' => $this->priority,
        'difficulty' => $this->difficulty,
        'is_active' => $this->is_active,
        'search_keywords' => $this->question . ' ' . $this->answer . ' ' . $this->tags,
    ]);

    session()->flash('status', 'FAQが正常に更新されました。');
    return redirect()->route('faqs.show', $this->faq->faq_id);
};

$delete = function () {
    $this->faq->delete();
    session()->flash('status', 'FAQが削除されました。');
    return redirect()->route('faqs.index');
};

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
                    <a href="{{ route('faqs.show', $faq->faq_id) }}"
                        class="ml-1 text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                        FAQ詳細
                    </a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-gray-500 dark:text-gray-400">編集</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">FAQ編集</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">FAQ ID: #{{ $faq->faq_id }}</p>
    </div>

    <form wire:submit="update" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <!-- 基本情報 -->
            <div class="space-y-6">
                <!-- 質問 -->
                <div>
                    <label for="question" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        質問 <span class="text-red-500">*</span>
                    </label>
                    <textarea id="question" wire:model="question" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        required></textarea>
                    @error('question')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 回答 -->
                <div>
                    <label for="answer" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        回答 <span class="text-red-500">*</span>
                    </label>
                    <textarea id="answer" wire:model="answer" rows="8"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        required></textarea>
                    @error('answer')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- カテゴリ -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        カテゴリ <span class="text-red-500">*</span>
                    </label>
                    <select id="category_id" wire:model="category_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        required>
                        <option value="">カテゴリを選択してください</option>
                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- その他のフィールド -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            優先度 <span class="text-red-500">*</span>
                        </label>
                        <select id="priority" wire:model="priority"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="1">低</option>
                            <option value="2">中</option>
                            <option value="3">高</option>
                        </select>
                    </div>

                    <div>
                        <label for="difficulty" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            難易度 <span class="text-red-500">*</span>
                        </label>
                        <select id="difficulty" wire:model="difficulty"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="1">簡単</option>
                            <option value="2">普通</option>
                            <option value="3">難しい</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="is_active" wire:model="is_active"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                        FAQを公開する
                    </label>
                </div>
            </div>
        </div>

        <!-- アクションボタン -->
        <div class="flex justify-between">
            <div class="flex gap-2">
                <a href="{{ route('faqs.show', $faq->faq_id) }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-md text-sm font-medium">
                    キャンセル
                </a>
                <button type="button" wire:click="delete" wire:confirm="本当にこのFAQを削除しますか？"
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                    削除
                </button>
            </div>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                更新する
            </button>
        </div>
    </form>
</div>
