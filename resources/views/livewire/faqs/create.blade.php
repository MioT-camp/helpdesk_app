<?php

use App\Models\FAQ;
use App\Models\Category;
use App\Models\Tag;
use Livewire\Attributes\Validate;
use function Livewire\Volt\{state, with, computed, rules};

state([
    'question' => '',
    'answer' => '',
    'category_id' => '',
    'tags' => '',
    'priority' => 1,
    'difficulty' => 1,
    'is_active' => true,
]);

rules([
    'question' => 'required|string|max:500',
    'answer' => 'required|string',
    'category_id' => 'required|exists:categories,id',
    'tags' => 'nullable|string|max:255',
    'priority' => 'required|integer|between:1,3',
    'difficulty' => 'required|integer|between:1,3',
    'is_active' => 'boolean',
]);

$categories = computed(fn() => Category::active()->get());

$save = function () {
    $this->validate();

    $faq = FAQ::create([
        'question' => $this->question,
        'answer' => $this->answer,
        'category_id' => $this->category_id,
        'tags' => $this->tags,
        'priority' => $this->priority,
        'difficulty' => $this->difficulty,
        'is_active' => $this->is_active,
        'user_id' => auth()->id(),
        'search_keywords' => $this->question . ' ' . $this->answer . ' ' . $this->tags,
    ]);

    session()->flash('status', 'FAQが正常に作成されました。');
    return redirect()->route('faqs.show', $faq->faq_id);
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
                    <span class="ml-1 text-gray-500 dark:text-gray-400">FAQ作成</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">FAQ作成</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">新しいFAQを作成します</p>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <!-- 基本情報 -->
            <div class="space-y-6">
                <!-- 質問 -->
                <div>
                    <label for="question" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        質問 <span class="text-red-500">*</span>
                    </label>
                    <textarea id="question" wire:model="question" rows="3" placeholder="よくある質問を入力してください..."
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
                    <textarea id="answer" wire:model="answer" rows="8" placeholder="詳細な回答を入力してください..."
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

                <!-- タグ -->
                <div>
                    <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        タグ
                    </label>
                    <input type="text" id="tags" wire:model="tags" placeholder="タグをカンマ区切りで入力（例：ログイン,パスワード,エラー）"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">検索で使用されるタグをカンマ区切りで入力してください</p>
                    @error('tags')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 優先度 -->
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
                        @error('priority')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 難易度 -->
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
                        @error('difficulty')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 公開設定 -->
                <div>
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" wire:model="is_active"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            FAQを公開する
                        </label>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">チェックを外すと下書きとして保存されます</p>
                </div>
            </div>
        </div>

        <!-- アクションボタン -->
        <div class="flex justify-between">
            <a href="{{ route('faqs.index') }}"
                class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-6 py-2 rounded-md text-sm font-medium transition-colors">
                キャンセル
            </a>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium transition-colors"
                wire:loading.attr="disabled">
                <span wire:loading.remove>FAQを作成</span>
                <span wire:loading class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    作成中...
                </span>
            </button>
        </div>
    </form>
</div>
