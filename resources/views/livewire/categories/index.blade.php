<?php

use App\Models\Category;
use function Livewire\Volt\{state, computed, rules};

state([
    'showCreateForm' => false,
    'editingCategory' => null,
    'name' => '',
    'slug' => '',
    'color' => '#3B82F6',
    'is_active' => true,
]);

rules([
    'name' => 'required|string|max:100',
    'slug' => 'required|string|max:100|unique:categories,slug',
    'color' => 'nullable|string|max:7',
    'is_active' => 'boolean',
]);

$categories = computed(fn() => Category::withCount(['faqs', 'inquiries'])->get());

$showCreateForm = function () {
    $this->showCreateForm = true;
    $this->resetForm();
};

$hideCreateForm = function () {
    $this->showCreateForm = false;
    $this->editingCategory = null;
    $this->resetForm();
};

$resetForm = function () {
    $this->name = '';
    $this->slug = '';
    $this->color = '#3B82F6';
    $this->is_active = true;
    $this->resetValidation();
};

$generateSlug = function () {
    $this->slug = Str::slug($this->name);
};

$create = function () {
    $this->validate();

    Category::create([
        'name' => $this->name,
        'slug' => $this->slug,
        'color' => $this->color,
        'is_active' => $this->is_active,
    ]);

    $this->hideCreateForm();
    session()->flash('status', 'カテゴリが作成されました。');
};

$edit = function ($categoryId) {
    $category = Category::findOrFail($categoryId);
    $this->editingCategory = $category;
    $this->name = $category->name;
    $this->slug = $category->slug;
    $this->color = $category->color ?? '#3B82F6';
    $this->is_active = $category->is_active;
    $this->showCreateForm = true;
};

$update = function () {
    $this->validate([
        'name' => 'required|string|max:100',
        'slug' => 'required|string|max:100|unique:categories,slug,' . $this->editingCategory->id,
        'color' => 'nullable|string|max:7',
        'is_active' => 'boolean',
    ]);

    $this->editingCategory->update([
        'name' => $this->name,
        'slug' => $this->slug,
        'color' => $this->color,
        'is_active' => $this->is_active,
    ]);

    $this->hideCreateForm();
    session()->flash('status', 'カテゴリが更新されました。');
};

$delete = function ($categoryId) {
    $category = Category::findOrFail($categoryId);
    $category->delete();
    session()->flash('status', 'カテゴリが削除されました。');
};

?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">カテゴリ管理</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">FAQと問い合わせのカテゴリを管理</p>
            </div>
            @if (!$showCreateForm)
                <button wire:click="showCreateForm"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    新しいカテゴリ
                </button>
            @endif
        </div>
    </div>

    @if ($showCreateForm)
        <!-- カテゴリ作成・編集フォーム -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $editingCategory ? 'カテゴリ編集' : 'カテゴリ作成' }}
                </h2>
                <button wire:click="hideCreateForm" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form wire:submit="{{ $editingCategory ? 'update' : 'create' }}" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            カテゴリ名 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" wire:model="name" wire:keyup="generateSlug"
                            placeholder="カテゴリ名を入力"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            スラッグ <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="slug" wire:model="slug" placeholder="URL用スラッグ"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            表示色
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="color" wire:model="color"
                                class="h-10 w-20 border border-gray-300 rounded-md">
                            <input type="text" wire:model="color" placeholder="#3B82F6"
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        @error('color')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" wire:model="is_active"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            アクティブ
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="hideCreateForm"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-md text-sm font-medium transition-colors">
                        キャンセル
                    </button>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium transition-colors">
                        {{ $editingCategory ? '更新' : '作成' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- カテゴリ一覧 -->
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            カテゴリ
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            スラッグ
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            FAQ数
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            問い合わせ数
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            ステータス
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            操作
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->categories as $category)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full mr-3"
                                        style="background-color: {{ $category->color ?? '#6B7280' }}"></div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $category->name }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $category->slug }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($category->faqs_count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($category->inquiries_count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($category->is_active)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        アクティブ
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        非アクティブ
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="edit({{ $category->id }})"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        編集
                                    </button>
                                    @if ($category->faqs_count === 0 && $category->inquiries_count === 0)
                                        <button wire:click="delete({{ $category->id }})" wire:confirm="このカテゴリを削除しますか？"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            削除
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                カテゴリが見つかりません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
