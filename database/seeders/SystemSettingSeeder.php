<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'app_name',
                'value' => 'ヘルプデスクシステム',
                'type' => 'string',
                'description' => 'アプリケーション名',
                'is_public' => true,
            ],
            [
                'key' => 'default_response_time',
                'value' => '24',
                'type' => 'integer',
                'description' => 'デフォルト回答時間（時間）',
                'is_public' => false,
            ],
            [
                'key' => 'max_file_size',
                'value' => '10485760',
                'type' => 'integer',
                'description' => '最大ファイルサイズ（バイト）',
                'is_public' => false,
            ],
            [
                'key' => 'allowed_file_types',
                'value' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx',
                'type' => 'string',
                'description' => '許可ファイル形式',
                'is_public' => false,
            ],
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'メンテナンスモード',
                'is_public' => true,
            ],
            [
                'key' => 'contact_email',
                'value' => 'helpdesk@example.com',
                'type' => 'string',
                'description' => '問い合わせ先メールアドレス',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::create($setting);
        }
    }
}
