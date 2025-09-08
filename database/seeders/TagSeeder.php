<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            [
                'name' => 'ログイン',
                'slug' => 'login',
                'color' => '#EF4444',
                'is_active' => true,
            ],
            [
                'name' => 'パスワード',
                'slug' => 'password',
                'color' => '#F97316',
                'is_active' => true,
            ],
            [
                'name' => 'データ',
                'slug' => 'data',
                'color' => '#84CC16',
                'is_active' => true,
            ],
            [
                'name' => 'エラー',
                'slug' => 'error',
                'color' => '#DC2626',
                'is_active' => true,
            ],
            [
                'name' => '操作方法',
                'slug' => 'operation',
                'color' => '#2563EB',
                'is_active' => true,
            ],
            [
                'name' => '設定',
                'slug' => 'settings',
                'color' => '#7C3AED',
                'is_active' => true,
            ],
            [
                'name' => 'メール',
                'slug' => 'email',
                'color' => '#0891B2',
                'is_active' => true,
            ],
            [
                'name' => '印刷',
                'slug' => 'print',
                'color' => '#059669',
                'is_active' => true,
            ],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
