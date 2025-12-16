<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\CarProfile;

class ManagerDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $per_page_value = $request->input('per_page', 25);

        if (! $user->hasRole('manager')) {
            abort(403);
        }

        $country = $user->country;

        $statuses = [
            'new_arrival' => 'New Arrival',
            'ready_for_sale' => 'Ready For Sale',
            'in_transit' => 'In Transit',
            'pending_sold' => 'Pending Sold',
            'sold' => 'Sold',
            'rejected' => 'Rejected',
            'booked' => 'Booked',
            'under_maintenance' => 'Under Maintenance',
        ];
        
        $baseQuery = CarProfile::with('country')
            ->where('assigned_manager_id', $user->id);
            
        $totalAssignedCars = (clone $baseQuery)
            ->where('car_status', '!=', 'sold')
            ->count();

        $statusCounts = (clone $baseQuery)
            ->selectRaw('LOWER(car_status) as status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statusCounts = array_merge(array_fill_keys(array_keys($statuses), 0), $statusCounts);

        return view('dashboards.manager', compact('country', 'totalAssignedCars', 'per_page_value', 'statuses', 'statusCounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
