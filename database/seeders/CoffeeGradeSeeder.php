<?php

namespace Database\Seeders;

use App\Models\CoffeeGrade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoffeeGradeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $grades = [
            'AA' => 'Largest Arabica bean size, screen 17-18.',
            'AB' => 'Medium Arabica bean size, screen 15-16.',
            'PB' => 'Peaberry — a single rounded bean per cherry.',
            'C' => 'Small Arabica bean size, screen below 15.',
            'E' => 'Elephant bean — oversized, irregular bean.',
            'TT' => 'Lighter beans separated from AA/AB by density.',
            'T' => 'Smallest grade — beans and fragments, triage.',
            'Screen 18' => 'Robusta bean size, screen 18.',
            'Screen 15' => 'Robusta bean size, screen 15.',
            'FAQ' => 'Fair Average Quality — standard Robusta export grade.',
            'Specialty' => 'Cupping score of 80 or above.',
            'Premium' => 'High-quality lot just below specialty threshold.',
            'Commercial' => 'Standard trade-grade lot.',
        ];

        foreach (array_values(array_keys($grades)) as $index => $grade) {
            CoffeeGrade::query()->updateOrCreate(
                ['slug' => Str::slug($grade)],
                [
                    'name' => $grade,
                    'description' => $grades[$grade],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
