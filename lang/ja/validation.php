<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url' => ':attributeは有効なURLではありません。',
    'after' => ':attributeは:dateより後の日付にしてください。',
    'after_or_equal' => ':attributeは:date以降の日付にしてください。',
    'alpha' => ':attributeは英字のみで入力してください。',
    'alpha_dash' => ':attributeは英数字、ハイフン、アンダースコアのみで入力してください。',
    'alpha_num' => ':attributeは英数字のみで入力してください。',
    'array' => ':attributeは配列で入力してください。',
    'ascii' => ':attributeは半角英数字と記号のみで入力してください。',
    'before' => ':attributeは:dateより前の日付にしてください。',
    'before_or_equal' => ':attributeは:date以前の日付にしてください。',
    'between' => [
        'array' => ':attributeは:min個から:max個の間で選択してください。',
        'file' => ':attributeは:minKBから:maxKBの間で選択してください。',
        'numeric' => ':attributeは:minから:maxの間で入力してください。',
        'string' => ':attributeは:min文字から:max文字の間で入力してください。',
    ],
    'boolean' => ':attributeは真偽値で入力してください。',
    'can' => ':attributeには権限がありません。',
    'confirmed' => ':attributeの確認が一致しません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attributeは正しい日付ではありません。',
    'date_equals' => ':attributeは:dateと同じ日付にしてください。',
    'date_format' => ':attributeは:formatの形式で入力してください。',
    'decimal' => ':attributeは小数点以下:decimal桁で入力してください。',
    'declined' => ':attributeを拒否してください。',
    'declined_if' => ':otherが:valueの場合、:attributeを拒否してください。',
    'different' => ':attributeと:otherは異なる値にしてください。',
    'digits' => ':attributeは:digits桁で入力してください。',
    'digits_between' => ':attributeは:min桁から:max桁の間で入力してください。',
    'dimensions' => ':attributeの画像サイズが正しくありません。',
    'distinct' => ':attributeに重複した値があります。',
    'distinct_if' => ':otherが:valueの場合、:attributeに重複した値があります。',
    'doesnt_end_with' => ':attributeは:valuesで終わらない値にしてください。',
    'doesnt_start_with' => ':attributeは:valuesで始まらない値にしてください。',
    'email' => ':attributeは正しいメールアドレス形式で入力してください。',
    'ends_with' => ':attributeは:valuesのいずれかで終わる値にしてください。',
    'enum' => '選択された:attributeは正しくありません。',
    'exists' => '選択された:attributeは正しくありません。',
    'extensions' => ':attributeは:valuesのいずれかの拡張子である必要があります。',
    'file' => ':attributeはファイルで入力してください。',
    'filled' => ':attributeは必須項目です。',
    'gt' => [
        'array' => ':attributeは:value個より多く選択してください。',
        'file' => ':attributeは:valueKBより大きいファイルを選択してください。',
        'numeric' => ':attributeは:valueより大きい値にしてください。',
        'string' => ':attributeは:value文字より多く入力してください。',
    ],
    'gte' => [
        'array' => ':attributeは:value個以上選択してください。',
        'file' => ':attributeは:valueKB以上のファイルを選択してください。',
        'numeric' => ':attributeは:value以上の値にしてください。',
        'string' => ':attributeは:value文字以上入力してください。',
    ],
    'hex_color' => ':attributeは有効な16進数カラーコードで入力してください。',
    'image' => ':attributeは画像ファイルで入力してください。',
    'in' => '選択された:attributeは正しくありません。',
    'in_array' => ':attributeは:otherに含まれていません。',
    'integer' => ':attributeは整数で入力してください。',
    'ip' => ':attributeは正しいIPアドレスで入力してください。',
    'ipv4' => ':attributeは正しいIPv4アドレスで入力してください。',
    'ipv6' => ':attributeは正しいIPv6アドレスで入力してください。',
    'json' => ':attributeは正しいJSON形式で入力してください。',
    'lowercase' => ':attributeは小文字で入力してください。',
    'lt' => [
        'array' => ':attributeは:value個より少なく選択してください。',
        'file' => ':attributeは:valueKBより小さいファイルを選択してください。',
        'numeric' => ':attributeは:valueより小さい値にしてください。',
        'string' => ':attributeは:value文字より少なく入力してください。',
    ],
    'lte' => [
        'array' => ':attributeは:value個以下選択してください。',
        'file' => ':attributeは:valueKB以下のファイルを選択してください。',
        'numeric' => ':attributeは:value以下の値にしてください。',
        'string' => ':attributeは:value文字以下入力してください。',
    ],
    'mac_address' => ':attributeは正しいMACアドレスで入力してください。',
    'max' => [
        'array' => ':attributeは:max個以下で選択してください。',
        'file' => ':attributeは:maxKB以下のファイルを選択してください。',
        'numeric' => ':attributeは:max以下の値にしてください。',
        'string' => ':attributeは:max文字以下で入力してください。',
    ],
    'max_digits' => ':attributeは:max桁以下で入力してください。',
    'mimes' => ':attributeは:valuesのファイル形式で入力してください。',
    'mimetypes' => ':attributeは:valuesのファイル形式で入力してください。',
    'min' => [
        'array' => ':attributeは:min個以上で選択してください。',
        'file' => ':attributeは:minKB以上のファイルを選択してください。',
        'numeric' => ':attributeは:min以上の値にしてください。',
        'string' => ':attributeは:min文字以上で入力してください。',
    ],
    'min_digits' => ':attributeは:min桁以上で入力してください。',
    'missing' => ':attributeは存在してはいけません。',
    'missing_if' => ':otherが:valueの場合、:attributeは存在してはいけません。',
    'missing_unless' => ':otherが:valueでない場合、:attributeは存在してはいけません。',
    'missing_with' => ':valuesが存在する場合、:attributeは存在してはいけません。',
    'missing_with_all' => ':valuesがすべて存在する場合、:attributeは存在してはいけません。',
    'multiple_of' => ':attributeは:valueの倍数で入力してください。',
    'not_in' => '選択された:attributeは正しくありません。',
    'not_regex' => ':attributeの形式が正しくありません。',
    'numeric' => ':attributeは数値で入力してください。',
    'password' => [
        'letters' => ':attributeには少なくとも1つの文字が含まれている必要があります。',
        'mixed' => ':attributeには少なくとも1つの大文字と1つの小文字が含まれている必要があります。',
        'numbers' => ':attributeには少なくとも1つの数字が含まれている必要があります。',
        'symbols' => ':attributeには少なくとも1つの記号が含まれている必要があります。',
        'uncompromised' => '指定された:attributeがデータ漏洩に含まれています。別の:attributeを選択してください。',
    ],
    'present' => ':attributeは存在する必要があります。',
    'present_if' => ':otherが:valueの場合、:attributeは存在する必要があります。',
    'present_unless' => ':otherが:valueでない場合、:attributeは存在する必要があります。',
    'present_with' => ':valuesが存在する場合、:attributeは存在する必要があります。',
    'present_with_all' => ':valuesがすべて存在する場合、:attributeは存在する必要があります。',
    'prohibited' => ':attributeは禁止されています。',
    'prohibited_if' => ':otherが:valueの場合、:attributeは禁止されています。',
    'prohibited_unless' => ':otherが:valuesに含まれていない場合、:attributeは禁止されています。',
    'prohibits' => ':attributeが存在する場合、:otherは禁止されています。',
    'regex' => ':attributeの形式が正しくありません。',
    'required' => ':attributeは必須項目です。',
    'required_array_keys' => ':attributeには:valuesのキーが含まれている必要があります。',
    'required_if' => ':otherが:valueの場合、:attributeは必須項目です。',
    'required_if_accepted' => ':otherが承認された場合、:attributeは必須項目です。',
    'required_unless' => ':otherが:valuesでない場合、:attributeは必須項目です。',
    'required_with' => ':valuesが存在する場合、:attributeは必須項目です。',
    'required_with_all' => ':valuesがすべて存在する場合、:attributeは必須項目です。',
    'required_without' => ':valuesが存在しない場合、:attributeは必須項目です。',
    'required_without_all' => ':valuesがすべて存在しない場合、:attributeは必須項目です。',
    'same' => ':attributeと:otherが一致しません。',
    'size' => [
        'array' => ':attributeは:size個で選択してください。',
        'file' => ':attributeは:sizeKBのファイルを選択してください。',
        'numeric' => ':attributeは:sizeで入力してください。',
        'string' => ':attributeは:size文字で入力してください。',
    ],
    'starts_with' => ':attributeは:valuesのいずれかで始まる値にしてください。',
    'string' => ':attributeは文字列で入力してください。',
    'timezone' => ':attributeは正しいタイムゾーンで入力してください。',
    'unique' => ':attributeは既に使用されています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'uppercase' => ':attributeは大文字で入力してください。',
    'url' => ':attributeは正しいURL形式で入力してください。',
    'ulid' => ':attributeは正しいULIDで入力してください。',
    'uuid' => ':attributeは正しいUUIDで入力してください。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード確認',
        'current_password' => '現在のパスワード',
        'title' => 'タイトル',
        'content' => '内容',
        'subject' => '件名',
        'message' => 'メッセージ',
        'category_id' => 'カテゴリ',
        'tags' => 'タグ',
        'priority' => '優先度',
        'difficulty' => '難易度',
        'is_active' => '公開状態',
        'question' => '質問',
        'answer' => '回答',
        'sender_email' => '送信者メールアドレス',
        'customer_id' => '顧客ID',
        'prefecture' => '都道府県',
        'user_attribute' => 'ユーザー属性',
        'summary' => '要約',
        'response' => '回答',
        'assigned_user_id' => '担当者',
        'response_deadline' => '回答期限',
        'attachments' => '添付ファイル',
        'department' => '部署',
        'specialties' => '専門分野',
        'role' => '権限',
        'is_active' => 'アクティブ状態',
        'last_login_at' => '最終ログイン日時',
        'email_verified_at' => 'メール認証日時',
        'remember_token' => 'ログイン記憶トークン',
        'created_at' => '作成日時',
        'updated_at' => '更新日時',
        'received_at' => '受信日時',
        'first_response_at' => '初回回答日時',
        'completed_at' => '完了日時',
        'status' => 'ステータス',
        'count' => '閲覧回数',
        'search_keywords' => '検索キーワード',
        'linked_faq_ids' => '関連FAQ',
        'created_user_id' => '作成者',
        'updated_by' => '更新者',
        'user_id' => 'ユーザーID',
        'ip_address' => 'IPアドレス',
        'user_agent' => 'ユーザーエージェント',
        'viewed_at' => '閲覧日時',
        'action' => 'アクション',
        'field_name' => 'フィールド名',
        'old_value' => '変更前の値',
        'new_value' => '変更後の値',
        'comment' => 'コメント',
        'type' => 'タイプ',
        'notifiable_type' => '通知対象タイプ',
        'notifiable_id' => '通知対象ID',
        'data' => 'データ',
        'read_at' => '既読日時',
        'key' => 'キー',
        'value' => '値',
        'description' => '説明',
        'is_public' => '公開設定',
        'filename' => 'ファイル名',
        'original_name' => '元のファイル名',
        'mime_type' => 'MIMEタイプ',
        'size' => 'サイズ',
        'path' => 'パス',
        'uploaded_by' => 'アップロード者',
        'slug' => 'スラッグ',
        'color' => '色',
        'relevance' => '関連度',
        'linked_by' => '紐付け実行者',
        'inquiry_id' => '問い合わせID',
        'faq_id' => 'FAQ ID',
        'tag_id' => 'タグID',
        'attachable_type' => '関連タイプ',
        'attachable_id' => '関連ID',
    ],

];
