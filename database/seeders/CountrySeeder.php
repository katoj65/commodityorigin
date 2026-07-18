<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with every world country.
     */
    public function run(): void
    {
        foreach ($this->countries() as $country) {
            Country::query()->updateOrCreate(
                ['iso2' => $country['iso2']],
                $country,
            );
        }

        foreach ($this->coffeeProducers() as $iso2 => $bags) {
            Country::query()->where('iso2', $iso2)->update([
                'is_coffee_producer' => true,
                'coffee_production_bags' => $bags,
            ]);
        }
    }

    /**
     * Annual coffee production in 60kg bags, keyed by ISO2 code. Approximate
     * figures for reference purposes, ordered roughly by output.
     *
     * @return array<string, int>
     */
    private function coffeeProducers(): array
    {
        return [
            'BR' => 63_000_000, // Brazil
            'VN' => 27_000_000, // Vietnam
            'CO' => 11_500_000, // Colombia
            'ID' => 10_000_000, // Indonesia
            'ET' => 7_500_000,  // Ethiopia
            'HN' => 5_900_000,  // Honduras
            'IN' => 5_800_000,  // India
            'UG' => 5_700_000,  // Uganda
            'PE' => 4_200_000,  // Peru
            'MX' => 4_000_000,  // Mexico
            'GT' => 3_400_000,  // Guatemala
            'NI' => 2_800_000,  // Nicaragua
            'CI' => 1_800_000,  // Ivory Coast
            'CN' => 1_900_000,  // China
            'CR' => 1_500_000,  // Costa Rica
            'TZ' => 1_100_000,  // Tanzania
            'PG' => 1_000_000,  // Papua New Guinea
            'KE' => 800_000,    // Kenya
            'EC' => 800_000,    // Ecuador
            'LA' => 800_000,    // Laos
            'VE' => 650_000,    // Venezuela
            'SV' => 600_000,    // El Salvador
            'CM' => 500_000,    // Cameroon
            'TH' => 500_000,    // Thailand
            'PH' => 500_000,    // Philippines
            'CD' => 450_000,    // Congo (DR)
            'MG' => 450_000,    // Madagascar
            'DO' => 350_000,    // Dominican Republic
            'RW' => 350_000,    // Rwanda
            'TG' => 180_000,    // Togo
            'BI' => 200_000,    // Burundi
            'YE' => 120_000,    // Yemen
            'BO' => 120_000,    // Bolivia
            'TL' => 100_000,    // Timor-Leste
            'PA' => 100_000,    // Panama
            'SL' => 50_000,     // Sierra Leone
            'AO' => 50_000,     // Angola
            'NG' => 50_000,     // Nigeria
            'GH' => 15_000,     // Ghana
            'MW' => 20_000,     // Malawi
            'ZM' => 10_000,     // Zambia
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function countries(): array
    {
        return [
            // ── Africa ──────────────────────────────────────────────────
            ['name' => 'Algeria', 'iso2' => 'DZ', 'iso3' => 'DZA', 'phone_code' => '+213', 'region' => 'Africa', 'subregion' => 'Northern Africa', 'currency_code' => 'DZD', 'currency_name' => 'Algerian Dinar'],
            ['name' => 'Angola', 'iso2' => 'AO', 'iso3' => 'AGO', 'phone_code' => '+244', 'region' => 'Africa', 'subregion' => 'Middle Africa', 'currency_code' => 'AOA', 'currency_name' => 'Angolan Kwanza'],
            ['name' => 'Benin', 'iso2' => 'BJ', 'iso3' => 'BEN', 'phone_code' => '+229', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'XOF', 'currency_name' => 'West African CFA Franc'],
            ['name' => 'Botswana', 'iso2' => 'BW', 'iso3' => 'BWA', 'phone_code' => '+267', 'region' => 'Africa', 'subregion' => 'Southern Africa', 'currency_code' => 'BWP', 'currency_name' => 'Botswana Pula'],
            ['name' => 'Burkina Faso', 'iso2' => 'BF', 'iso3' => 'BFA', 'phone_code' => '+226', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'XOF', 'currency_name' => 'West African CFA Franc'],
            ['name' => 'Burundi', 'iso2' => 'BI', 'iso3' => 'BDI', 'phone_code' => '+257', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'BIF', 'currency_name' => 'Burundian Franc'],
            ['name' => 'Cabo Verde', 'iso2' => 'CV', 'iso3' => 'CPV', 'phone_code' => '+238', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'CVE', 'currency_name' => 'Cape Verdean Escudo'],
            ['name' => 'Cameroon', 'iso2' => 'CM', 'iso3' => 'CMR', 'phone_code' => '+237', 'region' => 'Africa', 'subregion' => 'Middle Africa', 'currency_code' => 'XAF', 'currency_name' => 'Central African CFA Franc'],
            ['name' => 'Central African Republic', 'iso2' => 'CF', 'iso3' => 'CAF', 'phone_code' => '+236', 'region' => 'Africa', 'subregion' => 'Middle Africa', 'currency_code' => 'XAF', 'currency_name' => 'Central African CFA Franc'],
            ['name' => 'Chad', 'iso2' => 'TD', 'iso3' => 'TCD', 'phone_code' => '+235', 'region' => 'Africa', 'subregion' => 'Middle Africa', 'currency_code' => 'XAF', 'currency_name' => 'Central African CFA Franc'],
            ['name' => 'Comoros', 'iso2' => 'KM', 'iso3' => 'COM', 'phone_code' => '+269', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'KMF', 'currency_name' => 'Comorian Franc'],
            ['name' => 'Congo (Republic of the)', 'iso2' => 'CG', 'iso3' => 'COG', 'phone_code' => '+242', 'region' => 'Africa', 'subregion' => 'Middle Africa', 'currency_code' => 'XAF', 'currency_name' => 'Central African CFA Franc'],
            ['name' => 'Congo (DR)', 'iso2' => 'CD', 'iso3' => 'COD', 'phone_code' => '+243', 'region' => 'Africa', 'subregion' => 'Middle Africa', 'currency_code' => 'CDF', 'currency_name' => 'Congolese Franc'],
            ['name' => 'Djibouti', 'iso2' => 'DJ', 'iso3' => 'DJI', 'phone_code' => '+253', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'DJF', 'currency_name' => 'Djiboutian Franc'],
            ['name' => 'Egypt', 'iso2' => 'EG', 'iso3' => 'EGY', 'phone_code' => '+20', 'region' => 'Africa', 'subregion' => 'Northern Africa', 'currency_code' => 'EGP', 'currency_name' => 'Egyptian Pound'],
            ['name' => 'Equatorial Guinea', 'iso2' => 'GQ', 'iso3' => 'GNQ', 'phone_code' => '+240', 'region' => 'Africa', 'subregion' => 'Middle Africa', 'currency_code' => 'XAF', 'currency_name' => 'Central African CFA Franc'],
            ['name' => 'Eritrea', 'iso2' => 'ER', 'iso3' => 'ERI', 'phone_code' => '+291', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'ERN', 'currency_name' => 'Eritrean Nakfa'],
            ['name' => 'Eswatini', 'iso2' => 'SZ', 'iso3' => 'SWZ', 'phone_code' => '+268', 'region' => 'Africa', 'subregion' => 'Southern Africa', 'currency_code' => 'SZL', 'currency_name' => 'Swazi Lilangeni'],
            ['name' => 'Ethiopia', 'iso2' => 'ET', 'iso3' => 'ETH', 'phone_code' => '+251', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'ETB', 'currency_name' => 'Ethiopian Birr'],
            ['name' => 'Gabon', 'iso2' => 'GA', 'iso3' => 'GAB', 'phone_code' => '+241', 'region' => 'Africa', 'subregion' => 'Middle Africa', 'currency_code' => 'XAF', 'currency_name' => 'Central African CFA Franc'],
            ['name' => 'Gambia', 'iso2' => 'GM', 'iso3' => 'GMB', 'phone_code' => '+220', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'GMD', 'currency_name' => 'Gambian Dalasi'],
            ['name' => 'Ghana', 'iso2' => 'GH', 'iso3' => 'GHA', 'phone_code' => '+233', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'GHS', 'currency_name' => 'Ghanaian Cedi'],
            ['name' => 'Guinea', 'iso2' => 'GN', 'iso3' => 'GIN', 'phone_code' => '+224', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'GNF', 'currency_name' => 'Guinean Franc'],
            ['name' => 'Guinea-Bissau', 'iso2' => 'GW', 'iso3' => 'GNB', 'phone_code' => '+245', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'XOF', 'currency_name' => 'West African CFA Franc'],
            ['name' => 'Ivory Coast', 'iso2' => 'CI', 'iso3' => 'CIV', 'phone_code' => '+225', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'XOF', 'currency_name' => 'West African CFA Franc'],
            ['name' => 'Kenya', 'iso2' => 'KE', 'iso3' => 'KEN', 'phone_code' => '+254', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'KES', 'currency_name' => 'Kenyan Shilling'],
            ['name' => 'Lesotho', 'iso2' => 'LS', 'iso3' => 'LSO', 'phone_code' => '+266', 'region' => 'Africa', 'subregion' => 'Southern Africa', 'currency_code' => 'LSL', 'currency_name' => 'Lesotho Loti'],
            ['name' => 'Liberia', 'iso2' => 'LR', 'iso3' => 'LBR', 'phone_code' => '+231', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'LRD', 'currency_name' => 'Liberian Dollar'],
            ['name' => 'Libya', 'iso2' => 'LY', 'iso3' => 'LBY', 'phone_code' => '+218', 'region' => 'Africa', 'subregion' => 'Northern Africa', 'currency_code' => 'LYD', 'currency_name' => 'Libyan Dinar'],
            ['name' => 'Madagascar', 'iso2' => 'MG', 'iso3' => 'MDG', 'phone_code' => '+261', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'MGA', 'currency_name' => 'Malagasy Ariary'],
            ['name' => 'Malawi', 'iso2' => 'MW', 'iso3' => 'MWI', 'phone_code' => '+265', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'MWK', 'currency_name' => 'Malawian Kwacha'],
            ['name' => 'Mali', 'iso2' => 'ML', 'iso3' => 'MLI', 'phone_code' => '+223', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'XOF', 'currency_name' => 'West African CFA Franc'],
            ['name' => 'Mauritania', 'iso2' => 'MR', 'iso3' => 'MRT', 'phone_code' => '+222', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'MRU', 'currency_name' => 'Mauritanian Ouguiya'],
            ['name' => 'Mauritius', 'iso2' => 'MU', 'iso3' => 'MUS', 'phone_code' => '+230', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'MUR', 'currency_name' => 'Mauritian Rupee'],
            ['name' => 'Morocco', 'iso2' => 'MA', 'iso3' => 'MAR', 'phone_code' => '+212', 'region' => 'Africa', 'subregion' => 'Northern Africa', 'currency_code' => 'MAD', 'currency_name' => 'Moroccan Dirham'],
            ['name' => 'Mozambique', 'iso2' => 'MZ', 'iso3' => 'MOZ', 'phone_code' => '+258', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'MZN', 'currency_name' => 'Mozambican Metical'],
            ['name' => 'Namibia', 'iso2' => 'NA', 'iso3' => 'NAM', 'phone_code' => '+264', 'region' => 'Africa', 'subregion' => 'Southern Africa', 'currency_code' => 'NAD', 'currency_name' => 'Namibian Dollar'],
            ['name' => 'Niger', 'iso2' => 'NE', 'iso3' => 'NER', 'phone_code' => '+227', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'XOF', 'currency_name' => 'West African CFA Franc'],
            ['name' => 'Nigeria', 'iso2' => 'NG', 'iso3' => 'NGA', 'phone_code' => '+234', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'NGN', 'currency_name' => 'Nigerian Naira'],
            ['name' => 'Rwanda', 'iso2' => 'RW', 'iso3' => 'RWA', 'phone_code' => '+250', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'RWF', 'currency_name' => 'Rwandan Franc'],
            ['name' => 'Sao Tome and Principe', 'iso2' => 'ST', 'iso3' => 'STP', 'phone_code' => '+239', 'region' => 'Africa', 'subregion' => 'Middle Africa', 'currency_code' => 'STN', 'currency_name' => 'São Tomé and Príncipe Dobra'],
            ['name' => 'Senegal', 'iso2' => 'SN', 'iso3' => 'SEN', 'phone_code' => '+221', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'XOF', 'currency_name' => 'West African CFA Franc'],
            ['name' => 'Seychelles', 'iso2' => 'SC', 'iso3' => 'SYC', 'phone_code' => '+248', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'SCR', 'currency_name' => 'Seychellois Rupee'],
            ['name' => 'Sierra Leone', 'iso2' => 'SL', 'iso3' => 'SLE', 'phone_code' => '+232', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'SLE', 'currency_name' => 'Sierra Leonean Leone'],
            ['name' => 'Somalia', 'iso2' => 'SO', 'iso3' => 'SOM', 'phone_code' => '+252', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'SOS', 'currency_name' => 'Somali Shilling'],
            ['name' => 'South Africa', 'iso2' => 'ZA', 'iso3' => 'ZAF', 'phone_code' => '+27', 'region' => 'Africa', 'subregion' => 'Southern Africa', 'currency_code' => 'ZAR', 'currency_name' => 'South African Rand'],
            ['name' => 'South Sudan', 'iso2' => 'SS', 'iso3' => 'SSD', 'phone_code' => '+211', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'SSP', 'currency_name' => 'South Sudanese Pound'],
            ['name' => 'Sudan', 'iso2' => 'SD', 'iso3' => 'SDN', 'phone_code' => '+249', 'region' => 'Africa', 'subregion' => 'Northern Africa', 'currency_code' => 'SDG', 'currency_name' => 'Sudanese Pound'],
            ['name' => 'Tanzania', 'iso2' => 'TZ', 'iso3' => 'TZA', 'phone_code' => '+255', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'TZS', 'currency_name' => 'Tanzanian Shilling'],
            ['name' => 'Togo', 'iso2' => 'TG', 'iso3' => 'TGO', 'phone_code' => '+228', 'region' => 'Africa', 'subregion' => 'Western Africa', 'currency_code' => 'XOF', 'currency_name' => 'West African CFA Franc'],
            ['name' => 'Tunisia', 'iso2' => 'TN', 'iso3' => 'TUN', 'phone_code' => '+216', 'region' => 'Africa', 'subregion' => 'Northern Africa', 'currency_code' => 'TND', 'currency_name' => 'Tunisian Dinar'],
            ['name' => 'Uganda', 'iso2' => 'UG', 'iso3' => 'UGA', 'phone_code' => '+256', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'UGX', 'currency_name' => 'Ugandan Shilling'],
            ['name' => 'Zambia', 'iso2' => 'ZM', 'iso3' => 'ZMB', 'phone_code' => '+260', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'ZMW', 'currency_name' => 'Zambian Kwacha'],
            ['name' => 'Zimbabwe', 'iso2' => 'ZW', 'iso3' => 'ZWE', 'phone_code' => '+263', 'region' => 'Africa', 'subregion' => 'Eastern Africa', 'currency_code' => 'ZWL', 'currency_name' => 'Zimbabwean Dollar'],

            // ── Americas ────────────────────────────────────────────────
            ['name' => 'Antigua and Barbuda', 'iso2' => 'AG', 'iso3' => 'ATG', 'phone_code' => '+1268', 'region' => 'Americas', 'subregion' => 'Caribbean', 'currency_code' => 'XCD', 'currency_name' => 'East Caribbean Dollar'],
            ['name' => 'Argentina', 'iso2' => 'AR', 'iso3' => 'ARG', 'phone_code' => '+54', 'region' => 'Americas', 'subregion' => 'South America', 'currency_code' => 'ARS', 'currency_name' => 'Argentine Peso'],
            ['name' => 'Bahamas', 'iso2' => 'BS', 'iso3' => 'BHS', 'phone_code' => '+1242', 'region' => 'Americas', 'subregion' => 'Caribbean', 'currency_code' => 'BSD', 'currency_name' => 'Bahamian Dollar'],
            ['name' => 'Barbados', 'iso2' => 'BB', 'iso3' => 'BRB', 'phone_code' => '+1246', 'region' => 'Americas', 'subregion' => 'Caribbean', 'currency_code' => 'BBD', 'currency_name' => 'Barbadian Dollar'],
            ['name' => 'Belize', 'iso2' => 'BZ', 'iso3' => 'BLZ', 'phone_code' => '+501', 'region' => 'Americas', 'subregion' => 'Central America', 'currency_code' => 'BZD', 'currency_name' => 'Belize Dollar'],
            ['name' => 'Bolivia', 'iso2' => 'BO', 'iso3' => 'BOL', 'phone_code' => '+591', 'region' => 'Americas', 'subregion' => 'South America', 'currency_code' => 'BOB', 'currency_name' => 'Bolivian Boliviano'],
            ['name' => 'Brazil', 'iso2' => 'BR', 'iso3' => 'BRA', 'phone_code' => '+55', 'region' => 'Americas', 'subregion' => 'South America', 'currency_code' => 'BRL', 'currency_name' => 'Brazilian Real'],
            ['name' => 'Canada', 'iso2' => 'CA', 'iso3' => 'CAN', 'phone_code' => '+1', 'region' => 'Americas', 'subregion' => 'Northern America', 'currency_code' => 'CAD', 'currency_name' => 'Canadian Dollar'],
            ['name' => 'Chile', 'iso2' => 'CL', 'iso3' => 'CHL', 'phone_code' => '+56', 'region' => 'Americas', 'subregion' => 'South America', 'currency_code' => 'CLP', 'currency_name' => 'Chilean Peso'],
            ['name' => 'Colombia', 'iso2' => 'CO', 'iso3' => 'COL', 'phone_code' => '+57', 'region' => 'Americas', 'subregion' => 'South America', 'currency_code' => 'COP', 'currency_name' => 'Colombian Peso'],
            ['name' => 'Costa Rica', 'iso2' => 'CR', 'iso3' => 'CRI', 'phone_code' => '+506', 'region' => 'Americas', 'subregion' => 'Central America', 'currency_code' => 'CRC', 'currency_name' => 'Costa Rican Colón'],
            ['name' => 'Cuba', 'iso2' => 'CU', 'iso3' => 'CUB', 'phone_code' => '+53', 'region' => 'Americas', 'subregion' => 'Caribbean', 'currency_code' => 'CUP', 'currency_name' => 'Cuban Peso'],
            ['name' => 'Dominica', 'iso2' => 'DM', 'iso3' => 'DMA', 'phone_code' => '+1767', 'region' => 'Americas', 'subregion' => 'Caribbean', 'currency_code' => 'XCD', 'currency_name' => 'East Caribbean Dollar'],
            ['name' => 'Dominican Republic', 'iso2' => 'DO', 'iso3' => 'DOM', 'phone_code' => '+1809', 'region' => 'Americas', 'subregion' => 'Caribbean', 'currency_code' => 'DOP', 'currency_name' => 'Dominican Peso'],
            ['name' => 'Ecuador', 'iso2' => 'EC', 'iso3' => 'ECU', 'phone_code' => '+593', 'region' => 'Americas', 'subregion' => 'South America', 'currency_code' => 'USD', 'currency_name' => 'US Dollar'],
            ['name' => 'El Salvador', 'iso2' => 'SV', 'iso3' => 'SLV', 'phone_code' => '+503', 'region' => 'Americas', 'subregion' => 'Central America', 'currency_code' => 'USD', 'currency_name' => 'US Dollar'],
            ['name' => 'Grenada', 'iso2' => 'GD', 'iso3' => 'GRD', 'phone_code' => '+1473', 'region' => 'Americas', 'subregion' => 'Caribbean', 'currency_code' => 'XCD', 'currency_name' => 'East Caribbean Dollar'],
            ['name' => 'Guatemala', 'iso2' => 'GT', 'iso3' => 'GTM', 'phone_code' => '+502', 'region' => 'Americas', 'subregion' => 'Central America', 'currency_code' => 'GTQ', 'currency_name' => 'Guatemalan Quetzal'],
            ['name' => 'Guyana', 'iso2' => 'GY', 'iso3' => 'GUY', 'phone_code' => '+592', 'region' => 'Americas', 'subregion' => 'South America', 'currency_code' => 'GYD', 'currency_name' => 'Guyanese Dollar'],
            ['name' => 'Haiti', 'iso2' => 'HT', 'iso3' => 'HTI', 'phone_code' => '+509', 'region' => 'Americas', 'subregion' => 'Caribbean', 'currency_code' => 'HTG', 'currency_name' => 'Haitian Gourde'],
            ['name' => 'Honduras', 'iso2' => 'HN', 'iso3' => 'HND', 'phone_code' => '+504', 'region' => 'Americas', 'subregion' => 'Central America', 'currency_code' => 'HNL', 'currency_name' => 'Honduran Lempira'],
            ['name' => 'Jamaica', 'iso2' => 'JM', 'iso3' => 'JAM', 'phone_code' => '+1876', 'region' => 'Americas', 'subregion' => 'Caribbean', 'currency_code' => 'JMD', 'currency_name' => 'Jamaican Dollar'],
            ['name' => 'Mexico', 'iso2' => 'MX', 'iso3' => 'MEX', 'phone_code' => '+52', 'region' => 'Americas', 'subregion' => 'Central America', 'currency_code' => 'MXN', 'currency_name' => 'Mexican Peso'],
            ['name' => 'Nicaragua', 'iso2' => 'NI', 'iso3' => 'NIC', 'phone_code' => '+505', 'region' => 'Americas', 'subregion' => 'Central America', 'currency_code' => 'NIO', 'currency_name' => 'Nicaraguan Córdoba'],
            ['name' => 'Panama', 'iso2' => 'PA', 'iso3' => 'PAN', 'phone_code' => '+507', 'region' => 'Americas', 'subregion' => 'Central America', 'currency_code' => 'PAB', 'currency_name' => 'Panamanian Balboa'],
            ['name' => 'Paraguay', 'iso2' => 'PY', 'iso3' => 'PRY', 'phone_code' => '+595', 'region' => 'Americas', 'subregion' => 'South America', 'currency_code' => 'PYG', 'currency_name' => 'Paraguayan Guaraní'],
            ['name' => 'Peru', 'iso2' => 'PE', 'iso3' => 'PER', 'phone_code' => '+51', 'region' => 'Americas', 'subregion' => 'South America', 'currency_code' => 'PEN', 'currency_name' => 'Peruvian Sol'],
            ['name' => 'Saint Kitts and Nevis', 'iso2' => 'KN', 'iso3' => 'KNA', 'phone_code' => '+1869', 'region' => 'Americas', 'subregion' => 'Caribbean', 'currency_code' => 'XCD', 'currency_name' => 'East Caribbean Dollar'],
            ['name' => 'Saint Lucia', 'iso2' => 'LC', 'iso3' => 'LCA', 'phone_code' => '+1758', 'region' => 'Americas', 'subregion' => 'Caribbean', 'currency_code' => 'XCD', 'currency_name' => 'East Caribbean Dollar'],
            ['name' => 'Saint Vincent and the Grenadines', 'iso2' => 'VC', 'iso3' => 'VCT', 'phone_code' => '+1784', 'region' => 'Americas', 'subregion' => 'Caribbean', 'currency_code' => 'XCD', 'currency_name' => 'East Caribbean Dollar'],
            ['name' => 'Suriname', 'iso2' => 'SR', 'iso3' => 'SUR', 'phone_code' => '+597', 'region' => 'Americas', 'subregion' => 'South America', 'currency_code' => 'SRD', 'currency_name' => 'Surinamese Dollar'],
            ['name' => 'Trinidad and Tobago', 'iso2' => 'TT', 'iso3' => 'TTO', 'phone_code' => '+1868', 'region' => 'Americas', 'subregion' => 'Caribbean', 'currency_code' => 'TTD', 'currency_name' => 'Trinidad and Tobago Dollar'],
            ['name' => 'United States', 'iso2' => 'US', 'iso3' => 'USA', 'phone_code' => '+1', 'region' => 'Americas', 'subregion' => 'Northern America', 'currency_code' => 'USD', 'currency_name' => 'US Dollar'],
            ['name' => 'Uruguay', 'iso2' => 'UY', 'iso3' => 'URY', 'phone_code' => '+598', 'region' => 'Americas', 'subregion' => 'South America', 'currency_code' => 'UYU', 'currency_name' => 'Uruguayan Peso'],
            ['name' => 'Venezuela', 'iso2' => 'VE', 'iso3' => 'VEN', 'phone_code' => '+58', 'region' => 'Americas', 'subregion' => 'South America', 'currency_code' => 'VES', 'currency_name' => 'Venezuelan Bolívar'],

            // ── Asia ────────────────────────────────────────────────────
            ['name' => 'Afghanistan', 'iso2' => 'AF', 'iso3' => 'AFG', 'phone_code' => '+93', 'region' => 'Asia', 'subregion' => 'Southern Asia', 'currency_code' => 'AFN', 'currency_name' => 'Afghan Afghani'],
            ['name' => 'Armenia', 'iso2' => 'AM', 'iso3' => 'ARM', 'phone_code' => '+374', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'AMD', 'currency_name' => 'Armenian Dram'],
            ['name' => 'Azerbaijan', 'iso2' => 'AZ', 'iso3' => 'AZE', 'phone_code' => '+994', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'AZN', 'currency_name' => 'Azerbaijani Manat'],
            ['name' => 'Bahrain', 'iso2' => 'BH', 'iso3' => 'BHR', 'phone_code' => '+973', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'BHD', 'currency_name' => 'Bahraini Dinar'],
            ['name' => 'Bangladesh', 'iso2' => 'BD', 'iso3' => 'BGD', 'phone_code' => '+880', 'region' => 'Asia', 'subregion' => 'Southern Asia', 'currency_code' => 'BDT', 'currency_name' => 'Bangladeshi Taka'],
            ['name' => 'Bhutan', 'iso2' => 'BT', 'iso3' => 'BTN', 'phone_code' => '+975', 'region' => 'Asia', 'subregion' => 'Southern Asia', 'currency_code' => 'BTN', 'currency_name' => 'Bhutanese Ngultrum'],
            ['name' => 'Brunei', 'iso2' => 'BN', 'iso3' => 'BRN', 'phone_code' => '+673', 'region' => 'Asia', 'subregion' => 'South-eastern Asia', 'currency_code' => 'BND', 'currency_name' => 'Brunei Dollar'],
            ['name' => 'Cambodia', 'iso2' => 'KH', 'iso3' => 'KHM', 'phone_code' => '+855', 'region' => 'Asia', 'subregion' => 'South-eastern Asia', 'currency_code' => 'KHR', 'currency_name' => 'Cambodian Riel'],
            ['name' => 'China', 'iso2' => 'CN', 'iso3' => 'CHN', 'phone_code' => '+86', 'region' => 'Asia', 'subregion' => 'Eastern Asia', 'currency_code' => 'CNY', 'currency_name' => 'Chinese Yuan'],
            ['name' => 'Cyprus', 'iso2' => 'CY', 'iso3' => 'CYP', 'phone_code' => '+357', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Georgia', 'iso2' => 'GE', 'iso3' => 'GEO', 'phone_code' => '+995', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'GEL', 'currency_name' => 'Georgian Lari'],
            ['name' => 'India', 'iso2' => 'IN', 'iso3' => 'IND', 'phone_code' => '+91', 'region' => 'Asia', 'subregion' => 'Southern Asia', 'currency_code' => 'INR', 'currency_name' => 'Indian Rupee'],
            ['name' => 'Indonesia', 'iso2' => 'ID', 'iso3' => 'IDN', 'phone_code' => '+62', 'region' => 'Asia', 'subregion' => 'South-eastern Asia', 'currency_code' => 'IDR', 'currency_name' => 'Indonesian Rupiah'],
            ['name' => 'Iran', 'iso2' => 'IR', 'iso3' => 'IRN', 'phone_code' => '+98', 'region' => 'Asia', 'subregion' => 'Southern Asia', 'currency_code' => 'IRR', 'currency_name' => 'Iranian Rial'],
            ['name' => 'Iraq', 'iso2' => 'IQ', 'iso3' => 'IRQ', 'phone_code' => '+964', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'IQD', 'currency_name' => 'Iraqi Dinar'],
            ['name' => 'Israel', 'iso2' => 'IL', 'iso3' => 'ISR', 'phone_code' => '+972', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'ILS', 'currency_name' => 'Israeli New Shekel'],
            ['name' => 'Japan', 'iso2' => 'JP', 'iso3' => 'JPN', 'phone_code' => '+81', 'region' => 'Asia', 'subregion' => 'Eastern Asia', 'currency_code' => 'JPY', 'currency_name' => 'Japanese Yen'],
            ['name' => 'Jordan', 'iso2' => 'JO', 'iso3' => 'JOR', 'phone_code' => '+962', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'JOD', 'currency_name' => 'Jordanian Dinar'],
            ['name' => 'Kazakhstan', 'iso2' => 'KZ', 'iso3' => 'KAZ', 'phone_code' => '+7', 'region' => 'Asia', 'subregion' => 'Central Asia', 'currency_code' => 'KZT', 'currency_name' => 'Kazakhstani Tenge'],
            ['name' => 'Kuwait', 'iso2' => 'KW', 'iso3' => 'KWT', 'phone_code' => '+965', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'KWD', 'currency_name' => 'Kuwaiti Dinar'],
            ['name' => 'Kyrgyzstan', 'iso2' => 'KG', 'iso3' => 'KGZ', 'phone_code' => '+996', 'region' => 'Asia', 'subregion' => 'Central Asia', 'currency_code' => 'KGS', 'currency_name' => 'Kyrgyzstani Som'],
            ['name' => 'Laos', 'iso2' => 'LA', 'iso3' => 'LAO', 'phone_code' => '+856', 'region' => 'Asia', 'subregion' => 'South-eastern Asia', 'currency_code' => 'LAK', 'currency_name' => 'Lao Kip'],
            ['name' => 'Lebanon', 'iso2' => 'LB', 'iso3' => 'LBN', 'phone_code' => '+961', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'LBP', 'currency_name' => 'Lebanese Pound'],
            ['name' => 'Malaysia', 'iso2' => 'MY', 'iso3' => 'MYS', 'phone_code' => '+60', 'region' => 'Asia', 'subregion' => 'South-eastern Asia', 'currency_code' => 'MYR', 'currency_name' => 'Malaysian Ringgit'],
            ['name' => 'Maldives', 'iso2' => 'MV', 'iso3' => 'MDV', 'phone_code' => '+960', 'region' => 'Asia', 'subregion' => 'Southern Asia', 'currency_code' => 'MVR', 'currency_name' => 'Maldivian Rufiyaa'],
            ['name' => 'Mongolia', 'iso2' => 'MN', 'iso3' => 'MNG', 'phone_code' => '+976', 'region' => 'Asia', 'subregion' => 'Eastern Asia', 'currency_code' => 'MNT', 'currency_name' => 'Mongolian Tögrög'],
            ['name' => 'Myanmar', 'iso2' => 'MM', 'iso3' => 'MMR', 'phone_code' => '+95', 'region' => 'Asia', 'subregion' => 'South-eastern Asia', 'currency_code' => 'MMK', 'currency_name' => 'Myanmar Kyat'],
            ['name' => 'Nepal', 'iso2' => 'NP', 'iso3' => 'NPL', 'phone_code' => '+977', 'region' => 'Asia', 'subregion' => 'Southern Asia', 'currency_code' => 'NPR', 'currency_name' => 'Nepalese Rupee'],
            ['name' => 'North Korea', 'iso2' => 'KP', 'iso3' => 'PRK', 'phone_code' => '+850', 'region' => 'Asia', 'subregion' => 'Eastern Asia', 'currency_code' => 'KPW', 'currency_name' => 'North Korean Won'],
            ['name' => 'Oman', 'iso2' => 'OM', 'iso3' => 'OMN', 'phone_code' => '+968', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'OMR', 'currency_name' => 'Omani Rial'],
            ['name' => 'Pakistan', 'iso2' => 'PK', 'iso3' => 'PAK', 'phone_code' => '+92', 'region' => 'Asia', 'subregion' => 'Southern Asia', 'currency_code' => 'PKR', 'currency_name' => 'Pakistani Rupee'],
            ['name' => 'Palestine', 'iso2' => 'PS', 'iso3' => 'PSE', 'phone_code' => '+970', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'ILS', 'currency_name' => 'Israeli New Shekel'],
            ['name' => 'Philippines', 'iso2' => 'PH', 'iso3' => 'PHL', 'phone_code' => '+63', 'region' => 'Asia', 'subregion' => 'South-eastern Asia', 'currency_code' => 'PHP', 'currency_name' => 'Philippine Peso'],
            ['name' => 'Qatar', 'iso2' => 'QA', 'iso3' => 'QAT', 'phone_code' => '+974', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'QAR', 'currency_name' => 'Qatari Riyal'],
            ['name' => 'Saudi Arabia', 'iso2' => 'SA', 'iso3' => 'SAU', 'phone_code' => '+966', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'SAR', 'currency_name' => 'Saudi Riyal'],
            ['name' => 'Singapore', 'iso2' => 'SG', 'iso3' => 'SGP', 'phone_code' => '+65', 'region' => 'Asia', 'subregion' => 'South-eastern Asia', 'currency_code' => 'SGD', 'currency_name' => 'Singapore Dollar'],
            ['name' => 'South Korea', 'iso2' => 'KR', 'iso3' => 'KOR', 'phone_code' => '+82', 'region' => 'Asia', 'subregion' => 'Eastern Asia', 'currency_code' => 'KRW', 'currency_name' => 'South Korean Won'],
            ['name' => 'Sri Lanka', 'iso2' => 'LK', 'iso3' => 'LKA', 'phone_code' => '+94', 'region' => 'Asia', 'subregion' => 'Southern Asia', 'currency_code' => 'LKR', 'currency_name' => 'Sri Lankan Rupee'],
            ['name' => 'Syria', 'iso2' => 'SY', 'iso3' => 'SYR', 'phone_code' => '+963', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'SYP', 'currency_name' => 'Syrian Pound'],
            ['name' => 'Tajikistan', 'iso2' => 'TJ', 'iso3' => 'TJK', 'phone_code' => '+992', 'region' => 'Asia', 'subregion' => 'Central Asia', 'currency_code' => 'TJS', 'currency_name' => 'Tajikistani Somoni'],
            ['name' => 'Thailand', 'iso2' => 'TH', 'iso3' => 'THA', 'phone_code' => '+66', 'region' => 'Asia', 'subregion' => 'South-eastern Asia', 'currency_code' => 'THB', 'currency_name' => 'Thai Baht'],
            ['name' => 'Timor-Leste', 'iso2' => 'TL', 'iso3' => 'TLS', 'phone_code' => '+670', 'region' => 'Asia', 'subregion' => 'South-eastern Asia', 'currency_code' => 'USD', 'currency_name' => 'US Dollar'],
            ['name' => 'Turkey', 'iso2' => 'TR', 'iso3' => 'TUR', 'phone_code' => '+90', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'TRY', 'currency_name' => 'Turkish Lira'],
            ['name' => 'Turkmenistan', 'iso2' => 'TM', 'iso3' => 'TKM', 'phone_code' => '+993', 'region' => 'Asia', 'subregion' => 'Central Asia', 'currency_code' => 'TMT', 'currency_name' => 'Turkmenistani Manat'],
            ['name' => 'United Arab Emirates', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '+971', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'AED', 'currency_name' => 'UAE Dirham'],
            ['name' => 'Uzbekistan', 'iso2' => 'UZ', 'iso3' => 'UZB', 'phone_code' => '+998', 'region' => 'Asia', 'subregion' => 'Central Asia', 'currency_code' => 'UZS', 'currency_name' => 'Uzbekistani Som'],
            ['name' => 'Vietnam', 'iso2' => 'VN', 'iso3' => 'VNM', 'phone_code' => '+84', 'region' => 'Asia', 'subregion' => 'South-eastern Asia', 'currency_code' => 'VND', 'currency_name' => 'Vietnamese Đồng'],
            ['name' => 'Yemen', 'iso2' => 'YE', 'iso3' => 'YEM', 'phone_code' => '+967', 'region' => 'Asia', 'subregion' => 'Western Asia', 'currency_code' => 'YER', 'currency_name' => 'Yemeni Rial'],

            // ── Europe ──────────────────────────────────────────────────
            ['name' => 'Albania', 'iso2' => 'AL', 'iso3' => 'ALB', 'phone_code' => '+355', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'ALL', 'currency_name' => 'Albanian Lek'],
            ['name' => 'Andorra', 'iso2' => 'AD', 'iso3' => 'AND', 'phone_code' => '+376', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Austria', 'iso2' => 'AT', 'iso3' => 'AUT', 'phone_code' => '+43', 'region' => 'Europe', 'subregion' => 'Western Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Belarus', 'iso2' => 'BY', 'iso3' => 'BLR', 'phone_code' => '+375', 'region' => 'Europe', 'subregion' => 'Eastern Europe', 'currency_code' => 'BYN', 'currency_name' => 'Belarusian Ruble'],
            ['name' => 'Belgium', 'iso2' => 'BE', 'iso3' => 'BEL', 'phone_code' => '+32', 'region' => 'Europe', 'subregion' => 'Western Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Bosnia and Herzegovina', 'iso2' => 'BA', 'iso3' => 'BIH', 'phone_code' => '+387', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'BAM', 'currency_name' => 'Bosnia-Herzegovina Convertible Mark'],
            ['name' => 'Bulgaria', 'iso2' => 'BG', 'iso3' => 'BGR', 'phone_code' => '+359', 'region' => 'Europe', 'subregion' => 'Eastern Europe', 'currency_code' => 'BGN', 'currency_name' => 'Bulgarian Lev'],
            ['name' => 'Croatia', 'iso2' => 'HR', 'iso3' => 'HRV', 'phone_code' => '+385', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Czechia', 'iso2' => 'CZ', 'iso3' => 'CZE', 'phone_code' => '+420', 'region' => 'Europe', 'subregion' => 'Eastern Europe', 'currency_code' => 'CZK', 'currency_name' => 'Czech Koruna'],
            ['name' => 'Denmark', 'iso2' => 'DK', 'iso3' => 'DNK', 'phone_code' => '+45', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'currency_code' => 'DKK', 'currency_name' => 'Danish Krone'],
            ['name' => 'Estonia', 'iso2' => 'EE', 'iso3' => 'EST', 'phone_code' => '+372', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Finland', 'iso2' => 'FI', 'iso3' => 'FIN', 'phone_code' => '+358', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'France', 'iso2' => 'FR', 'iso3' => 'FRA', 'phone_code' => '+33', 'region' => 'Europe', 'subregion' => 'Western Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Germany', 'iso2' => 'DE', 'iso3' => 'DEU', 'phone_code' => '+49', 'region' => 'Europe', 'subregion' => 'Western Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Greece', 'iso2' => 'GR', 'iso3' => 'GRC', 'phone_code' => '+30', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Hungary', 'iso2' => 'HU', 'iso3' => 'HUN', 'phone_code' => '+36', 'region' => 'Europe', 'subregion' => 'Eastern Europe', 'currency_code' => 'HUF', 'currency_name' => 'Hungarian Forint'],
            ['name' => 'Iceland', 'iso2' => 'IS', 'iso3' => 'ISL', 'phone_code' => '+354', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'currency_code' => 'ISK', 'currency_name' => 'Icelandic Króna'],
            ['name' => 'Ireland', 'iso2' => 'IE', 'iso3' => 'IRL', 'phone_code' => '+353', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Italy', 'iso2' => 'IT', 'iso3' => 'ITA', 'phone_code' => '+39', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Latvia', 'iso2' => 'LV', 'iso3' => 'LVA', 'phone_code' => '+371', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Liechtenstein', 'iso2' => 'LI', 'iso3' => 'LIE', 'phone_code' => '+423', 'region' => 'Europe', 'subregion' => 'Western Europe', 'currency_code' => 'CHF', 'currency_name' => 'Swiss Franc'],
            ['name' => 'Lithuania', 'iso2' => 'LT', 'iso3' => 'LTU', 'phone_code' => '+370', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Luxembourg', 'iso2' => 'LU', 'iso3' => 'LUX', 'phone_code' => '+352', 'region' => 'Europe', 'subregion' => 'Western Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Malta', 'iso2' => 'MT', 'iso3' => 'MLT', 'phone_code' => '+356', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Moldova', 'iso2' => 'MD', 'iso3' => 'MDA', 'phone_code' => '+373', 'region' => 'Europe', 'subregion' => 'Eastern Europe', 'currency_code' => 'MDL', 'currency_name' => 'Moldovan Leu'],
            ['name' => 'Monaco', 'iso2' => 'MC', 'iso3' => 'MCO', 'phone_code' => '+377', 'region' => 'Europe', 'subregion' => 'Western Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Montenegro', 'iso2' => 'ME', 'iso3' => 'MNE', 'phone_code' => '+382', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Netherlands', 'iso2' => 'NL', 'iso3' => 'NLD', 'phone_code' => '+31', 'region' => 'Europe', 'subregion' => 'Western Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'North Macedonia', 'iso2' => 'MK', 'iso3' => 'MKD', 'phone_code' => '+389', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'MKD', 'currency_name' => 'Macedonian Denar'],
            ['name' => 'Norway', 'iso2' => 'NO', 'iso3' => 'NOR', 'phone_code' => '+47', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'currency_code' => 'NOK', 'currency_name' => 'Norwegian Krone'],
            ['name' => 'Poland', 'iso2' => 'PL', 'iso3' => 'POL', 'phone_code' => '+48', 'region' => 'Europe', 'subregion' => 'Eastern Europe', 'currency_code' => 'PLN', 'currency_name' => 'Polish Złoty'],
            ['name' => 'Portugal', 'iso2' => 'PT', 'iso3' => 'PRT', 'phone_code' => '+351', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Romania', 'iso2' => 'RO', 'iso3' => 'ROU', 'phone_code' => '+40', 'region' => 'Europe', 'subregion' => 'Eastern Europe', 'currency_code' => 'RON', 'currency_name' => 'Romanian Leu'],
            ['name' => 'Russia', 'iso2' => 'RU', 'iso3' => 'RUS', 'phone_code' => '+7', 'region' => 'Europe', 'subregion' => 'Eastern Europe', 'currency_code' => 'RUB', 'currency_name' => 'Russian Ruble'],
            ['name' => 'San Marino', 'iso2' => 'SM', 'iso3' => 'SMR', 'phone_code' => '+378', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Serbia', 'iso2' => 'RS', 'iso3' => 'SRB', 'phone_code' => '+381', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'RSD', 'currency_name' => 'Serbian Dinar'],
            ['name' => 'Slovakia', 'iso2' => 'SK', 'iso3' => 'SVK', 'phone_code' => '+421', 'region' => 'Europe', 'subregion' => 'Eastern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Slovenia', 'iso2' => 'SI', 'iso3' => 'SVN', 'phone_code' => '+386', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Spain', 'iso2' => 'ES', 'iso3' => 'ESP', 'phone_code' => '+34', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Sweden', 'iso2' => 'SE', 'iso3' => 'SWE', 'phone_code' => '+46', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'currency_code' => 'SEK', 'currency_name' => 'Swedish Krona'],
            ['name' => 'Switzerland', 'iso2' => 'CH', 'iso3' => 'CHE', 'phone_code' => '+41', 'region' => 'Europe', 'subregion' => 'Western Europe', 'currency_code' => 'CHF', 'currency_name' => 'Swiss Franc'],
            ['name' => 'Ukraine', 'iso2' => 'UA', 'iso3' => 'UKR', 'phone_code' => '+380', 'region' => 'Europe', 'subregion' => 'Eastern Europe', 'currency_code' => 'UAH', 'currency_name' => 'Ukrainian Hryvnia'],
            ['name' => 'United Kingdom', 'iso2' => 'GB', 'iso3' => 'GBR', 'phone_code' => '+44', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'currency_code' => 'GBP', 'currency_name' => 'British Pound'],
            ['name' => 'Vatican City', 'iso2' => 'VA', 'iso3' => 'VAT', 'phone_code' => '+379', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],

            // ── Oceania ─────────────────────────────────────────────────
            ['name' => 'Australia', 'iso2' => 'AU', 'iso3' => 'AUS', 'phone_code' => '+61', 'region' => 'Oceania', 'subregion' => 'Australia and New Zealand', 'currency_code' => 'AUD', 'currency_name' => 'Australian Dollar'],
            ['name' => 'Fiji', 'iso2' => 'FJ', 'iso3' => 'FJI', 'phone_code' => '+679', 'region' => 'Oceania', 'subregion' => 'Melanesia', 'currency_code' => 'FJD', 'currency_name' => 'Fijian Dollar'],
            ['name' => 'Kiribati', 'iso2' => 'KI', 'iso3' => 'KIR', 'phone_code' => '+686', 'region' => 'Oceania', 'subregion' => 'Micronesia', 'currency_code' => 'AUD', 'currency_name' => 'Australian Dollar'],
            ['name' => 'Marshall Islands', 'iso2' => 'MH', 'iso3' => 'MHL', 'phone_code' => '+692', 'region' => 'Oceania', 'subregion' => 'Micronesia', 'currency_code' => 'USD', 'currency_name' => 'US Dollar'],
            ['name' => 'Micronesia', 'iso2' => 'FM', 'iso3' => 'FSM', 'phone_code' => '+691', 'region' => 'Oceania', 'subregion' => 'Micronesia', 'currency_code' => 'USD', 'currency_name' => 'US Dollar'],
            ['name' => 'Nauru', 'iso2' => 'NR', 'iso3' => 'NRU', 'phone_code' => '+674', 'region' => 'Oceania', 'subregion' => 'Micronesia', 'currency_code' => 'AUD', 'currency_name' => 'Australian Dollar'],
            ['name' => 'New Zealand', 'iso2' => 'NZ', 'iso3' => 'NZL', 'phone_code' => '+64', 'region' => 'Oceania', 'subregion' => 'Australia and New Zealand', 'currency_code' => 'NZD', 'currency_name' => 'New Zealand Dollar'],
            ['name' => 'Palau', 'iso2' => 'PW', 'iso3' => 'PLW', 'phone_code' => '+680', 'region' => 'Oceania', 'subregion' => 'Micronesia', 'currency_code' => 'USD', 'currency_name' => 'US Dollar'],
            ['name' => 'Papua New Guinea', 'iso2' => 'PG', 'iso3' => 'PNG', 'phone_code' => '+675', 'region' => 'Oceania', 'subregion' => 'Melanesia', 'currency_code' => 'PGK', 'currency_name' => 'Papua New Guinean Kina'],
            ['name' => 'Samoa', 'iso2' => 'WS', 'iso3' => 'WSM', 'phone_code' => '+685', 'region' => 'Oceania', 'subregion' => 'Polynesia', 'currency_code' => 'WST', 'currency_name' => 'Samoan Tālā'],
            ['name' => 'Solomon Islands', 'iso2' => 'SB', 'iso3' => 'SLB', 'phone_code' => '+677', 'region' => 'Oceania', 'subregion' => 'Melanesia', 'currency_code' => 'SBD', 'currency_name' => 'Solomon Islands Dollar'],
            ['name' => 'Tonga', 'iso2' => 'TO', 'iso3' => 'TON', 'phone_code' => '+676', 'region' => 'Oceania', 'subregion' => 'Polynesia', 'currency_code' => 'TOP', 'currency_name' => 'Tongan Paʻanga'],
            ['name' => 'Tuvalu', 'iso2' => 'TV', 'iso3' => 'TUV', 'phone_code' => '+688', 'region' => 'Oceania', 'subregion' => 'Polynesia', 'currency_code' => 'AUD', 'currency_name' => 'Australian Dollar'],
            ['name' => 'Vanuatu', 'iso2' => 'VU', 'iso3' => 'VUT', 'phone_code' => '+678', 'region' => 'Oceania', 'subregion' => 'Melanesia', 'currency_code' => 'VUV', 'currency_name' => 'Vanuatu Vatu'],
        ];
    }
}
