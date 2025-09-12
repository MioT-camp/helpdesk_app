<?php

use App\Models\Inquiry;
use App\Models\User;
use App\Models\FAQ;
use App\Livewire\Actions\SearchRelatedFaqs;
use function Livewire\Volt\{state, mount, computed, rules};

state(['inquiry', 'response' => '', 'status' => '', 'assigned_user_id' => '', 'priority' => 2, 'expanded_faq_id' => null, 'editing_mode' => false, 'edit_subject' => '', 'edit_summary' => '', 'edit_content' => '', 'edit_sender_email' => '', 'edit_customer_id' => '', 'edit_prefecture' => '', 'edit_user_attribute' => '', 'edit_category_id' => '', 'response_expanded_faq_id' => null]);

rules([
    'response' => 'required|string',
    'status' => 'required|in:pending,in_progress,completed,closed',
    'assigned_user_id' => 'nullable|exists:users,id',
    'priority' => 'required|integer|between:1,4',
    'edit_subject' => 'required|string|max:500',
    'edit_summary' => 'nullable|string',
    'edit_content' => 'required|string',
    'edit_sender_email' => 'required|email|max:255',
    'edit_customer_id' => 'nullable|string|max:100',
    'edit_prefecture' => 'nullable|string|max:20',
    'edit_user_attribute' => 'nullable|string|max:50',
    'edit_category_id' => 'nullable|exists:categories,id',
]);

mount(function ($inquiry_id) {
    $this->inquiry = Inquiry::with(['category', 'assignedUser', 'createdUser'])
        ->where('inquiry_id', $inquiry_id)
        ->firstOrFail();

    $this->response = $this->inquiry->response ?? '';
    $this->status = $this->inquiry->status;
    $this->assigned_user_id = $this->inquiry->assigned_user_id;
    $this->priority = $this->inquiry->priority;

    // 編集用フィールドの初期化
    $this->edit_subject = $this->inquiry->subject;
    $this->edit_summary = $this->inquiry->summary ?? '';
    $this->edit_content = $this->inquiry->content;
    $this->edit_sender_email = $this->inquiry->sender_email;
    $this->edit_customer_id = $this->inquiry->customer_id ?? '';
    $this->edit_prefecture = $this->inquiry->prefecture ?? '';
    $this->edit_user_attribute = $this->inquiry->user_attribute ?? '';
    $this->edit_category_id = $this->inquiry->category_id;
});

$users = computed(fn() => User::where('is_active', true)->get());
$categories = computed(fn() => \App\Models\Category::active()->orderBy('name')->get());

