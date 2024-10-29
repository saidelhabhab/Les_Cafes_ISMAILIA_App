<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'Apple iPhone 14',
                'description' => 'Latest model with A15 Bionic chip and improved camera.',
                'price' => 999.99,
                'buy_price' => 900.00,
                'quantity' => 50,
                'barcode' => '0123456789012',
                'category_id' => 2,
                'subcategory_id' => 2,
            ],
            [
                'name' => 'Samsung Galaxy S23',
                'description' => 'High-performance Android phone with AMOLED display.',
                'price' => 899.99,
                'buy_price' => 850.00,
                'quantity' => 30,
                'barcode' => '0123456789013',
                'category_id' => 1,
                'subcategory_id' => 1,
            ],
            [
                'name' => 'Sony WH-1000XM5 Headphones',
                'description' => 'Noise-cancelling wireless headphones with premium sound.',
                'price' => 349.99,
                'buy_price' => 300.00,
                'quantity' => 100,
                'barcode' => '0123456789014',
                'category_id' => 3,
                'subcategory_id' => 3,
            ],
            [
                'name' => 'Dell XPS 13 Laptop',
                'description' => 'Ultra-portable laptop with Intel Core i7 and 16GB RAM.',
                'price' => 1299.99,
                'buy_price' => 1200.00,
                'quantity' => 20,
                'barcode' => '0123456789015',
                'category_id' => 4,
                'subcategory_id' => 4,
            ],
            [
                'name' => 'Apple Watch Series 8',
                'description' => 'Smartwatch with fitness tracking, GPS, and heart monitoring.',
                'price' => 499.99,
                'buy_price' => 450.00,
                'quantity' => 75,
                'barcode' => '0123456789016',
                'category_id' => 2,
                'subcategory_id' => 2,
            ],
            [
                'name' => 'GoPro HERO 11',
                'description' => 'Action camera with 5K video and waterproof design.',
                'price' => 399.99,
                'buy_price' => 370.00,
                'quantity' => 40,
                'barcode' => '0123456789017',
                'category_id' => 5,
                'subcategory_id' => 5,
            ],
            [
                'name' => 'Microsoft Surface Pro 9',
                'description' => '2-in-1 laptop and tablet with 12.3-inch display.',
                'price' => 999.99,
                'buy_price' => 950.00,
                'quantity' => 25,
                'barcode' => '0123456789018',
                'category_id' => 4,
                'subcategory_id' => 4,
            ],
            [
                'name' => 'Canon EOS R6 Camera',
                'description' => 'Mirrorless camera with 20.1MP sensor and 4K video.',
                'price' => 2499.99,
                'buy_price' => 2300.00,
                'quantity' => 15,
                'barcode' => '0123456789019',
                'category_id' => 6,
                'subcategory_id' => 6,
            ],
            [
                'name' => 'Nintendo Switch OLED',
                'description' => 'Gaming console with improved OLED display.',
                'price' => 349.99,
                'buy_price' => 320.00,
                'quantity' => 60,
                'barcode' => '0123456789020',
                'category_id' => 1,
                'subcategory_id' => 1,
            ],
            [
                'name' => 'Bose SoundLink Speaker',
                'description' => 'Portable Bluetooth speaker with deep bass.',
                'price' => 199.99,
                'buy_price' => 180.00,
                'quantity' => 80,
                'barcode' => '0123456789021',
                'category_id' => 3,
                'subcategory_id' => 3,
            ],
        ];
    
        foreach ($products as $product) {
            Product::create($product);
        }
    }
    

}
