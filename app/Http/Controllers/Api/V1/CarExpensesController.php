<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CarExpenseRequest;
use App\Http\Resources\Api\V1\CarExpenseResource;
use App\Models\CarExpense;
use App\Models\CarProfile;
use App\Models\Country;
use App\Models\BankCashAccount;
use App\Models\AccountTransaction;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarExpensesController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of car expenses.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $isManager = $user->hasRole('manager');

            $query = CarExpense::with([
                'creator:id,name',
                'account',
                'carProfile:id,rec_no,car_image,country_id,assigned_manager_id,chassis,located_yard',
                'currencyInfo'
            ])->whereNull('deleted_at');

            // Manager role restrictions
            if ($isManager) {
                $query->whereHas('carProfile', function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->where('country_id', $user->country_id)
                        ->where('assigned_manager_id', $user->id);
                });
            }

            // Apply filters
            if ($request->filled('car_profile_id')) {
                $query->where('car_profile_id', $request->car_profile_id);
            }

            if ($request->filled('currency')) {
                $query->where('currency', $request->currency);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->input('per_page', 25);
            $carExpenses = $query->paginate($perPage);

            // Calculate grand total
            $grandTotal = CarExpense::whereNull('deleted_at')
                ->when($isManager, function ($q) use ($user) {
                    $q->whereHas('carProfile', function ($query) use ($user) {
                        $query->where('created_by', $user->id)
                            ->where('country_id', $user->country_id)
                            ->where('assigned_manager_id', $user->id);
                    });
                })
                ->sum('usd_amount');

            return $this->success([
                'expenses' => CarExpenseResource::collection($carExpenses),
                'pagination' => [
                    'total' => $carExpenses->total(),
                    'per_page' => $carExpenses->perPage(),
                    'current_page' => $carExpenses->currentPage(),
                    'last_page' => $carExpenses->lastPage(),
                    'from' => $carExpenses->firstItem(),
                    'to' => $carExpenses->lastItem(),
                ],
                'grand_total_usd' => (float) $grandTotal,
            ], 'Car expenses retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve car expenses: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get metadata for car expenses (currencies, car profiles, countries, etc.)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function metadata()
    {
        try {
            $user = auth()->user();
            $isManager = $user->hasRole('manager');

            // Countries
            $countryQuery = Country::whereNotNull('currency_type')
                ->select('id', 'name', 'currency_type', 'currency_rate');

            if ($isManager) {
                $countryQuery->where('id', $user->country_id);
            }

            $countries = $countryQuery->get();
            $currencies = $countries->pluck('currency_type')->unique()->values();
            $currencyRates = $countries->pluck('currency_rate', 'currency_type');

            // Car Profiles
            $carQuery = CarProfile::select('id', 'rec_no', 'chassis', 'car_image', 'country_id')
                ->where('car_status', '!=', 'sold')
                ->whereNotNull('rec_no');

            if ($isManager) {
                $carQuery->where('country_id', $user->country_id)
                    ->where('assigned_manager_id', $user->id);
            }

            $carProfiles = $carQuery->orderBy('rec_no')->get();

            // Bank/Cash Accounts
            $accounts = BankCashAccount::where('status', 'active')
                ->get();

            return $this->success([
                'countries' => $countries,
                'currencies' => $currencies,
                'currency_rates' => $currencyRates,
                'car_profiles' => $carProfiles,
                'accounts' => $accounts,
            ], 'Metadata retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve metadata: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created car expense.
     *
     * @param CarExpenseRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CarExpenseRequest $request)
    {
        DB::beginTransaction();

        try {
            // Get stock number from car profile
            $carProfile = CarProfile::find($request->car_profile_id);
            $stockNumber = $carProfile ? $carProfile->rec_no : null;

            // Calculate USD amount with validation
            $currency_rate = Country::where('currency_type', $request->currency)
                ->value('currency_rate');

            // Check if currency_rate is valid
            if (!$currency_rate || $currency_rate == 0) {
                DB::rollBack();
                return $this->error('Invalid currency or currency rate not found', 400);
            }
            $usd_amount = $request->amount / $currency_rate;
            // Handle account transaction
            if ($request->filled('account_id') && $request->amount > 0) {
                $account = BankCashAccount::find($request->account_id);

                if (!$account) {
                    DB::rollBack();
                    return $this->error('Account not found', 404);
                }

                if ($account->opening_balance < $request->amount) {
                    DB::rollBack();
                    return $this->error('Insufficient balance in selected account', 400);
                }

                // Create transaction
                AccountTransaction::create([
                    'account_id' => $account->id,
                    'transaction_type' => $request->expenses_for,
                    'reference_id' => $request->car_profile_id,
                    'amount' => $request->amount,
                    'flow_type' => 'debit',
                    'opening_balance' => $account->opening_balance,
                    'closing_balance' => $account->opening_balance - $request->amount,
                    'currency_type' => $request->currency,
                    'created_by' => auth()->id(),
                ]);

                // Update account balance
                $account->opening_balance -= $request->amount;
                $account->save();
            }

            // Create car expense
            $carExpense = CarExpense::create([
                'car_profile_id' => $request->car_profile_id,
                'stock_number' => $stockNumber,
                'expenses_for' => $request->expenses_for,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'usd_amount' => $usd_amount,
                'created_by' => auth()->id(),
                'account_id' => $request->account_id,
            ]);

            // Load relationships
            $carExpense->load(['creator', 'account', 'carProfile', 'currencyInfo']);

            DB::commit();

            return $this->success(
                new CarExpenseResource($carExpense),
                'Car expense created successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to create car expense: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified car expense.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $carExpense = CarExpense::with([
                'creator',
                'account',
                'carProfile',
                'currencyInfo'
            ])->findOrFail($id);

            return $this->success(
                new CarExpenseResource($carExpense),
                'Car expense retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Car expense not found', 404);
        }
    }

    /**
     * Update the specified car expense.
     *
     * @param CarExpenseRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CarExpenseRequest $request, string $id)
    {
        DB::beginTransaction();

        try {
            $carExpense = CarExpense::findOrFail($id);

            // Get stock number if car_profile_id is being updated
            $stockNumber = $carExpense->stock_number;
            if ($request->filled('car_profile_id')) {
                $carProfile = CarProfile::find($request->car_profile_id);
                $stockNumber = $carProfile ? $carProfile->rec_no : null;
            }

            // Calculate USD amount if amount or currency is being updated
            $usd_amount = $carExpense->usd_amount;
            if ($request->filled('amount') || $request->filled('currency')) {
                $amount = $request->filled('amount') ? $request->amount : $carExpense->amount;
                $currency = $request->filled('currency') ? $request->currency : $carExpense->currency;

                $currency_rate = Country::where('currency_type', $currency)->value('currency_rate');
                $usd_amount = $amount / $currency_rate;
            }

            // Update car expense
            $carExpense->update([
                'car_profile_id' => $request->input('car_profile_id', $carExpense->car_profile_id),
                'stock_number' => $stockNumber,
                'expenses_for' => $request->input('expenses_for', $carExpense->expenses_for),
                'amount' => $request->input('amount', $carExpense->amount),
                'currency' => $request->input('currency', $carExpense->currency),
                'usd_amount' => $usd_amount,
                'account_id' => $request->input('account_id', $carExpense->account_id),
            ]);

            // Load relationships
            $carExpense->load(['creator', 'account', 'carProfile', 'currencyInfo']);

            DB::commit();

            return $this->success(
                new CarExpenseResource($carExpense),
                'Car expense updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to update car expense: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified car expense.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $carExpense = CarExpense::findOrFail($id);
            $carExpense->delete();

            return $this->success(null, 'Car expense deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete car expense: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Filter car expenses based on criteria.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filter(Request $request)
    {
        try {
            $user = auth()->user();
            $isManager = $user->hasRole('manager');
            // dd($isManager);
            $response = [];

            // Manager role - get their expenses and rec_nos
            if ($isManager) {
                $carExpenseQuery = CarExpense::whereNull('deleted_at')
                    ->where('created_by', $user->id)
                    ->whereHas('carProfile', function ($query) use ($user, $request) {
                        $query->where('country_id', $user->country_id)
                            ->where('assigned_manager_id', $user->id);

                        if ($request->filled('rec_no')) {
                            $query->where('rec_no', $request->rec_no);
                        }
                    })
                    ->with([
                        'creator:id,name',
                        'carProfile:id,rec_no,car_image,located_yard,country_id',
                        'currencyInfo:id,currency_type,currency_rate'
                    ]);

                $grandTotal = (clone $carExpenseQuery)->sum('usd_amount');
                $carExpenses = $carExpenseQuery->orderByDesc('created_at')->get();

                // Get available rec_nos
                $recNos = CarExpense::whereNull('deleted_at')
                    ->where('created_by', $user->id)
                    ->whereHas('carProfile', function ($query) use ($user) {
                        $query->where('assigned_manager_id', $user->id)
                            ->where('country_id', $user->country_id);
                    })
                    ->with('carProfile:id,rec_no')
                    ->get()
                    ->pluck('carProfile.rec_no')
                    ->unique()
                    ->values();

                return $this->success([
                    'rec_nos' => $recNos,
                    'expenses' => CarExpenseResource::collection($carExpenses),
                    'grand_total_usd' => (float) $grandTotal,
                ], 'Expenses filtered successfully');
            }

            // Admin role - get managers for selected country
            if ($request->filled('country_id') && !$request->filled('manager')) {
                $managerIds = CarExpense::whereNull('deleted_at')
                    ->whereHas('carProfile', function ($q) use ($request) {
                        $q->where('country_id', $request->country_id)
                            ->whereNotNull('assigned_manager_id');
                    })
                    ->with('carProfile:id,assigned_manager_id,country_id')
                    ->get()
                    ->pluck('carProfile.assigned_manager_id')
                    ->unique()
                    ->filter()
                    ->values();

                $managers = \App\Models\User::whereIn('id', $managerIds)
                    ->select('id', 'name')
                    ->get();

                $response['managers'] = $managers;
            }

            // Get rec_nos for selected country and manager
            if ($request->filled('country_id') && $request->filled('manager') && !$request->filled('rec_no')) {
                $recNos = CarExpense::whereNull('deleted_at')
                    ->whereHas('carProfile', function ($q) use ($request) {
                        $q->where('country_id', $request->country_id)
                            ->where('assigned_manager_id', $request->manager);
                    })
                    ->with('carProfile:id,rec_no,assigned_manager_id,country_id')
                    ->get()
                    ->pluck('carProfile.rec_no')
                    ->unique()
                    ->values();

                $response['rec_nos'] = $recNos;
            }

            // Fetch filtered expenses
            if ($request->filled('country_id')) {
                $expensesQuery = CarExpense::whereNull('deleted_at')
                    ->with([
                        'creator:id,name',
                        'carProfile:id,rec_no,car_image,located_yard,country_id',
                        'currencyInfo:id,currency_type,currency_rate'
                    ])
                    ->whereHas('carProfile', function ($query) use ($request) {
                        $query->when($request->country_id, fn($q) => $q->where('country_id', $request->country_id))
                            ->when(!$request->filled('manager'), fn($q) => $q->whereNotNull('assigned_manager_id'))
                            ->when($request->manager, fn($q) => $q->where('assigned_manager_id', $request->manager))
                            ->when($request->rec_no, fn($q) => $q->where('rec_no', $request->rec_no));
                    });

                $grandTotal = (clone $expensesQuery)->sum('usd_amount');
                $carExpenses = $expensesQuery->orderByDesc('created_at')->get();

                $response['expenses'] = CarExpenseResource::collection($carExpenses);
                $response['grand_total_usd'] = (float) $grandTotal;
            }

            return $this->success($response, 'Filter applied successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to filter expenses: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Search car expenses.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $user = auth()->user();
            $isManager = $user->hasRole('manager');
            $search = $request->input('search');

            $query = CarExpense::with([
                'creator:id,name',
                'account',
                'carProfile:id,rec_no,car_image,country_id,assigned_manager_id'
            ])->whereNull('deleted_at');

            // Apply search
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('stock_number', 'LIKE', "%{$search}%")
                        ->orWhere('expenses_for', 'LIKE', "%{$search}%");
                });
            }

            // Manager restrictions
            if ($isManager) {
                $query->whereHas('carProfile', function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->where('country_id', $user->country_id)
                        ->where('assigned_manager_id', $user->id);
                });
            }

            $perPage = $request->input('per_page', 25);
            $carExpenses = $query->orderByDesc('created_at')->paginate($perPage);

            $grandTotal = $carExpenses->sum('usd_amount');

            return $this->success([
                'expenses' => CarExpenseResource::collection($carExpenses),
                'pagination' => [
                    'total' => $carExpenses->total(),
                    'per_page' => $carExpenses->perPage(),
                    'current_page' => $carExpenses->currentPage(),
                    'last_page' => $carExpenses->lastPage(),
                ],
                'grand_total_usd' => (float) $grandTotal,
            ], 'Search completed successfully');
        } catch (\Exception $e) {
            return $this->error('Search failed: ' . $e->getMessage(), 500);
        }
    }
}
