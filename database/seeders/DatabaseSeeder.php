<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure storage directories exist
        if (!File::exists(storage_path('app/public/products'))) {
            File::makeDirectory(storage_path('app/public/products'), 0755, true);
        }

        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@vibe.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // Create categories
        $hoodies = Category::create([
            'name' => 'Hoodies',
            'slug' => 'hoodies',
            'description' => 'Premium quality hoodies for all seasons',
            'image' => 'products/hoodie-black.png',
            'is_active' => true,
        ]);

        $tshirts = Category::create([
            'name' => 'T-Shirts',
            'slug' => 't-shirts',
            'description' => 'Comfortable and stylish t-shirts',
            'image' => 'products/tshirt-graphic.png',
            'is_active' => true,
        ]);

        $jackets = Category::create([
            'name' => 'Jackets',
            'slug' => 'jackets',
            'description' => 'Premium urban jackets and outerwear',
            'is_active' => true,
        ]);

        // ...

        // Product 5: Jacket (was Pants)
        $product5 = Product::create([
            'category_id' => $jackets->id,
            'name' => 'Urban Varsity Jacket',
            'slug' => 'urban-varsity-jacket',
            'description' => 'Classic varsity jacket with a modern twist. Premium wool blend body and faux leather sleeves.',
            'price' => 8500,
            'is_active' => true,
            'is_featured' => false,
        ]);

        // No image for jackets yet, or reuse hoodie as placeholder
        ProductImage::create([
            'product_id' => $product5->id,
            'path' => 'products/hoodie-black.png', // Placeholder
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        foreach (['S', 'M', 'L', 'XL'] as $size) {
            ProductVariant::create([
                'product_id' => $product5->id,
                'size' => $size,
                'color' => 'Black',
                'sku' => "UVJ-{$size}-BK",
                'stock_quantity' => rand(5, 15),
                'price_adjustment' => 0,
            ]);
        }
    }
}
