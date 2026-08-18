<?php

namespace Database\Seeders;

use App\Models\AgriculturalInput;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AgriculturalInputSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed 20 medicine and 30 fertilizer products for the agricultural
     * input store.
     */
    public function run(): void
    {
        $manufacturers = [
            'AgroChem Uganda Ltd', 'Balton CP Uganda', 'Yara Uganda', 'Osho Chemical Industries',
            'Pearl Fertilizers', 'Victoria Chemicals', 'Elgon AgroSupplies', 'Nile Agro Industries',
            'Bukoola Chemical Industries', 'Greenlife Crop Protection Africa',
        ];

        $medicine = [
            ['name' => 'Copper Oxychloride 50WP Fungicide', 'tag' => 'Fungicide', 'unit' => 'kg', 'price' => [18, 32], 'description' => 'Broad-spectrum protective fungicide for controlling coffee leaf rust and other foliar fungal diseases.'],
            ['name' => 'Cypermethrin 10EC Insecticide', 'tag' => 'Insecticide', 'unit' => 'litre', 'price' => [14, 26], 'description' => 'Fast-acting contact insecticide effective against coffee berry borer and leaf-eating pests.'],
            ['name' => 'Glyphosate 41% SL Herbicide', 'tag' => 'Herbicide', 'unit' => 'litre', 'price' => [12, 22], 'description' => 'Non-selective systemic herbicide for clearing weeds around farm plots before planting.'],
            ['name' => 'Coffee Berry Borer Trap Lure', 'tag' => 'Pest Control', 'unit' => 'pack', 'price' => [8, 15], 'description' => 'Pheromone-baited trap lure used to monitor and reduce coffee berry borer populations.'],
            ['name' => 'Streptomycin Sulfate Bactericide', 'tag' => 'Bactericide', 'unit' => 'sachet', 'price' => [6, 12], 'description' => 'Antibacterial treatment for controlling bacterial blight and wilt in coffee seedlings.'],
            ['name' => 'Carbofuran 3G Nematicide', 'tag' => 'Nematicide', 'unit' => 'kg', 'price' => [16, 28], 'description' => 'Granular soil nematicide that protects root systems from nematode damage.'],
            ['name' => 'Mancozeb 80WP Fungicide', 'tag' => 'Fungicide', 'unit' => 'kg', 'price' => [15, 27], 'description' => 'Multi-site contact fungicide for managing anthracnose and leaf spot diseases.'],
            ['name' => 'Imidacloprid 200SL Systemic Insecticide', 'tag' => 'Insecticide', 'unit' => 'litre', 'price' => [20, 35], 'description' => 'Systemic insecticide absorbed by the plant to control sucking pests such as aphids and mealybugs.'],
            ['name' => 'Coffee Wilt Disease Biocontrol Spray', 'tag' => 'Biocontrol', 'unit' => 'bottle', 'price' => [22, 38], 'description' => 'Biological treatment that suppresses Fusarium xylarioides, the pathogen behind coffee wilt disease.'],
            ['name' => 'Triadimefon Leaf Rust Control', 'tag' => 'Fungicide', 'unit' => 'kg', 'price' => [19, 30], 'description' => 'Systemic fungicide targeted at controlling coffee leaf rust outbreaks during wet season.'],
            ['name' => 'Abamectin 1.8EC Miticide', 'tag' => 'Miticide', 'unit' => 'litre', 'price' => [24, 40], 'description' => 'Miticide and insecticide combination for controlling spider mites and leaf miners.'],
            ['name' => 'Oxytetracycline Foliar Antibiotic Spray', 'tag' => 'Bactericide', 'unit' => 'bottle', 'price' => [10, 18], 'description' => 'Foliar-applied antibiotic used to manage bacterial leaf spot in young coffee plants.'],
            ['name' => 'Bromadiolone Rodenticide Bait', 'tag' => 'Rodenticide', 'unit' => 'pack', 'price' => [7, 14], 'description' => 'Anticoagulant bait for controlling rodents that damage stored produce and seedlings.'],
            ['name' => 'Phosphine Post-Harvest Fumigant Tablets', 'tag' => 'Fumigant', 'unit' => 'pack', 'price' => [25, 42], 'description' => 'Fumigation tablets used to protect stored dried coffee from weevils and grain pests.'],
            ['name' => 'Metalaxyl 35WS Root Rot Treatment', 'tag' => 'Fungicide', 'unit' => 'kg', 'price' => [17, 29], 'description' => 'Seed and soil treatment fungicide that protects seedlings from root rot and damping-off.'],
            ['name' => 'Chlorothalonil 75WP Anthracnose Control', 'tag' => 'Fungicide', 'unit' => 'kg', 'price' => [16, 26], 'description' => 'Preventive fungicide for controlling anthracnose and berry disease on coffee trees.'],
            ['name' => 'Trichoderma Biofungicide Powder', 'tag' => 'Biocontrol', 'unit' => 'kg', 'price' => [13, 24], 'description' => 'Beneficial fungus applied to soil to suppress root pathogens and improve plant vigour.'],
            ['name' => 'Fipronil 2.5SC Termite Control', 'tag' => 'Pest Control', 'unit' => 'litre', 'price' => [21, 34], 'description' => 'Non-repellent termiticide that protects young coffee trees from termite damage.'],
            ['name' => 'Pirimicarb 50WG Aphid Control', 'tag' => 'Insecticide', 'unit' => 'kg', 'price' => [15, 25], 'description' => 'Selective aphicide that controls aphid infestations while sparing beneficial insects.'],
            ['name' => 'Diazinon 60EC Mealybug Treatment', 'tag' => 'Insecticide', 'unit' => 'litre', 'price' => [18, 30], 'description' => 'Broad-spectrum insecticide effective against mealybugs and scale insects on coffee.'],
        ];

        $fertilizer = [
            ['name' => 'NPK 17-17-17 Compound Fertilizer', 'tag' => 'NPK Blend', 'unit' => 'bag (50kg)', 'price' => [95, 140], 'description' => 'Balanced compound fertilizer supplying nitrogen, phosphorus, and potassium for general crop growth.'],
            ['name' => 'Urea 46% Nitrogen Fertilizer', 'tag' => 'Nitrogen', 'unit' => 'bag (50kg)', 'price' => [85, 125], 'description' => 'High-nitrogen granular fertilizer that boosts vegetative growth and leaf development.'],
            ['name' => 'Triple Super Phosphate (TSP)', 'tag' => 'Phosphate', 'unit' => 'bag (50kg)', 'price' => [90, 130], 'description' => 'Concentrated phosphate fertilizer that strengthens root development and flowering.'],
            ['name' => 'Muriate of Potash (MOP)', 'tag' => 'Potash', 'unit' => 'bag (50kg)', 'price' => [88, 128], 'description' => 'Potassium-rich fertilizer that improves bean quality and disease resistance.'],
            ['name' => 'Diammonium Phosphate (DAP)', 'tag' => 'Phosphate', 'unit' => 'bag (50kg)', 'price' => [100, 145], 'description' => 'Fast-dissolving phosphate and nitrogen fertilizer ideal for planting and early growth stages.'],
            ['name' => 'Organic Compost Blend', 'tag' => 'Organic', 'unit' => 'bag (50kg)', 'price' => [40, 65], 'description' => 'Fully decomposed organic matter that improves soil structure and moisture retention.'],
            ['name' => 'Calcium Ammonium Nitrate (CAN)', 'tag' => 'Nitrogen', 'unit' => 'bag (50kg)', 'price' => [82, 118], 'description' => 'Nitrogen fertilizer with added calcium for steady growth and soil pH balance.'],
            ['name' => 'Coffee Pulp Compost', 'tag' => 'Organic', 'unit' => 'bag (50kg)', 'price' => [35, 58], 'description' => 'Recycled coffee pulp compost that recycles farm waste into a nutrient-rich soil amendment.'],
            ['name' => 'Vermicompost Organic Fertilizer', 'tag' => 'Organic', 'unit' => 'bag (25kg)', 'price' => [45, 70], 'description' => 'Worm-processed compost rich in microbial activity for healthier root systems.'],
            ['name' => 'Seaweed Extract Foliar Feed', 'tag' => 'Foliar Feed', 'unit' => 'bottle', 'price' => [20, 35], 'description' => 'Liquid seaweed extract applied to leaves to boost stress tolerance and yield.'],
            ['name' => 'Magnesium Sulfate (Epsom Salt)', 'tag' => 'Micronutrient', 'unit' => 'kg', 'price' => [8, 16], 'description' => 'Corrects magnesium deficiency, preventing yellowing between leaf veins.'],
            ['name' => 'Zinc Sulfate Micronutrient Mix', 'tag' => 'Micronutrient', 'unit' => 'kg', 'price' => [10, 18], 'description' => 'Micronutrient supplement that addresses zinc deficiency common in coffee soils.'],
            ['name' => 'Boron Foliar Spray', 'tag' => 'Micronutrient', 'unit' => 'bottle', 'price' => [12, 20], 'description' => 'Foliar boron supplement that supports flowering and fruit set.'],
            ['name' => 'Chicken Manure Pellets', 'tag' => 'Organic', 'unit' => 'bag (50kg)', 'price' => [38, 60], 'description' => 'Pelletized poultry manure providing slow-release nitrogen and organic matter.'],
            ['name' => 'NPK 20-10-10 Coffee Blend', 'tag' => 'NPK Blend', 'unit' => 'bag (50kg)', 'price' => [98, 142], 'description' => 'Nitrogen-forward blend formulated specifically for coffee tree vegetative stages.'],
            ['name' => 'Bone Meal Organic Fertilizer', 'tag' => 'Organic', 'unit' => 'kg', 'price' => [14, 24], 'description' => 'Slow-release organic phosphorus source that strengthens root and flower development.'],
            ['name' => 'Gypsum Soil Conditioner', 'tag' => 'Soil Conditioner', 'unit' => 'bag (50kg)', 'price' => [50, 75], 'description' => 'Improves soil structure and drainage while supplying calcium and sulfur.'],
            ['name' => 'Liquid Foliar Fertilizer NPK 10-5-5', 'tag' => 'Foliar Feed', 'unit' => 'litre', 'price' => [18, 30], 'description' => 'Fast-absorbing liquid fertilizer for quick correction of nutrient deficiencies.'],
            ['name' => 'Biochar Soil Amendment', 'tag' => 'Soil Conditioner', 'unit' => 'bag (25kg)', 'price' => [42, 68], 'description' => 'Carbon-rich soil amendment that improves water retention and microbial activity.'],
            ['name' => 'Rock Phosphate', 'tag' => 'Phosphate', 'unit' => 'bag (50kg)', 'price' => [70, 105], 'description' => 'Slow-release natural phosphate source suited for long-term soil fertility building.'],
            ['name' => 'Ammonium Sulfate 21%', 'tag' => 'Nitrogen', 'unit' => 'bag (50kg)', 'price' => [80, 115], 'description' => 'Nitrogen and sulfur fertilizer that supports leaf growth and protein synthesis.'],
            ['name' => 'Slow-Release NPK 15-15-15', 'tag' => 'NPK Blend', 'unit' => 'bag (50kg)', 'price' => [105, 150], 'description' => 'Controlled-release balanced fertilizer that feeds crops steadily over several months.'],
            ['name' => 'Humic Acid Soil Enhancer', 'tag' => 'Soil Conditioner', 'unit' => 'litre', 'price' => [22, 38], 'description' => 'Improves nutrient uptake efficiency and stimulates root development.'],
            ['name' => 'Coffee Husk Compost', 'tag' => 'Organic', 'unit' => 'bag (50kg)', 'price' => [30, 52], 'description' => 'Composted coffee husks that return organic matter and potassium to the soil.'],
            ['name' => 'Potassium Sulfate (SOP)', 'tag' => 'Potash', 'unit' => 'bag (50kg)', 'price' => [96, 138], 'description' => 'Chloride-free potassium source that improves bean size and cup quality.'],
            ['name' => 'Micronutrient Foliar Blend', 'tag' => 'Micronutrient', 'unit' => 'bottle', 'price' => [16, 28], 'description' => 'Combined trace-element spray covering zinc, boron, and manganese deficiencies.'],
            ['name' => 'Green Manure Cover Crop Seed', 'tag' => 'Organic', 'unit' => 'kg', 'price' => [9, 17], 'description' => 'Fast-growing cover crop seed used to fix nitrogen and suppress weeds between rows.'],
            ['name' => 'Dolomite Lime Soil Corrector', 'tag' => 'Soil Conditioner', 'unit' => 'bag (50kg)', 'price' => [45, 68], 'description' => 'Raises soil pH and supplies calcium and magnesium to acidic coffee soils.'],
            ['name' => 'Fish Emulsion Organic Fertilizer', 'tag' => 'Organic', 'unit' => 'bottle', 'price' => [19, 32], 'description' => 'Nutrient-dense liquid fertilizer made from fish byproducts for rapid green-up.'],
            ['name' => 'NPK 12-24-12 Starter Fertilizer', 'tag' => 'NPK Blend', 'unit' => 'bag (50kg)', 'price' => [92, 136], 'description' => 'Phosphorus-heavy starter blend that establishes strong root systems in new seedlings.'],
        ];

        $userIds = User::query()->pluck('id')->all();

        $this->seedCategory('medicine', $medicine, $manufacturers, $userIds);
        $this->seedCategory('fertilizer', $fertilizer, $manufacturers, $userIds);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, string>  $manufacturers
     * @param  array<int, int>  $userIds
     */
    protected function seedCategory(string $category, array $items, array $manufacturers, array $userIds): void
    {
        $prefix = strtoupper(substr($category, 0, 3));

        foreach ($items as $index => $item) {
            $sku = sprintf('%s-%03d', $prefix, $index + 1);

            AgriculturalInput::query()->updateOrCreate(
                ['sku' => $sku],
                [
                    'user_id' => $userIds ? fake()->randomElement($userIds) : null,
                    'name' => $item['name'],
                    'category' => $category,
                    'tag' => $item['tag'],
                    'description' => $item['description'],
                    'price' => fake()->randomFloat(2, $item['price'][0], $item['price'][1]),
                    'image' => null,
                    'stock_quantity' => fake()->numberBetween(0, 500),
                    'unit' => $item['unit'],
                    'manufacturer' => fake()->randomElement($manufacturers),
                    'status' => 'active',
                ],
            );
        }
    }
}
