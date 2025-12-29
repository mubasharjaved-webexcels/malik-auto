<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Country;
use Illuminate\Validation\ValidationException;

class CurrencyRateController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/currency-rates
     */
    public function index()
    {
        try {
            $countries = Country::whereNotNull('currency_rate')
                ->orderBy('name')
                ->get(['id', 'name', 'currency_type', 'currency_rate']);

            return $this->success($countries, 'Currency rates fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch currency rates', 500);
        }
    }

    /**
     * PUT /api/currency-rates/{country}
     */
    public function update(Request $request, Country $country)
    {
        try {
            $validated = $request->validate([
                'currency_rate' => 'required|numeric|min:0',
            ]);

            $country->update([
                'currency_rate' => $validated['currency_rate'],
            ]);

            return $this->success(
                $country->only(['id', 'name', 'currency_type', 'currency_rate']),
                'Currency information updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->error(
                'Validation error',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->error('Something went wrong', code: 500);
        }
    }
}
