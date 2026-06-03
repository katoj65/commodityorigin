<?php

namespace Database\Seeders;

use App\Models\CropGradeMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CropGradeMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $grades = [
            'Grade 1',
            'Grade 2',
            'Grade 3',
            'Grade 4',
            'Grade 5',
            'AA',
            'AB',
            'Peaberry',
            'C Grade',
            'Specialty',
            'TT&T'
        ];

        foreach ($grades as $index => $grade) {
            CropGradeMetadata::query()->updateOrCreate(
                ['slug' => Str::slug($grade)],
                [
                    'name' => $grade,
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