// 都道府県リスト
$prefectures = computed(fn() => ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県']);

// ユーザー属性リスト
$userAttributes = computed(fn() => ['個人', '法人', '代理店', '関連会社', 'その他']);

// 関連FAQ検索
$relatedFaqs = computed(function () {
    $searchAction = new SearchRelatedFaqs();
    return $searchAction->execute($this->inquiry->subject, $this->inquiry->summary, $this->inquiry->content, $this->inquiry->category_id, 5);
});

// 回答内容に基づく関連FAQ検索
$responseRelatedFaqs = computed(function () {
    $responseText = trim($this->response);
    if (strlen($responseText) < 3) {
        return collect();
    }

    $searchAction = new SearchRelatedFaqs();
    return $searchAction->execute('', '', $responseText, $this->inquiry->category_id, 5);
});

$updateStatus = function () {
    $this->inquiry->update([
        'status' => $this->status,
        'assigned_user_id' => $this->assigned_user_id,
        'priority' => $this->priority,
    ]);

    session()->flash('status', 'ステータスが更新されました。');
};

$saveResponse = function () {
    $this->validate(['response' => 'required|string']);

    $this->inquiry->update([
        'response' => $this->response,
        'status' => 'completed',
        'first_response_at' => $this->inquiry->first_response_at ?? now(),
        'completed_at' => now(),
    ]);

    session()->flash('status', '回答が保存されました。');
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
    $linkedFaqIds = $this->inquiry->linked_faq_ids ?? [];
    if (!in_array($faqId, $linkedFaqIds)) {
        $linkedFaqIds[] = $faqId;
        $this->inquiry->update(['linked_faq_ids' => $linkedFaqIds]);
    }
};

$unlinkFaq = function ($faqId) {
    $linkedFaqIds = $this->inquiry->linked_faq_ids ?? [];
    $linkedFaqIds = array_filter($linkedFaqIds, fn($id) => $id != $faqId);
    $this->inquiry->update(['linked_faq_ids' => array_values($linkedFaqIds)]);
};

$saveResponse = function () {
    $this->validate([
        'response' => 'required|string|max:10000',
        'status' => 'required|in:pending,in_progress,completed,closed',
        'assigned_user_id' => 'nullable|exists:users,id',
        'priority' => 'required|integer|min:1|max:4',
    ]);

    $updateData = [
        'response' => $this->response,
        'status' => $this->status,
        'assigned_user_id' => $this->assigned_user_id,
        'priority' => $this->priority,
    ];

    // 初回回答の場合
    if (!$this->inquiry->first_response_at && $this->response) {
        $updateData['first_response_at'] = now();
    }

    // 完了の場合
    if ($this->status === 'completed' && !$this->inquiry->completed_at) {
        $updateData['completed_at'] = now();
    }

    $this->inquiry->update($updateData);

    session()->flash('message', '回答を保存しました。');
};

// 編集モードの制御
$startEditing = function () {
    $this->editing_mode = true;
    // 編集開始時に最新の値を設定
    $this->edit_subject = $this->inquiry->subject;
    $this->edit_summary = $this->inquiry->summary ?? '';
    $this->edit_content = $this->inquiry->content;
    $this->edit_sender_email = $this->inquiry->sender_email;
    $this->edit_customer_id = $this->inquiry->customer_id ?? '';
    $this->edit_prefecture = $this->inquiry->prefecture ?? '';
    $this->edit_user_attribute = $this->inquiry->user_attribute ?? '';
    $this->edit_category_id = $this->inquiry->category_id;
};

$cancelEditing = function () {
    $this->editing_mode = false;
    $this->resetValidation();
};

$saveInquiryEdit = function () {
    $this->validate([
        'edit_subject' => 'required|string|max:500',
        'edit_summary' => 'nullable|string',
        'edit_content' => 'required|string',
        'edit_sender_email' => 'required|email|max:255',
        'edit_customer_id' => 'nullable|string|max:100',
        'edit_prefecture' => 'nullable|string|max:20',
        'edit_user_attribute' => 'nullable|string|max:50',
        'edit_category_id' => 'nullable|exists:categories,id',
    ]);

    $this->inquiry->update([
        'subject' => $this->edit_subject,
        'summary' => $this->edit_summary ?: null,
        'content' => $this->edit_content,
        'sender_email' => $this->edit_sender_email,
        'customer_id' => $this->edit_customer_id ?: null,
        'prefecture' => $this->edit_prefecture ?: null,
        'user_attribute' => $this->edit_user_attribute ?: null,
        'category_id' => $this->edit_category_id ?: null,
    ]);

    $this->editing_mode = false;
    session()->flash('message', '問い合わせ内容を更新しました。');
};

// 回答用FAQ関連のメソッド
$toggleResponseFaqExpansion = function ($faqId) {
    if ($this->response_expanded_faq_id === $faqId) {
        $this->response_expanded_faq_id = null;
    } else {
        $this->response_expanded_faq_id = $faqId;
    }
};

$insertFaqToResponse = function ($faqId) {
    $faq = FAQ::find($faqId);
    if (!$faq) {
        return;
    }

    $faqAnswer = $faq->answer;
    $faqReference = "\n\n※参考FAQ #{$faq->faq_id}: {$faq->question}\n{$faqAnswer}";

    // 現在の回答に追加
    if (trim($this->response)) {
        $this->response .= $faqReference;
    } else {
        $this->response = trim($faqReference);
    }
};

?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- フラッシュメッセージ -->
    @if (session()->has('message'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <!-- パンくずナビ -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('inquiries.index') }}"
                    class="text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                    問い合わせ管理
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-gray-500 dark:text-gray-400">問い合わせ詳細</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- メインコンテンツ -->
        <div class="lg:col-span-8 space-y-6">
            <!-- 問い合わせ内容 -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            @if ($editing_mode)
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            件名 <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" wire:model="edit_subject"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            placeholder="件名を入力してください">
                                        @error('edit_subject')
                                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            送信者メールアドレス <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" wire:model="edit_sender_email"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            placeholder="メールアドレスを入力してください">
                                        @error('edit_sender_email')
                                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                顧客ID
                                            </label>
                                            <input type="text" wire:model="edit_customer_id"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                placeholder="顧客IDを入力してください">
                                            @error('edit_customer_id')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                カテゴリ
                                            </label>
                                            <select wire:model="edit_category_id"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                                <option value="">カテゴリを選択してください</option>
                                                @foreach ($this->categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('edit_category_id')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                都道府県
                                            </label>
                                            <select wire:model="edit_prefecture"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                                <option value="">都道府県を選択してください</option>
                                                @foreach ($this->prefectures as $prefecture)
                                                    <option value="{{ $prefecture }}">{{ $prefecture }}</option>
                                                @endforeach
                                            </select>
                                            @error('edit_prefecture')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                ユーザー属性
                                            </label>
                                            <select wire:model="edit_user_attribute"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                                <option value="">ユーザー属性を選択してください</option>
                                                @foreach ($this->userAttributes as $attribute)
                                                    <option value="{{ $attribute }}">{{ $attribute }}</option>
                                                @endforeach
                                            </select>
                                            @error('edit_user_attribute')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 pt-4">
                                        <button wire:click="saveInquiryEdit"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                            保存
                                        </button>
                                        <button wire:click="cancelEditing"
                                            class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                            キャンセル
                                        </button>
                                    </div>
                                </div>
                            @else
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $inquiry->subject }}
                                </h1>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">問い合わせ ID:
                                    #{{ $inquiry->inquiry_id }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            @if (!$editing_mode)
                                <button wire:click="startEditing"
                                    class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                    <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                    編集
                                </button>
                            @endif
                            @switch($inquiry->status)
                                @case('pending')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        未対応
                                    </span>
                                @break

                                @case('in_progress')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        対応中
                                    </span>
                                @break

                                @case('completed')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        完了
                                    </span>
                                @break

                                @case('closed')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        クローズ
                                    </span>
                                @break
                            @endswitch
                        </div>
                    </div>
                </div>

                @if ($editing_mode)
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            要約
                        </label>
                        <textarea wire:model="edit_summary" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="要約を入力してください（任意）"></textarea>
                        @error('edit_summary')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="px-6 py-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            問い合わせ内容 <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="edit_content" rows="8"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="問い合わせ内容を入力してください"></textarea>
                        @error('edit_content')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    @if ($inquiry->summary)
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-md font-medium text-gray-900 dark:text-white mb-3">要約</h3>
                            <div class="prose dark:prose-invert max-w-none">
                                {!! nl2br(e($inquiry->summary)) !!}
                            </div>
                        </div>
                    @endif

                    <div class="px-6 py-6">
                        <div class="prose dark:prose-invert max-w-none">
                            {!! nl2br(e($inquiry->content)) !!}
                        </div>
                    </div>
                @endif
            </div>

            <!-- 問い合わせ情報 -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">問い合わせ情報</h2>
                </div>

                <div class="px-6 py-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-gray-400">送信者メールアドレス</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ $inquiry->sender_email }}</dd>
                        </div>
                        @if ($inquiry->customer_id)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">顧客ID</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">{{ $inquiry->customer_id }}</dd>
                            </div>
                        @endif
                        @if ($inquiry->prefecture)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">都道府県</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">{{ $inquiry->prefecture }}</dd>
                            </div>
                        @endif
                        @if ($inquiry->user_attribute)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">ユーザー属性</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">{{ $inquiry->user_attribute }}</dd>
                            </div>
                        @endif
                        @if ($inquiry->category)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">カテゴリ</dt>
                                <dd class="mt-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        style="background-color: {{ $inquiry->category->color ?? '#6B7280' }}20; color: {{ $inquiry->category->color ?? '#6B7280' }}">
                                        {{ $inquiry->category->name }}
                                    </span>
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-gray-400">受信日時</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">
                                {{ $inquiry->received_at->format('Y年m月d日 H:i') }}</dd>
                        </div>
                        @if ($inquiry->response_deadline)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">回答期限</dt>
                                <dd
                                    class="mt-1 text-gray-900 dark:text-white {{ $inquiry->response_deadline->isPast() ? 'text-red-500' : '' }}">
                                    {{ $inquiry->response_deadline->format('Y年m月d日 H:i') }}
                                </dd>
                            </div>
                        @endif
                        @if ($inquiry->first_response_at)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">初回回答日時</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">
                                    {{ $inquiry->first_response_at->format('Y年m月d日 H:i') }}</dd>
                            </div>
                        @endif
                        @if ($inquiry->completed_at)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">完了日時</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">
                                    {{ $inquiry->completed_at->format('Y年m月d日 H:i') }}</dd>
                            </div>
                        @endif
                        @if ($inquiry->assigned_user_id)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">担当者</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">
                                    {{ $inquiry->assignedUser->name ?? '不明' }}
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-gray-400">優先度</dt>
                            <dd class="mt-1">
                                @switch($inquiry->priority)
                                    @case(1)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                            低
                                        </span>
                                    @break

                                    @case(2)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200">
                                            中
                                        </span>
                                    @break

                                    @case(3)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200">
                                            高
                                        </span>
                                    @break

                                    @case(4)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200">
                                            緊急
                                        </span>
                                    @break
                                @endswitch
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- 回答登録 -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">回答登録</h2>
                </div>

                <form wire:submit="saveResponse" class="px-6 py-6 space-y-4">
                    <!-- 回答内容 -->
                    <div>
                        <label for="response" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            回答内容 <span class="text-red-500">*</span>
                        </label>
                        <textarea id="response" wire:model.live="response" rows="6" placeholder="回答内容を入力してください"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required></textarea>
                        @error('response')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 回答に基づく関連FAQ -->
                    @if ($this->responseRelatedFaqs->isNotEmpty())
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">
                                参考FAQ（回答テンプレート）
                            </h4>
                            <div class="space-y-3">
                                @foreach ($this->responseRelatedFaqs as $faq)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <button wire:click="toggleResponseFaqExpansion({{ $faq->faq_id }})"
                                                    class="text-left w-full">
                                                    <h5
                                                        class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                                        {{ $faq->question }}
                                                    </h5>
                                                </button>

                                                @if ($faq->category)
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs mt-1"
                                                        style="background-color: {{ $faq->category->color ?? '#6B7280' }}20; color: {{ $faq->category->color ?? '#6B7280' }}">
                                                        {{ $faq->category->name }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-2 ml-3">
                                                <button wire:click="insertFaqToResponse({{ $faq->faq_id }})"
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded hover:bg-blue-200 dark:hover:bg-blue-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                    回答に挿入
                                                </button>
                                            </div>
                                        </div>

                                        @if ($response_expanded_faq_id === $faq->faq_id)
                                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                                <div
                                                    class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">
                                                    {{ $faq->answer }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif


                    <!-- ステータス -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            ステータス <span class="text-red-500">*</span>
                        </label>
                        <select id="status" wire:model="status"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                            <option value="pending">未対応</option>
                            <option value="in_progress">対応中</option>
                            <option value="completed">完了</option>
                            <option value="closed">クローズ</option>
                        </select>
                        @error('status')
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
                            <option value="">担当者を選択</option>
                            @foreach ($this->users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_user_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 優先度 -->
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            優先度
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

                    <!-- 送信ボタン -->
                    <div class="pt-4">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            回答を保存
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- 右カラム: 関連FAQ -->
        <div class="lg:col-span-4">

            <!-- 関連FAQ -->
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
                                    @if (in_array($faq->faq_id, $inquiry->linked_faq_ids ?? []))
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
                            この問い合わせに関連するFAQは見つかりませんでした。
                        </p>
                    </div>
                @endif

                <!-- 紐付け済みFAQ -->
                @if (count($inquiry->linked_faq_ids ?? []) > 0)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">紐付け済みFAQ</h4>
                        <div class="space-y-2">
                            @foreach ($inquiry->linked_faq_ids as $faqId)
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
