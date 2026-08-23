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
            ['district' => 'Mbale', 'subcounties' => ['Buginyanya', 'Busiu', 'Bukonde'], 'region' => 'Eastern'],
            ['district' => 'Sironko', 'subcounties' => ['Budadiri', 'Bumasifwa', 'Bukiyi'], 'region' => 'Eastern'],
            ['district' => 'Kapchorwa', 'subcounties' => ['Tegeres', 'Kaserem', 'Chema'], 'region' => 'Eastern'],
            ['district' => 'Kasese', 'subcounties' => ['Kisinga', 'Kyarumba', 'Nyakiyumbu'], 'region' => 'Western'],
            ['district' => 'Ntungamo', 'subcounties' => ['Rubaare', 'Itojo', 'Nyakyera'], 'region' => 'Western'],
            ['district' => 'Mukono', 'subcounties' => ['Nakifuma', 'Mpatta', 'Kojja'], 'region' => 'Central'],
            ['district' => 'Masaka', 'subcounties' => ['Buwunga', 'Kyanamukaka', 'Kabonera'], 'region' => 'Central'],
            ['district' => 'Luwero', 'subcounties' => ['Wobulenzi', 'Butuntumula', 'Bamunanika'], 'region' => 'Central'],
        ];

        $profile = fake()->randomElement($profiles);
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $email = fake()->unique()->safeEmail();
        $tel = '+2567'.fake()->unique()->numerify('########');

        return [
            'user_id' => User::factory()->state([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role' => 'farmer',
                'telephone' => $tel,
                'email' => $email,
            ]),
            'cooperative_id' => null,
            'farmer_number' => 'FMR-'.now()->format('Y').'-'.strtoupper(fake()->unique()->bothify('??####')),
            'first_name' => $firstName,
            'middle_name' => fake()->boolean(30) ? fake()->firstName() : null,
            'last_name' => $lastName,
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
            'tel' => $tel,
            'email' => fake()->boolean(85) ? $email : null,
            'country' => 'Uganda',
            'region' => $profile['region'],
            'district' => $profile['district'],
            'county' => null,
            'subcounty' => fake()->randomElement($profile['subcounties']),
            'parish' => fake()->boolean(60) ? fake()->citySuffix() : null,
            'village' => fake()->boolean(70) ? fake()->streetName() : null,
            'national_id' => fake()->boolean(75) ? strtoupper(fake()->unique()->bothify('CM########????')) : null,
            'status' => 'active',
            'verification_status' => fake()->randomElement(['pending', 'verified']),
        ];
    }
}
