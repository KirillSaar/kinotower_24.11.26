<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Фильмы' => ['Боевик', 'Комедия', 'Драма', 'Ужасы', 'Фантастика', 'Мелодрама', 'Триллер'],
            'Сериалы' => ['Детектив', 'Фэнтези', 'Криминал'],
            'Мультфильмы' => ['Детские', 'Аниме'],
        ];

        foreach ($tree as $parentName => $children) {
            $parent = Category::firstOrCreate(['name' => $parentName, 'parent_id' => null]);

            foreach ($children as $childName) {
                Category::firstOrCreate(['name' => $childName, 'parent_id' => $parent->id]);
            }
        }
    }
}
