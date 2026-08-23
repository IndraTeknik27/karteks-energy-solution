<?php

namespace Database\Seeders\Catalog;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = Config::get('karteks.service_types', []);
        $servicesCategory = Category::where('slug', 'services')->first();

        foreach ($services as $data) {
            Service::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $servicesCategory?->id,
                    'name' => $data['name'],
                    'short_description' => null,
                    'pricing_type' => $data['pricing_type'],
                    'is_active' => true,
                    'is_featured' => in_array($data['slug'], ['konsultasi-ev', 'solar-installation', 'ev-conversion']),
                    'sort' => array_search($data, $services),
                ]
            );
        }

        $this->command->info('Services seeded: '.count($services));
    }
}