<?php

namespace Database\Factories;

use App\Models\Farmer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Farmer>
 */
class FarmerFactory extends Factory
{
    protected $model = Farmer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $profiles = [
            [
                'district' => 'Mbale',
                'sub_counties' => ['Buginyanya', 'Busiu', 'Bukonde'],
                'coffee_types' => ['arabica'],
                'cooperatives' => ['Sipi Farmers Cooperative', 'Mount Elgon Growers Union'],
            ],
            [
                'district' => 'Sironko',
                'sub_counties' => ['Budadiri', 'Bumasifwa', 'Bukiyi'],
                'coffee_types' => ['arabica'],
                'cooperatives' => ['Bugisu Arabica Growers', 'Elgon Highland Cooperative'],
            ],
            [
                'district' => 'Kapchorwa',
                'sub_counties' => ['Tegeres', 'Kaserem', 'Chema'],
                'coffee_types' => ['arabica'],
                'cooperatives' => ['Sebei Highlands Cooperative', 'Kapchorwa Coffee Farmers Union'],
            ],
            [
                'district' => 'Kasese',
                'sub_counties' => ['Kisinga', 'Kyarumba', 'Nyakiyumbu'],
                'coffee_types' => ['arabica', 'mixed'],
                'cooperatives' => ['Rwenzori Coffee Cooperative', 'Kasese Highland Farmers'],
            ],
            [
                'district' => 'Ntungamo',
                'sub_counties' => ['Rubaare', 'Itojo', 'Nyakyera'],
                'coffee_types' => ['arabica', 'robusta', 'mixed'],
                'cooperatives' => ['Ankole Coffee Producers Union', 'Southwest Coffee Network'],
            ],
            [
                'district' => 'Mukono',
                'sub_counties' => ['Nakifuma', 'Mpatta', 'Kojja'],
                'coffee_types' => ['robusta'],
                'cooperatives' => ['Lake Victoria Coffee Growers', 'Mukono Coffee Producers Cooperative'],
            ],
            [
                'district' => 'Masaka',
                'sub_counties' => ['Buwunga', 'Kyanamukaka', 'Kabonera'],
                'coffee_types' => ['robusta'],
                'cooperatives' => ['Masaka Coffee Farmers SACCO', 'Greater Masaka Growers Union'],
            ],
            [
                'district' => 'Luwero',
                'sub_counties' => ['Wobulenzi', 'Butuntumula', 'Bamunanika'],
                'coffee_types' => ['robusta'],
                'cooperatives' => ['Luwero Coffee Collective', 'Central Uganda Coffee Cooperative'],
            ],
        ];

        $profile = fake()->randomElement($profiles);
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $email = fake()->unique()->safeEmail();
        $telephone = '+2567'.fake()->unique()->numerify('########');
        $coffeeType = fake()->randomElement($profile['coffee_types']);
        $farmSize = fake()->randomFloat(1, 0.5, 8.0).' acres';

        return [
            'user_id' => User::factory()->state([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role' => 'farmer',
                'telephone' => $telephone,
                'email' => $email,
            ]),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'telephone' => $telephone,
            'email' => fake()->boolean(85) ? $email : null,
            'district' => $profile['district'],
            'sub_county' => fake()->randomElement($profile['sub_counties']),
            'coffee_type' => $coffeeType,
            'cooperative' => fake()->boolean(80) ? fake()->randomElement($profile['cooperatives']) : null,
            'farm_size' => fake()->boolean(90) ? $farmSize : null,
            'notes' => fake()->optional(0.65)->randomElement([
                'Delivers hand-picked cherries during peak season.',
                'Active in traceability training and record keeping.',
                'Interested in expanding certified export volumes.',
                'Uses raised drying beds for specialty micro-lots.',
                'Regular supplier to the local wet mill and coop collection center.',
            ]),
        ];
    }
}
