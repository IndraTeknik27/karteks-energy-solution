<?php

namespace Database\Seeders\Catalog;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = Config::get('karteks.default_categories', []);

        foreach ($categories as $data) {
            Category::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'icon' => $data['icon'] ?? null,
                    'is_active' => true,
                    'is_featured' => in_array($data['slug'], ['ev-car', 'ev-bike', 'solar-energy', 'custom-battery']),
                    'sort' => array_search($data, $categories),
                ]
            );
        }

        $this->command->info('Categories seeded: '.count($categories));
    }
}