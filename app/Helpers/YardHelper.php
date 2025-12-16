<?php

use App\Models\CarProfile;

if (!function_exists('getYardsByCountry')) {
    function getYardsByCountry($countryId)
    {
        return CarProfile::where('country_id', $countryId)
            ->whereNotNull('located_yard')
            ->distinct()
            ->pluck('located_yard');
    }
}
