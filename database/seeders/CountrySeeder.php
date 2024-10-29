<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run()
    {
        $countries = [
            'Sierra Leone',
            'Brazil',
            'Colombia',
            'Côte d\'Ivoire',
            'Vietnam',
            'Guinea',
            'Indonesia',
        ];

        foreach ($countries as $country) {
            Country::create(['name' => $country]);
        }
    }
}
