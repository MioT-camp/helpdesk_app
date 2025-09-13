<?php

use App\Models\Todo;
use function Livewire\Volt\{state, mount};

state([
    'todos' => [],
    'filter' => 'all', // all, pending, completed
    'sortBy' => 'due_date', // due_date, priority, created_at
    'sortOrder' => 'asc', // asc, desc
]);

mount(function () {
    $this->loadTodos();
});

$loadTodos = function () {
    $query = Todo::where('user_id', auth()->id())->with('user');

    // フィルタリング
    if ($this->filter === 'pending') {
        $query->where('is_completed', false);
    } elseif ($this->filter === 'completed') {
        $query->where('is_completed', true);
    }

    // ソート
    $query->orderBy($this->sortBy, $this->sortOrder);

    $this->todos = $query->get();
};

$toggleComplete = function (Todo $todo) {
    $todo->update(['is_completed' => !$todo->is_completed]);
    $this->loadTodos();
};

$deleteTodo = function (Todo $todo) {
    $todo->delete();
    $this->loadTodos();
};

$setFilter = function (string $filter) {
    $this->filter = $filter;
    $this->loadTodos();
};

$setSort = function (string $sortBy) {
    if ($this->sortBy === $sortBy) {
        $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
    } else {
        $this->sortBy = $sortBy;
        $this->sortOrder = 'asc';
    }
    $this->loadTodos();
};

?>

<div class="space-y-6">
    <!-- ヘッダー -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">ToDoリスト</h1>
            <p class="text-gray-600">タスクを管理して効率的に作業を進めましょう</p>
        </div>
        <a href="{{ route('todos.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            新しいToDo
        </a>
    </div>

    <!-- フィルターとソート -->
    <div class="flex flex-wrap gap-4 items-center">
        <!-- フィルター -->
        <div class="flex gap-2">
            <button wire:click="setFilter('all')"
                class="px-3 py-1 rounded {{ $filter === 'all' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                すべて
            </button>
            <button wire:click="setFilter('pending')"
                class="px-3 py-1 rounded {{ $filter === 'pending' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                未完了
            </button>
            <button wire:click="setFilter('completed')"
                class="px-3 py-1 rounded {{ $filter === 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                完了済み
            </button>
        </div>

        <!-- ソート -->
        <div class="flex gap-2">
            <span class="text-sm text-gray-600">並び順:</span>
            <button wire:click="setSort('due_date')"
                class="px-3 py-1 rounded text-sm {{ $sortBy === 'due_date' ? 'bg-gray-200' : 'bg-gray-100' }}">
                期限
            </button>
            <button wire:click="setSort('priority')"
                class="px-3 py-1 rounded text-sm {{ $sortBy === 'priority' ? 'bg-gray-200' : 'bg-gray-100' }}">
                優先度
            </button>
            <button wire:click="setSort('created_at')"
                class="px-3 py-1 rounded text-sm {{ $sortBy === 'created_at' ? 'bg-gray-200' : 'bg-gray-100' }}">
                作成日
            </button>
        </div>
    </div>

    <!-- ToDoリスト -->
    <div class="space-y-3">
        @forelse($todos as $todo)
            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start gap-3">
                    <!-- チェックボックス -->
                    <input type="checkbox" wire:click="toggleComplete({{ $todo->id }})"
                        {{ $todo->is_completed ? 'checked' : '' }}
                        class="mt-1 h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">

                    <div class="flex-1 min-w-0">
                        <!-- タイトルと優先度 -->
                        <div class="flex items-center gap-2 mb-2">
                            <h3
                                class="text-lg font-medium {{ $todo->is_completed ? 'line-through text-gray-500' : 'text-gray-900' }}">
                                {{ $todo->title }}
                            </h3>
                            <span
                                class="px-2 py-1 text-xs rounded-full
                                {{ $todo->priority === 'high' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $todo->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $todo->priority === 'low' ? 'bg-green-100 text-green-700' : '' }}">
                                {{ $todo->priority_label }}
                            </span>
                        </div>

                        <!-- 説明 -->
                        @if ($todo->description)
                            <p class="text-gray-600 text-sm mb-2">{{ $todo->description }}</p>
                        @endif

                        <!-- 期限と作成日 -->
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            @if ($todo->due_date)
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    期限: {{ $todo->due_date->format('Y/m/d') }}
                                </span>
                            @endif
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                作成: {{ $todo->created_at->format('Y/m/d') }}
                            </span>
                        </div>
                    </div>

                    <!-- アクションボタン -->
                    <div class="flex gap-2">
                        <a href="{{ route('todos.edit', $todo) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            編集
                        </a>
                        <button wire:click="deleteTodo({{ $todo->id }})" wire:confirm="このToDoを削除しますか？"
                            class="text-red-600 hover:text-red-800 text-sm">
                            削除
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                    </path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">ToDoがありません</h3>
                <p class="mt-1 text-sm text-gray-500">新しいToDoを作成して始めましょう。</p>
                <div class="mt-6">
                    <a href="{{ route('todos.create') }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        新しいToDo
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>
