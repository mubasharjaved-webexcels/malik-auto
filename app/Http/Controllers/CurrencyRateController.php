<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;

class CurrencyRateController extends Controller
{
    public function index()
    {
        $countries = Country::whereNotNull('currency_rate')->orderBy('name')->get();
        return view('dashboards.currency_rates.index', compact('countries'));
    }

    public function update(Request $request, Country $country)
    {
        $request->validate([
            // 'name' => 'required|string|max:255',
            // 'currency_type' => 'required|string|max:10',
            'currency_rate' => 'required|numeric|min:0',
        ]);

        $country->update([
            // 'name' => $request->name,
            // 'currency_type' => $request->currency_type,
            'currency_rate' => $request->currency_rate,
        ]);

        return redirect()->back()->with('success', 'Currency information updated successfully!');
    }


}
