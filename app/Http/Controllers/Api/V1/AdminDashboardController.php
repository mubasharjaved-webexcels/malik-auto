<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CarProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponse;

class AdminDashboardController extends Controller
{
    // use ApiResponse;

    public function index(Request $request)
    {
        /* =======================
           Expenses Graph (Monthly)
        ======================== */
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

        /* =======================
           Countries + Cars Count
        ======================== */
        $countries = Country::withCount('carProfiles')->get();

        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        /* =======================
           Car Expenses
        ======================== */
        $carExpenses = DB::table('car_expenses')
            ->join('car_profiles', 'car_expenses.car_profile_id', '=', 'car_profiles.id')
            ->join('countries', 'car_profiles.country_id', '=', 'countries.id')
            ->select('countries.name as country', DB::raw('SUM(car_expenses.usd_amount) as total'))
            ->when(
                $startDate && $endDate,
                fn($q) =>
                $q->whereBetween('car_expenses.created_at', [$startDate, $endDate])
            )
            ->groupBy('countries.name')
            ->get();

        $totalCarExpense = $carExpenses->sum('total');

        /* =======================
           Office Expenses
        ======================== */
        $officeExpenses = DB::table('office_expenses')
            ->join('countries', 'office_expenses.country_id', '=', 'countries.id')
            ->select('countries.name as country', DB::raw('SUM(office_expenses.usd_amount) as total'))
            ->when(
                $startDate && $endDate,
                fn($q) =>
                $q->whereBetween('office_expenses.created_at', [$startDate, $endDate])
            )
            ->groupBy('countries.name')
            ->get();

        $totalOfficeExpense = $officeExpenses->sum('total');

        /* =======================
           Sold Cars Revenue
        ======================== */
        $soldPrices = DB::table('car_profiles')
            ->join('countries', 'car_profiles.country_id', '=', 'countries.id')
            ->select('countries.name as country', DB::raw('SUM(car_profiles.sold_price) as total'))
            ->where('car_profiles.car_status', 'sold')
            ->when(
                $startDate && $endDate,
                fn($q) =>
                $q->whereBetween('car_profiles.updated_at', [$startDate, $endDate])
            )
            ->groupBy('countries.name')
            ->get();

        $totalSoldPrice = $soldPrices->sum('total');

        /* =======================
           Cash / Bank Summary
        ======================== */
        $latestTransactions = DB::table('account_transactions as at')
            ->select('at.account_id', 'at.closing_balance', 'at.created_at')
            ->join(DB::raw('(
                SELECT account_id, MAX(created_at) as max_created_at
                FROM account_transactions
                GROUP BY account_id
            ) as latest'), function ($join) {
                $join->on('at.account_id', '=', 'latest.account_id')
                    ->on('at.created_at', '=', 'latest.max_created_at');
            });

        $cashSummary = DB::table('bank_cash_accounts as acc')
            ->leftJoinSub($latestTransactions, 'tx', 'tx.account_id', '=', 'acc.id')
            ->join('countries', 'acc.country_id', '=', 'countries.id')
            ->where('acc.status', 'active')
            ->select(
                DB::raw('LOWER(countries.name) as country'),
                'acc.type',
                DB::raw('SUM(COALESCE(tx.closing_balance, acc.opening_balance)) as balance')
            )
            ->groupBy('country', 'acc.type')
            ->get()
            ->groupBy('country');

        /* =======================
           Car Status Counts
        ======================== */
        $statuses = [
            'new_arrival',
            'ready_for_sale',
            'in_transit',
            'pending_sold',
            'sold',
            'rejected',
            'booked',
            'under_maintenance',
        ];

        $statusCounts = CarProfile::selectRaw('LOWER(car_status) as status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statusCounts = array_merge(array_fill_keys($statuses, 0), $statusCounts);

        /* =======================
           FINAL API RESPONSE
        ======================== */
        return $this->success([
            'countries' => $countries,
            'monthly_graph' => $monthlyData,
            'car_expenses' => [
                'list' => $carExpenses,
                'total' => $totalCarExpense
            ],
            'office_expenses' => [
                'list' => $officeExpenses,
                'total' => $totalOfficeExpense
            ],
            'sold_revenue' => [
                'list' => $soldPrices,
                'total' => $totalSoldPrice
            ],
            'cash_summary' => $cashSummary,
            'car_status_counts' => $statusCounts
        ], 'Admin dashboard data loaded successfully');
    }
}
