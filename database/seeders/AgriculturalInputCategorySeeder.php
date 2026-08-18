<?php

namespace Database\Seeders;

use App\Models\AgriculturalInputCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AgriculturalInputCategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the agricultural input store's two categories.
     */
    public function run(): void
    {
        $categories = [
            'Medicine' => 'Fungicides, insecticides, and other crop and livestock treatments.',
            'Fertilizer' => 'Soil and foliar nutrients for improving yield and plant health.',
        ];

        $index = 0;
        foreach ($categories as $name => $description) {
            $index++;

            AgriculturalInputCategory::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $description,
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
