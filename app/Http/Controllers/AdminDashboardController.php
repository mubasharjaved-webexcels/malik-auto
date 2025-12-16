<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CarProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        ///////--      expenses graph query start        --//////////
        $query = CarProfile::selectRaw('
            DATE_FORMAT(car_profiles.created_at, "%b") as month,
            MONTH(car_profiles.created_at) as month_number,
            SUM(car_profiles.price) as total_price,
            COALESCE(SUM(car_expenses.usd_amount), 0) as total_expenses
        ')
        ->leftJoin('car_expenses', 'car_profiles.id', '=', 'car_expenses.car_profile_id');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('car_profiles.created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $monthlyData = $query
            ->groupBy('month', 'month_number')
            ->orderBy('month_number')
            ->get();
        ///////--       expenses graph query end         --//////////

        
        
        $countries = Country::withCount('carProfiles')->get();
        
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $carExpenses = DB::table('car_expenses')
            ->join('car_profiles', 'car_expenses.car_profile_id', '=', 'car_profiles.id')
            ->join('countries', 'car_profiles.country_id', '=', 'countries.id')
            ->select('countries.name as country_name', DB::raw('SUM(car_expenses.usd_amount) as total_usd'))
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('car_expenses.created_at', [$startDate, $endDate]);
            })
            ->groupBy('countries.name')
            ->get();
        $totalCarExpense = $carExpenses->sum('total_usd');
            
        $officeExpenses = DB::table('office_expenses')
            ->join('countries', 'office_expenses.country_id', '=', 'countries.id')
            ->select('countries.name as country_name', DB::raw('SUM(office_expenses.usd_amount) as total_usd'))
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('office_expenses.created_at', [$startDate, $endDate]);
            })
            ->groupBy('countries.name')
            ->get();
        $totalOfficeExpense = $officeExpenses->sum('total_usd');

        $soldPrices = DB::table('car_profiles')
            ->join('countries', 'car_profiles.country_id', '=', 'countries.id')
            ->select('countries.name as country_name', DB::raw('COALESCE(SUM(car_profiles.sold_price), 0) as total_sold_price'))
            ->where('car_profiles.car_status', 'sold')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('car_profiles.updated_at', [$startDate, $endDate]);
            })
            ->groupBy('countries.name')
            ->get();
        $totalSoldPrice = $soldPrices->sum('total_sold_price');

        $latestTransactions = DB::table('account_transactions as at')
            ->select('at.account_id', 'at.closing_balance', 'at.created_at')
            ->join(
                DB::raw('(SELECT account_id, MAX(created_at) as max_created_at
                        FROM account_transactions
                        WHERE created_at <= "' . $endDate . '"
                        GROUP BY account_id) as latest'),
                function ($join) {
                    $join->on('at.account_id', '=', 'latest.account_id')
                        ->on('at.created_at', '=', 'latest.max_created_at');
                }
            );

        $cashSummary = DB::table('bank_cash_accounts as acc')
            ->leftJoinSub($latestTransactions, 'tx', 'tx.account_id', '=', 'acc.id')
            ->join('countries', 'acc.country_id', '=', 'countries.id')
            ->where('acc.status', 'active')
            ->select(
                DB::raw('LOWER(countries.name) as country_name'),
                'acc.type',
                DB::raw('SUM(COALESCE(tx.closing_balance, acc.opening_balance)) as total_balance')
            )
            ->groupBy('country_name', 'acc.type')
            ->get()
            ->groupBy('country_name');

        ///////// for car status card count 
        $per_page_value = $request->input('per_page', 25);

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
        
        $baseQuery = CarProfile::query();

        $statusCounts = (clone $baseQuery)
            ->selectRaw('LOWER(car_status) as status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statusCounts = array_merge(array_fill_keys(array_keys($statuses), 0), $statusCounts);

        ///////// for car status card count 
        return view('dashboards.admin', compact('countries', 'carExpenses', 'totalCarExpense', 'officeExpenses', 'totalOfficeExpense', 'startDate', 
            'endDate', 'soldPrices', 'totalSoldPrice', 'cashSummary', 'monthlyData', 
            'per_page_value', 'statuses', 'statusCounts'));
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
