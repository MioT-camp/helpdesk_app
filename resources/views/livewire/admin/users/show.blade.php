<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] #[Title('ユーザー詳細')] class extends Component {
    public User $user;

    public function mount(User $user_id): void
    {
        $this->user = $user_id;
        $this->authorize('view', $this->user);
    }

    public function with(): array
    {
        return [
            'roles' => [
                User::ROLE_ADMIN => '管理者',
                User::ROLE_MANAGER => 'マネージャー',
                User::ROLE_STAFF => 'スタッフ',
            ],
        ];
    }

    public function toggleActive(): void
    {
        $this->authorize('toggleActive', $this->user);

        $this->user->update(['is_active' => !$this->user->is_active]);

        session()->flash('message', $this->user->is_active ? 'ユーザーを有効にしました。' : 'ユーザーを無効にしました。');
    }

    public function deleteUser(): void
    {
        $this->authorize('delete', $this->user);

        $this->user->delete();

        session()->flash('message', 'ユーザーを削除しました。');

        $this->redirect(route('admin.users.index'), navigate: true);
    }
};

?>

<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">ユーザー詳細</h1>
                <p class="mt-1 text-sm text-gray-600">{{ $user->name }} の詳細情報</p>
            </div>
            <div class="flex space-x-3">
                <flux:button href="{{ route('admin.users.index') }}" variant="ghost">
                    <flux:icon.arrow-left class="size-4" />
                    一覧に戻る
                </flux:button>
                <flux:button href="{{ route('admin.users.edit', $user->id) }}" variant="primary">
                    <flux:icon.pencil class="size-4" />
                    編集
                </flux:button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- 基本情報 -->
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="mb-4 text-lg font-medium text-gray-900">基本情報</h2>

                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">名前</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">メールアドレス</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">権限</dt>
                        <dd class="mt-1">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                @if ($user->role === 'admin') bg-red-100 text-red-800
                                @elseif($user->role === 'manager') bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ $user->role_label }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">部署</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->department ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">ステータス</dt>
                        <dd class="mt-1">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? '有効' : '無効' }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">最終ログイン</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $user->last_login_at ? $user->last_login_at->format('Y年m月d日 H:i') : '未ログイン' }}
                        </dd>
                    </div>
                </dl>

                <!-- 専門分野 -->
                @if ($user->specialties && count($user->specialties) > 0)
                    <div class="mt-6">
                        <dt class="text-sm font-medium text-gray-500">専門分野</dt>
                        <dd class="mt-2">
                            <div class="flex flex-wrap gap-2">
                                @foreach ($user->specialties as $specialty)
                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                        {{ $specialty }}
                                    </span>
                                @endforeach
                            </div>
                        </dd>
                    </div>
                @endif
            </div>

            <!-- 統計情報 -->
            <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="mb-4 text-lg font-medium text-gray-900">統計情報</h2>

                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">作成したFAQ</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $user->createdFaqs()->count() }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">担当中の問い合わせ</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $user->assignedInquiries()->count() }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">作成した問い合わせ</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $user->createdInquiries()->count() }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- サイドバー -->
        <div class="space-y-6">
            <!-- アバター -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 text-center">
                <div
                    class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gray-200 text-2xl font-medium text-gray-600">
                    {{ $user->initials() }}
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>

                @if ($user->id === Auth::id())
                    <div class="mt-2">
                        <span
                            class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                            あなた
                        </span>
                    </div>
                @endif
            </div>

            <!-- 操作 -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h3 class="mb-4 text-lg font-medium text-gray-900">操作</h3>

                <div class="space-y-3">
                    <flux:button href="{{ route('admin.users.edit', $user->id) }}" variant="primary" class="w-full">
                        <flux:icon.pencil class="size-4" />
                        編集
                    </flux:button>

                    @if ($user->id !== Auth::id())
                        <flux:button wire:click="toggleActive" variant="ghost" class="w-full"
                            wire:confirm="本当に{{ $user->is_active ? '無効' : '有効' }}にしますか？">
                            <flux:icon.power class="size-4" />
                            {{ $user->is_active ? '無効にする' : '有効にする' }}
                        </flux:button>

                        <flux:button wire:click="deleteUser" variant="ghost"
                            class="w-full text-red-600 hover:text-red-700" wire:confirm="本当に削除しますか？この操作は取り消せません。">
                            <flux:icon.trash class="size-4" />
                            削除
                        </flux:button>
                    @endif
                </div>
            </div>

            <!-- アカウント情報 -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h3 class="mb-4 text-lg font-medium text-gray-900">アカウント情報</h3>

                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">登録日</dt>
                        <dd class="text-sm text-gray-900">{{ $user->created_at->format('Y年m月d日') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">最終更新</dt>
                        <dd class="text-sm text-gray-900">{{ $user->updated_at->format('Y年m月d日 H:i') }}</dd>
                    </div>

                    @if ($user->email_verified_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">メール認証</dt>
                            <dd class="text-sm text-green-600">認証済み</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <!-- フラッシュメッセージ -->
    @if (session()->has('message'))
        <div class="mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif
</div>
