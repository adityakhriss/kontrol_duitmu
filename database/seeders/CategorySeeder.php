<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Gaji', 'type' => 'income', 'color' => 'emerald'],
            ['name' => 'Bonus', 'type' => 'income', 'color' => 'teal'],
            ['name' => 'Freelance', 'type' => 'income', 'color' => 'sky'],
            ['name' => 'Makan', 'type' => 'expense', 'color' => 'amber'],
            ['name' => 'Transport', 'type' => 'expense', 'color' => 'orange'],
            ['name' => 'Kebutuhan lainnya', 'type' => 'expense', 'color' => 'slate'],
            ['name' => 'Tagihan', 'type' => 'expense', 'color' => 'rose'],
            ['name' => 'Hiburan', 'type' => 'expense', 'color' => 'pink'],
            ['name' => 'Kesehatan', 'type' => 'expense', 'color' => 'red'],
            ['name' => 'Pendidikan', 'type' => 'expense', 'color' => 'indigo'],
            ['name' => 'Belanja', 'type' => 'expense', 'color' => 'violet'],
            ['name' => 'Cicilan', 'type' => 'expense', 'color' => 'yellow'],
            ['name' => 'Investasi', 'type' => 'expense', 'color' => 'emerald'],
            ['name' => 'Lain-lain', 'type' => 'expense', 'color' => 'zinc'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                [
                    'user_id' => null,
                    'type' => $category['type'],
                    'slug' => Str::slug($category['name']),
                ],
                [
                    'name' => $category['name'],
                    'is_default' => true,
                    'color' => $category['color'],
                ],
            );
        }
    }
}
