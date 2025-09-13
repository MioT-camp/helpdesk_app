<?php

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] #[Title('ユーザー管理')] class extends Component {
    public string $search = '';
    public string $roleFilter = '';
    public string $statusFilter = '';
    public string $departmentFilter = '';
    public int $perPage = 15;
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    public function with(): array
    {
        $query = User::query();

        // 検索条件
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('department', 'like', '%' . $this->search . '%');
            });
        }

        // 権限フィルター
        if ($this->roleFilter) {
            $query->where('role', $this->roleFilter);
        }

        // ステータスフィルター
        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        // 部署フィルター
        if ($this->departmentFilter) {
            $query->where('department', $this->departmentFilter);
        }

        // ソート
        $query->orderBy($this->sortBy, $this->sortDirection);

        $users = $query->paginate($this->perPage);

        return [
            'users' => $users,
            'departments' => User::distinct()->pluck('department')->filter()->sort()->values(),
            'roles' => [
                User::ROLE_ADMIN => '管理者',
                User::ROLE_MANAGER => 'マネージャー',
                User::ROLE_STAFF => 'スタッフ',
            ],
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDepartmentFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleActive(User $user): void
    {
        $this->authorize('toggleActive', $user);

        $user->update(['is_active' => !$user->is_active]);

        session()->flash('message', $user->is_active ? 'ユーザーを有効にしました。' : 'ユーザーを無効にしました。');
    }

    public function deleteUser(User $user): void
    {
        $this->authorize('delete', $user);

        $user->delete();

        session()->flash('message', 'ユーザーを削除しました。');
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->roleFilter = '';
        $this->statusFilter = '';
        $this->departmentFilter = '';
        $this->resetPage();
    }
};

?>

<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">ユーザー管理</h1>
                <p class="mt-1 text-sm text-gray-600">システムユーザーの管理を行います</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.users.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    新規ユーザー作成
                </a>
            </div>
        </div>
    </div>

    <!-- フィルター -->
    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
            <!-- 検索 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">検索</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="名前、メール、部署で検索"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <!-- 権限フィルター -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">権限</label>
                <select wire:model.live="roleFilter"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">すべて</option>
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- ステータスフィルター -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ステータス</label>
                <select wire:model.live="statusFilter"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">すべて</option>
                    <option value="active">有効</option>
                    <option value="inactive">無効</option>
                </select>
            </div>

            <!-- 部署フィルター -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">部署</label>
                <select wire:model.live="departmentFilter"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">すべて</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department }}">{{ $department }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 表示件数 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">表示件数</label>
                <select wire:model.live="perPage"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="10">10件</option>
                    <option value="15">15件</option>
                    <option value="25">25件</option>
                    <option value="50">50件</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button wire:click="resetFilters"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                フィルターリセット
            </button>
        </div>
    </div>

    <!-- ユーザー一覧 -->
    <div class="rounded-lg border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            <button wire:click="sortBy('name')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>名前</span>
                                @if ($sortBy === 'name')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            <button wire:click="sortBy('email')"
                                class="flex items-center space-x-1 hover:text-gray-700">
                                <span>メールアドレス</span>
                                @if ($sortBy === 'email')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            <button wire:click="sortBy('role')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>権限</span>
                                @if ($sortBy === 'role')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            <button wire:click="sortBy('department')"
                                class="flex items-center space-x-1 hover:text-gray-700">
                                <span>部署</span>
                                @if ($sortBy === 'department')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            <button wire:click="sortBy('is_active')"
                                class="flex items-center space-x-1 hover:text-gray-700">
                                <span>ステータス</span>
                                @if ($sortBy === 'is_active')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            <button wire:click="sortBy('last_login_at')"
                                class="flex items-center space-x-1 hover:text-gray-700">
                                <span>最終ログイン</span>
                                @if ($sortBy === 'last_login_at')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            操作
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-sm font-medium text-gray-600">
                                        {{ $user->initials() }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        @if ($user->id === Auth::id())
                                            <div class="text-xs text-blue-600">（あなた）</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                {{ $user->email }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    @if ($user->role === 'admin') bg-red-100 text-red-800
                                    @elseif($user->role === 'manager') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ $user->role_label }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                {{ $user->department ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->is_active ? '有効' : '無効' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ $user->last_login_at ? $user->last_login_at->format('Y/m/d H:i') : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.users.show', $user->id) }}"
                                        class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        詳細
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                        class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        編集
                                    </a>
                                    @if ($user->id !== Auth::id())
                                        <button wire:click="toggleActive({{ $user->id }})"
                                            wire:confirm="本当に{{ $user->is_active ? '無効' : '有効' }}にしますか？"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            {{ $user->is_active ? '無効' : '有効' }}
                                        </button>
                                        <button wire:click="deleteUser({{ $user->id }})"
                                            wire:confirm="本当に削除しますか？この操作は取り消せません。"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            削除
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                ユーザーが見つかりませんでした。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- ページネーション -->
        @if ($users->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- フラッシュメッセージ -->
    @if (session()->has('message'))
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
            role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif
</div>
