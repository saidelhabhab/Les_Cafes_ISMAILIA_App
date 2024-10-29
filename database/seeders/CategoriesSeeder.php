<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $categories = [
            ['name' => 'Smartphones'],
            ['name' => 'Laptops'],
            ['name' => 'Headphones'],
            ['name' => 'Cameras'],
            ['name' => 'Gaming Consoles'],
            ['name' => 'Speakers'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
