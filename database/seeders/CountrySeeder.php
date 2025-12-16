<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;
class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = ['Japan', 'UAE', 'Pakistan', 'South Africa'];

        foreach ($countries as $country) {
            Country::firstOrCreate(['name' => $country]);
        }
    }
}
