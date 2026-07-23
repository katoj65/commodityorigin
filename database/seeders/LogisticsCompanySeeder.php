<?php

namespace Database\Seeders;

use App\Models\LogisticsCompany;
use Illuminate\Database\Seeder;

class LogisticsCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            ['name' => 'Maersk East Africa', 'coverage_area' => 'East Africa · Global', 'contact_email' => 'eastafrica@maersk.com', 'contact_phone' => '+256 414 220 011', 'rating' => 4.6],
            ['name' => 'Kuehne+Nagel Uganda', 'coverage_area' => 'Uganda · EU · North America', 'contact_email' => 'kampala@kuehne-nagel.com', 'contact_phone' => '+256 312 264 500', 'rating' => 4.4],
            ['name' => 'DHL Global Forwarding', 'coverage_area' => 'Global', 'contact_email' => 'forwarding.ug@dhl.com', 'contact_phone' => '+256 417 112 200', 'rating' => 4.3],
            ['name' => 'Bolloré Logistics', 'coverage_area' => 'East & Central Africa', 'contact_email' => 'kampala@bollore.com', 'contact_phone' => '+256 414 344 015', 'rating' => 4.1],
            ['name' => 'Freight In Time (Fitrucks)', 'coverage_area' => 'East Africa Regional', 'contact_email' => 'info@fitrucks.com', 'contact_phone' => '+256 776 100 200', 'rating' => 3.9],
        ];

        foreach ($companies as $company) {
            LogisticsCompany::query()->updateOrCreate(['name' => $company['name']], $company);
        }
    }
}
