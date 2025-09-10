<?php

use App\Models\Inquiry;
use App\Models\Category;
use App\Models\User;
use App\Models\FAQ;
use App\Livewire\Actions\SearchRelatedFaqs;
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
    'linked_faq_ids' => [],
    'expanded_faq_id' => null,
]);

$categories = computed(fn() => Category::active()->orderBy('name')->get());
$users = computed(fn() => User::where('is_active', true)->orderBy('name')->get());

// 都道府県リスト
$prefectures = computed(fn() => ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県']);

// ユーザー属性リスト
$userAttributes = computed(fn() => ['個人', '法人', '代理店', '関連会社', 'その他']);

// 関連FAQ検索
$relatedFaqs = computed(function () {
    $searchText = trim($this->subject . ' ' . $this->summary . ' ' . $this->content);
    if (strlen($searchText) < 3) {
        return collect();
    }

    $searchAction = new SearchRelatedFaqs();
    return $searchAction->execute($this->subject, $this->summary, $this->content, $this->category_id ?: null, 5);
});

rules([
    'sender_email' => 'required|email|max:255',
    'customer_id' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9]+$/',
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
        'linked_faq_ids' => $this->linked_faq_ids,
    ]);

    session()->flash('status', '問い合わせを登録しました。');

    return redirect()->route('inquiries.show', ['inquiry_id' => $inquiry->inquiry_id]);
};

// FAQ関連のメソッド
$toggleFaqExpansion = function ($faqId) {
    if ($this->expanded_faq_id === $faqId) {
        $this->expanded_faq_id = null;
    } else {
        $this->expanded_faq_id = $faqId;
    }
};

$linkFaq = function ($faqId) {
    if (!in_array($faqId, $this->linked_faq_ids)) {
        $this->linked_faq_ids[] = $faqId;
    }
};

$unlinkFaq = function ($faqId) {
    $this->linked_faq_ids = array_filter($this->linked_faq_ids, fn($id) => $id != $faqId);
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

    <!-- 3カラムレイアウト -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- 左カラム: 基本情報 -->
        <div class="lg:col-span-4">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">基本情報</h3>

                <div class="space-y-6">
                    <!-- 送信者メールアドレス -->
                    <div>
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
                            顧客ID <span class="text-gray-500 text-xs">(半角英数のみ)</span>
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
                        <select id="category_id" wire:model.live="category_id"
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
                    <div>
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
        </div>

        <!-- 中央カラム: 問い合わせ内容 -->
        <div class="lg:col-span-5">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">問い合わせ内容</h3>

                <form wire:submit="save" class="space-y-6">
                    <!-- 件名 -->
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            件名 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="subject" wire:model.live="subject" placeholder="問い合わせの件名を入力してください"
                            x-on:paste="$nextTick(() => $wire.set('subject', $event.target.value))"
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
                        <textarea id="summary" wire:model.live="summary" rows="3" placeholder="問い合わせ内容の要約（任意）"
                            x-on:paste="$nextTick(() => $wire.set('summary', $event.target.value))"
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
                        <textarea id="content" wire:model.live="content" rows="8" placeholder="問い合わせの詳細内容を入力してください"
                            x-on:paste="$nextTick(() => $wire.set('content', $event.target.value))"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required></textarea>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">問題の詳細、エラーメッセージ、発生状況などを具体的に記載してください。
                        </p>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
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

        <!-- 右カラム: 関連FAQ -->
        <div class="lg:col-span-3">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 sticky top-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">関連FAQ</h3>

                @if ($this->relatedFaqs->count() > 0)
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        @foreach ($this->relatedFaqs as $faq)
                            <div
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2 overflow-hidden"
                                            style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                            {{ $faq->question }}
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                            {{ $faq->category->name ?? 'カテゴリなし' }}
                                        </p>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if ($faq->priority == 1) bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                                @elseif($faq->priority == 2) bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                                @elseif($faq->priority == 3) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @endif">
                                                @if ($faq->priority == 1)
                                                    低
                                                @elseif($faq->priority == 2)
                                                    中
                                                @elseif($faq->priority == 3)
                                                    高
                                                @else
                                                    緊急
                                                @endif
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                閲覧: {{ $faq->count }}回
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <button wire:click="toggleFaqExpansion({{ $faq->faq_id }})"
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-700 dark:hover:bg-blue-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                        {{ $expanded_faq_id === $faq->faq_id ? '閉じる' : '詳細' }}
                                    </button>
                                    @if (in_array($faq->faq_id, $linked_faq_ids))
                                        <button wire:click="unlinkFaq({{ $faq->faq_id }})"
                                            class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-red-900 dark:text-red-300 dark:border-red-700 dark:hover:bg-red-800">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    @else
                                        <button wire:click="linkFaq({{ $faq->faq_id }})"
                                            class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-green-600 bg-green-50 border border-green-200 rounded-md hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:bg-green-900 dark:text-green-300 dark:border-green-700 dark:hover:bg-green-800">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>

                                <!-- FAQ詳細（展開時） -->
                                @if ($expanded_faq_id === $faq->faq_id)
                                    <div
                                        class="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700">
                                        <div class="space-y-3">
                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">質問</label>
                                                <p class="text-sm text-gray-900 dark:text-white">{{ $faq->question }}
                                                </p>
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">回答</label>
                                                <div
                                                    class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap max-h-32 overflow-y-auto">
                                                    {{ $faq->answer }}
                                                </div>
                                            </div>

                                            @if ($faq->tags || $faq->tagRelations->count() > 0)
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">タグ</label>
                                                    <div class="flex flex-wrap gap-1">
                                                        @if ($faq->tags)
                                                            @foreach (explode(',', $faq->tags) as $tag)
                                                                @if (trim($tag))
                                                                    <span
                                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-300">
                                                                        {{ trim($tag) }}
                                                                    </span>
                                                                @endif
                                                            @endforeach
                                                        @endif

                                                        @foreach ($faq->tagRelations as $tag)
                                                            <span
                                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                                {{ $tag->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.47-.881-6.08-2.33">
                            </path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">関連FAQが見つかりません</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            問い合わせ内容を入力すると、関連するFAQが表示されます。
                        </p>
                    </div>
                @endif

                <!-- 紐付け済みFAQ -->
                @if (count($linked_faq_ids) > 0)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">紐付け済みFAQ</h4>
                        <div class="space-y-2">
                            @foreach ($linked_faq_ids as $faqId)
                                @php $faq = FAQ::with(['category', 'tagRelations'])->find($faqId) @endphp
                                @if ($faq)
                                    <div
                                        class="flex items-center justify-between p-2 bg-green-50 dark:bg-green-900 rounded-md">
                                        <span
                                            class="text-sm text-green-800 dark:text-green-300 truncate">{{ $faq->question }}</span>
                                        <button wire:click="unlinkFaq({{ $faqId }})"
                                            class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
