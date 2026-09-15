<?php

namespace Database\Seeders;

use App\Models\SustainabilityVerificationMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SustainabilityVerificationMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The sustainability/compliance standards and regulations a lot,
     * batch, or farm's practices can be verified against — each
     * description notes what should be tracked for that standard.
     */
    public function run(): void
    {
        $items = [
            'EUDR' => 'Deforestation — Farm geolocation, polygons, deforestation risk, legality, due diligence.',
            'CSRD / ESRS' => 'Corporate sustainability — Sustainability reporting and disclosures.',
            'CSDDD / CS3D' => 'Supply-chain responsibility — Human rights, environmental due diligence.',
            'EU Green Claims rules' => 'Green claims — Evidence behind environmental claims.',
            'EU Forced Labour Regulation' => 'Forced labour — Forced-labour risks in supply chains.',
            'EU Organic Regulation' => 'Organic — Organic production/certification.',
            'Rainforest Alliance' => 'Certification — Environmental, social, farm-management and traceability requirements.',
            'Fairtrade' => 'Fair trade — Fairtrade standards, premiums, producer requirements.',
            '4C Certification' => 'Sustainability — Sustainable coffee production and supply-chain requirements.',
            'USDA Organic' => 'Organic — Organic requirements for the US market.',
            'EU Organic' => 'Organic — Organic requirements for the EU market.',
            'GlobalG.A.P.' => 'Sustainability — Good agricultural practices and farm assurance.',
            'Regenerative Agriculture standards' => 'Regenerative — Soil, biodiversity, water and climate practices.',
            'Deforestation-free sourcing' => 'Forest — Deforestation and land-use risk.',
            'ILO standards' => 'Labour — Labour and worker protections.',
            'SA8000' => 'Social — Social accountability and working conditions.',
            'SMETA / Sedex' => 'Supply chain — Labour, health & safety, environment and ethics audits.',
            'GHG Protocol' => 'Climate — Supply-chain greenhouse-gas accounting.',
            'Science Based Targets (SBTi)' => 'Climate — Corporate emissions-reduction targets.',
            'AWS / water stewardship frameworks' => 'Water — Water risk and responsible water use.',
            'TNFD' => 'Biodiversity — Nature-related dependencies, risks and disclosures.',
        ];

        $index = 0;
        foreach ($items as $name => $description) {
            $index++;
            SustainabilityVerificationMetadata::query()->updateOrCreate(
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
