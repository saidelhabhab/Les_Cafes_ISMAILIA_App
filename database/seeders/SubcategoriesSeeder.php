<?php

namespace Database\Seeders;

use App\Models\Subcategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubcategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $subcategories = [
            ['name' => 'Android Phones', 'category_id' => 1],
            ['name' => 'iPhones', 'category_id' => 1],
            ['name' => 'Gaming Laptops', 'category_id' => 2],
            ['name' => 'Ultrabooks', 'category_id' => 2],
            ['name' => 'Over-Ear Headphones', 'category_id' => 3],
            ['name' => 'In-Ear Headphones', 'category_id' => 3],
            ['name' => 'DSLR Cameras', 'category_id' => 4],
            ['name' => 'Mirrorless Cameras', 'category_id' => 4],
            ['name' => 'Handheld Consoles', 'category_id' => 5],
            ['name' => 'Home Consoles', 'category_id' => 5],
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create($subcategory);
        }
    }
}
