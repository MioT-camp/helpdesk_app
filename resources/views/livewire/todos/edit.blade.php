<?php

use App\Models\Todo;
use function Livewire\Volt\{state, rules, mount};

state([
    'todo' => null,
    'title' => '',
    'description' => '',
    'due_date' => '',
    'priority' => 'medium',
    'is_completed' => false,
]);

rules([
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'due_date' => 'nullable|date',
    'priority' => 'required|in:high,medium,low',
    'is_completed' => 'boolean',
]);

mount(function (Todo $todo) {
    $this->todo = $todo;
    $this->title = $todo->title;
    $this->description = $todo->description ?? '';
    $this->due_date = $todo->due_date?->format('Y-m-d') ?? '';
    $this->priority = $todo->priority;
    $this->is_completed = $todo->is_completed;
});

$save = function () {
    $this->validate();

    $this->todo->update([
        'title' => $this->title,
        'description' => $this->description,
        'due_date' => $this->due_date ?: null,
        'priority' => $this->priority,
        'is_completed' => $this->is_completed,
    ]);

    return redirect()->route('todos.index')->with('success', 'ToDoを更新しました。');
};

?>

<div class="max-w-2xl mx-auto">
    <!-- ヘッダー -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">ToDoを編集</h1>
        <p class="text-gray-600">タスクの内容を更新しましょう</p>
    </div>

    <!-- フォーム -->
    <form wire:submit="save" class="space-y-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <!-- タイトル -->
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    タイトル <span class="text-red-500">*</span>
                </label>
                <input type="text" id="title" wire:model="title"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                    placeholder="ToDoのタイトルを入力してください">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- 説明 -->
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    説明
                </label>
                <textarea id="description" wire:model="description" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                    placeholder="詳細な説明があれば入力してください（任意）"></textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- 期限 -->
            <div class="mb-4">
                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                    期限
                </label>
                <input type="date" id="due_date" wire:model="due_date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('due_date') border-red-500 @enderror">
                @error('due_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- 優先度 -->
            <div class="mb-4">
                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                    優先度 <span class="text-red-500">*</span>
                </label>
                <select id="priority" wire:model="priority"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('priority') border-red-500 @enderror">
                    <option value="high">高</option>
                    <option value="medium">中</option>
                    <option value="low">低</option>
                </select>
                @error('priority')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- 完了状態 -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" wire:model="is_completed"
                        class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                    <span class="ml-2 text-sm font-medium text-gray-700">完了済み</span>
                </label>
            </div>
        </div>

        <!-- ボタン -->
        <div class="flex gap-3 justify-end">
            <a href="{{ route('todos.index') }}"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                キャンセル
            </a>
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                更新
            </button>
        </div>
    </form>
</div>
