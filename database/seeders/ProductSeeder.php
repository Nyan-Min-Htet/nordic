<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Wireless Mouse', 'price' => 25.99, 'stock' => 50],
            ['name' => 'Mechanical Keyboard', 'price' => 79.99, 'stock' => 30],
            ['name' => 'USB-C Hub', 'price' => 45.00, 'stock' => 40],
            ['name' => 'Monitor Stand', 'price' => 34.99, 'stock' => 25],
            ['name' => 'Webcam HD', 'price' => 59.99, 'stock' => 30],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
