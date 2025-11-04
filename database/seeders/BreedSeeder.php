<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Breed;
use App\Models\enums\SpeciesEnum;

class BreedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use short, concise breed names (<= 20 chars) to fit DB column
        $dogs = [
            'Labrador',
            'Pastor',
            'Golden',
            'Bulldog',
            'Caniche',
            'Beagle',
            'Rottweiler',
            'Yorkshire',
            'Boxer',
            'Teckel',
            'Chihuahua',
            'Shihtzu',
            'Husky',
            'Corgi',
            'Pitbull'
        ];

        $cats = [
            'Persa',
            'Siames',
            'Maine',
            'Ragdoll',
            'Bengal',
            'Sphynx',
            'British',
            'Scottish',
            'AzulRuso',
            'Exotico',
            'AmShorthair',
            'Noruego'
        ];

        foreach ($dogs as $name) {
            $label = mb_substr($name, 0, 20, 'UTF-8');
            Breed::firstOrCreate([
                'name' => $label,
                'species' => SpeciesEnum::DOG->value,
            ]);
        }

        foreach ($cats as $name) {
            $label = mb_substr($name, 0, 20, 'UTF-8');
            Breed::firstOrCreate([
                'name' => $label,
                'species' => SpeciesEnum::CAT->value,
            ]);
        }
    }
}
