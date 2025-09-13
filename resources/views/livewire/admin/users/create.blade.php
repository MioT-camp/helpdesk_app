<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] #[Title('新規ユーザー作成')] class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    #[Validate('required|string|min:8')]
    public string $password_confirmation = '';

    #[Validate('required|in:admin,manager,staff')]
    public string $role = 'staff';

    #[Validate('nullable|string|max:100')]
    public string $department = '';

    #[Validate('nullable|array')]
    public array $specialties = [];

    #[Validate('boolean')]
    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('create', User::class);
    }

    public function with(): array
    {
        return [
            'availableSpecialties' => ['Aシステム', 'Bシステム', 'システム全体', '制度', 'その他'],
            'roles' => [
                User::ROLE_ADMIN => '管理者',
                User::ROLE_MANAGER => 'マネージャー',
                User::ROLE_STAFF => 'スタッフ',
            ],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => $this->role,
            'department' => $this->department ?: null,
            'specialties' => $this->specialties ?: null,
            'is_active' => $this->is_active,
            'email_verified_at' => now(),
        ]);

        session()->flash('message', 'ユーザーを作成しました。');

        $this->redirect(route('admin.users.show', $user->id), navigate: true);
    }

    public function cancel(): void
    {
        $this->redirect(route('admin.users.index'), navigate: true);
    }
};

?>

<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">新規ユーザー作成</h1>
                <p class="mt-1 text-sm text-gray-600">新しいシステムユーザーを作成します</p>
            </div>
            <flux:button href="{{ route('admin.users.index') }}" variant="ghost">
                <flux:icon.arrow-left class="size-4" />
                一覧に戻る
            </flux:button>
        </div>
    </div>

    <div class="max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            <!-- 基本情報 -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="mb-4 text-lg font-medium text-gray-900">基本情報</h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- 名前 -->
                    <div>
                        <flux:field>
                            <flux:label>名前 <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="name" placeholder="山田 太郎" />
                            <flux:error name="name" />
                        </flux:field>
                    </div>

                    <!-- メールアドレス -->
                    <div>
                        <flux:field>
                            <flux:label>メールアドレス <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="email" type="email" placeholder="user@example.com" />
                            <flux:error name="email" />
                        </flux:field>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- パスワード -->
                    <div>
                        <flux:field>
                            <flux:label>パスワード <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="password" type="password" placeholder="8文字以上" />
                            <flux:error name="password" />
                        </flux:field>
                    </div>

                    <!-- パスワード確認 -->
                    <div>
                        <flux:field>
                            <flux:label>パスワード確認 <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="password_confirmation" type="password" placeholder="パスワードを再入力" />
                            <flux:error name="password_confirmation" />
                        </flux:field>
                    </div>
                </div>
            </div>

            <!-- 権限・設定 -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="mb-4 text-lg font-medium text-gray-900">権限・設定</h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- 権限 -->
                    <div>
                        <flux:field>
                            <flux:label>権限 <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="role">
                                @foreach ($roles as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="role" />
                        </flux:field>
                    </div>

                    <!-- 部署 -->
                    <div>
                        <flux:field>
                            <flux:label>部署</flux:label>
                            <flux:input wire:model="department" placeholder="運用保守チーム" />
                            <flux:error name="department" />
                        </flux:field>
                    </div>
                </div>

                <!-- 専門分野 -->
                <div class="mt-6">
                    <flux:field>
                        <flux:label>専門分野</flux:label>
                        <div class="space-y-2">
                            @foreach ($availableSpecialties as $specialty)
                                <label class="flex items-center">
                                    <flux:checkbox wire:model="specialties" value="{{ $specialty }}"
                                        class="mr-2" />
                                    <span class="text-sm text-gray-700">{{ $specialty }}</span>
                                </label>
                            @endforeach
                        </div>
                        <flux:error name="specialties" />
                    </flux:field>
                </div>

                <!-- ステータス -->
                <div class="mt-6">
                    <flux:field>
                        <label class="flex items-center">
                            <flux:checkbox wire:model="is_active" class="mr-2" />
                            <span class="text-sm text-gray-700">アクティブ（有効）</span>
                        </label>
                        <flux:error name="is_active" />
                    </flux:field>
                </div>
            </div>

            <!-- ボタン -->
            <div class="flex justify-end space-x-3">
                <flux:button type="button" wire:click="cancel" variant="ghost">
                    キャンセル
                </flux:button>
                <flux:button type="submit" variant="primary">
                    ユーザーを作成
                </flux:button>
            </div>
        </form>
    </div>
</div>
