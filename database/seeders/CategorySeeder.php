<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Aシステム',
                'slug' => 'system-a',
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'name' => 'Bシステム',
                'slug' => 'system-b',
                'color' => '#10B981',
                'is_active' => true,
            ],
            [
                'name' => 'システム全体',
                'slug' => 'system-general',
                'color' => '#8B5CF6',
                'is_active' => true,
            ],
            [
                'name' => '制度',
                'slug' => 'policy',
                'color' => '#F59E0B',
                'is_active' => true,
            ],
            [
                'name' => 'その他',
                'slug' => 'others',
                'color' => '#6B7280',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
