<?php

use App\Models\Inquiry;
use App\Models\Category;
use App\Models\User;
use Illuminate\Validation\Rule;
use function Livewire\Volt\{state, computed, rules};

state([
    'sender_email' => '',
    'customer_id' => '',
    'prefecture' => '',
    'user_attribute' => '',
    'category_id' => '',
    'subject' => '',
    'summary' => '',
    'content' => '',
    'priority' => Inquiry::PRIORITY_NORMAL,
    'assigned_user_id' => '',
    'response_deadline' => '',
]);

$categories = computed(fn() => Category::active()->orderBy('name')->get());
$users = computed(fn() => User::where('is_active', true)->orderBy('name')->get());

// 都道府県リスト
$prefectures = computed(fn() => ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県']);

// ユーザー属性リスト
$userAttributes = computed(fn() => ['個人', '法人', '代理店', '関連会社', 'その他']);

rules([
    'sender_email' => 'required|email|max:255',
    'customer_id' => 'nullable|string|max:100',
    'prefecture' => 'nullable|string|max:20',
    'user_attribute' => 'nullable|string|max:50',
    'category_id' => 'nullable|exists:categories,id',
    'subject' => 'required|string|max:500',
    'summary' => 'nullable|string',
    'content' => 'required|string',
    'priority' => ['required', 'integer', Rule::in([1, 2, 3, 4])],
    'assigned_user_id' => 'nullable|exists:users,id',
    'response_deadline' => 'nullable|date|after:now',
]);

$save = function () {
    $this->validate();

    $inquiry = Inquiry::create([
        'status' => Inquiry::STATUS_PENDING,
        'received_at' => now(),
        'sender_email' => $this->sender_email,
        'customer_id' => $this->customer_id ?: null,
        'prefecture' => $this->prefecture ?: null,
        'user_attribute' => $this->user_attribute ?: null,
        'category_id' => $this->category_id ?: null,
        'subject' => $this->subject,
        'summary' => $this->summary ?: null,
        'content' => $this->content,
        'priority' => $this->priority,
        'assigned_user_id' => $this->assigned_user_id ?: null,
        'created_user_id' => auth()->id(),
        'response_deadline' => $this->response_deadline ? \Carbon\Carbon::parse($this->response_deadline) : null,
    ]);

    session()->flash('status', '問い合わせを登録しました。');

    return redirect()->route('inquiries.show', ['inquiry_id' => $inquiry->inquiry_id]);
};

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- ヘッダー -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">新規問い合わせ登録</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">問い合わせ情報を入力して登録してください。</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('inquiries.index') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    一覧に戻る
                </a>
            </div>
        </div>
    </div>

    <!-- フォーム -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <form wire:submit="save" class="p-6 space-y-8">

            <!-- 基本情報 -->
            <div class="border-b border-gray-200 dark:border-gray-700 pb-8">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">基本情報</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 送信者メールアドレス -->
                    <div class="md:col-span-2">
                        <label for="sender_email"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            送信者メールアドレス <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="sender_email" wire:model="sender_email"
                            placeholder="example@domain.com"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                        @error('sender_email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 顧客ID -->
                    <div>
                        <label for="customer_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            顧客ID
                        </label>
                        <input type="text" id="customer_id" wire:model="customer_id" placeholder="CUST001"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('customer_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 都道府県 -->
                    <div>
                        <label for="prefecture" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            都道府県
                        </label>
                        <select id="prefecture" wire:model="prefecture"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">選択してください</option>
                            @foreach ($this->prefectures as $prefecture)
                                <option value="{{ $prefecture }}">{{ $prefecture }}</option>
                            @endforeach
                        </select>
                        @error('prefecture')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ユーザー属性 -->
                    <div>
                        <label for="user_attribute"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            ユーザー属性
                        </label>
                        <select id="user_attribute" wire:model="user_attribute"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">選択してください</option>
                            @foreach ($this->userAttributes as $attribute)
                                <option value="{{ $attribute }}">{{ $attribute }}</option>
                            @endforeach
                        </select>
                        @error('user_attribute')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- カテゴリ -->
                    <div>
                        <label for="category_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            カテゴリ
                        </label>
                        <select id="category_id" wire:model="category_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">選択してください</option>
                            @foreach ($this->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 問い合わせ内容 -->
            <div class="border-b border-gray-200 dark:border-gray-700 pb-8">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">問い合わせ内容</h3>

                <div class="space-y-6">
                    <!-- 件名 -->
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            件名 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="subject" wire:model="subject" placeholder="問い合わせの件名を入力してください"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                        @error('subject')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 要約 -->
                    <div>
                        <label for="summary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            要約
                        </label>
                        <textarea id="summary" wire:model="summary" rows="3" placeholder="問い合わせ内容の要約（任意）"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">問い合わせ内容の簡潔な要約を入力してください。</p>
                        @error('summary')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 詳細内容 -->
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            詳細内容 <span class="text-red-500">*</span>
                        </label>
                        <textarea id="content" wire:model="content" rows="8" placeholder="問い合わせの詳細内容を入力してください"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required></textarea>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">問題の詳細、エラーメッセージ、発生状況などを具体的に記載してください。</p>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 対応設定 -->
            <div class="pb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">対応設定</h3>

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
                            <option value="4">緊急</option>
                        </select>
                        @error('priority')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 担当者 -->
                    <div>
                        <label for="assigned_user_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            担当者
                        </label>
                        <select id="assigned_user_id" wire:model="assigned_user_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">選択してください</option>
                            @foreach ($this->users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">未選択の場合は後で割り当てることができます。</p>
                        @error('assigned_user_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 回答期限 -->
                    <div class="md:col-span-2">
                        <label for="response_deadline"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            回答期限
                        </label>
                        <input type="datetime-local" id="response_deadline" wire:model="response_deadline"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">回答期限を設定します（任意）。</p>
                        @error('response_deadline')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- アクションボタン -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('inquiries.index') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    キャンセル
                </a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    登録する
                </button>
            </div>

        </form>
    </div>
</div>
