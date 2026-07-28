<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $cropTypes = ['Arabica', 'Robusta'];
        $varieties = ['SL28', 'SL34', 'Bourbon', 'Typica', 'Caturra', 'Catuai', null];
        $grades = ['AA', 'AB', 'PB', 'Grade 1', 'Grade 2', 'FAQ'];
        $currencies = ['USD', 'EUR', 'UGX'];
        $statuses = ['open', 'open', 'open', 'open', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'withdrawn'];

        $userIds = User::query()->pluck('id')->all();

        for ($i = 1; $i <= 100; $i++) {
            $type = $i <= 50 ? 'request' : 'offer';
            $quantity = fake()->randomFloat(2, 200, 5000);
            $unitPrice = fake()->randomFloat(2, 2.5, 9.5);

            Order::query()->updateOrCreate(
                ['order_number' => 'ORD-' . strtoupper($type) . '-' . now()->format('Y') . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT)],
                [
                    'type' => $type,
                    'buyer_id' => $type === 'request' && $userIds ? fake()->randomElement($userIds) : null,
                    'seller_id' => $type === 'offer' && $userIds ? fake()->randomElement($userIds) : null,
                    'crop_type' => fake()->randomElement($cropTypes),
                    'variety' => fake()->randomElement($varieties),
                    'grade' => fake()->randomElement($grades),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_amount' => round($quantity * $unitPrice, 2),
                    'currency' => fake()->randomElement($currencies),
                    'notes' => fake()->boolean(40) ? fake()->sentence(10) : null,
                    'status' => fake()->randomElement($statuses),
                ],
            );
        }
    }
}
