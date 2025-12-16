<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarExpense;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use App\Models\CarProfile;
use App\Models\Country;
use App\Models\BankCashAccount;
use App\Models\AccountTransaction;

class CarExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $isManager = $user->hasRole('manager');

        $countryQuery = Country::whereNotNull('currency_type')
            ->select('id', 'name', 'currency_type', 'currency_rate');

        if ($isManager) {
            $countryQuery->where('id', $user->country_id);
        }

        $countries = $countryQuery->get();
        $currencies = $countries->pluck('currency_type')->unique()->values();
        $currencyRates = $countries->pluck('currency_rate', 'currency_type');

        $carQuery = CarProfile::select('id', 'rec_no', 'chassis', 'car_image', 'country_id')
            ->where('car_status', '!=', 'sold')
            ->whereNotNull('rec_no');

        if ($isManager) {
            $carQuery->where('country_id', $user->country_id)
                    ->where('assigned_manager_id', $user->id);
        }

        $carProfiles = $carQuery->orderBy('rec_no')->get();

        $carExpenseQuery = CarExpense::with([
                'creator:id,name',
                'account',
                'carProfile:id,rec_no,car_image,country_id,assigned_manager_id'
            ])
            ->whereNull('deleted_at')
            ->orderByDesc('created_at');

        if ($isManager) {
            $carExpenseQuery->whereHas('carProfile', function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->where('country_id', $user->country_id)
                    ->where('assigned_manager_id', $user->id);
            });
        }

        $carExpenses = $carExpenseQuery->paginate(25);
        
        return view('dashboards.car_expenses.index', compact('currencies', 'carProfiles', 'carExpenses', 'currencyRates', 'countries'));
    }

    // public function index()
    // {
    //     $user = auth()->user();
    //     $countryData = Country::whereNotNull('currency_type')
    //                     ->get(['id', 'name', 'currency_type', 'currency_rate']);

    //     $countries = $countryData;
    //     $currencies = $countryData->pluck('currency_type')->unique()->values();
    //     $currencyRates = $countryData->pluck('currency_rate', 'currency_type');

    //     $carQuery = CarProfile::select('id', 'rec_no', 'chassis', 'car_image', 'country_id')->whereNotNull('rec_no');

    //     if ($user->hasRole('manager')) {
    //         $carQuery->where('country_id', $user->country_id)->where('assigned_manager_id', $user->id);
    //     }
    //     $carProfiles = $carQuery->orderBy('rec_no')->get();

    //     $carExpenseQuery = CarExpense::whereNull('deleted_at')->with(['creator:id,name','account',
    //                         'carProfile:id,rec_no,car_image,country_id,assigned_manager_id',
    //                     ])->orderByDesc('created_at');
                        
    //     if ($user->hasRole('manager')) {
    //         $carExpenseQuery->whereHas('carProfile', function ($query) use ($user) {
    //             $query->where('created_by', $user->id)
    //                 ->where('country_id', $user->country_id)
    //                 ->where('assigned_manager_id', $user->id);
    //         });
    //         $countries = Country::where('id', $user->country_id)->get();
    //     }

    //     $carExpenses = $carExpenseQuery->paginate(10);

    //     return view('dashboards.car_expenses.index', compact('currencies', 'carProfiles', 'carExpenses', 'currencyRates', 'countries'));
    // }

    // habib's index
// public function index()
// {
    // $currencies = \App\Models\Country::select('currency_type')
    //                 ->whereNotNull('currency_type')
    //                 ->distinct()
    //                 ->pluck('currency_type');

    // $carProfiles = \App\Models\CarProfile::select('id', 'rec_no', 'chassis', 'car_image')
    //                 ->whereNotNull('rec_no')
    //                 ->orderBy('rec_no')
    //                 ->get();

    // // IMPORTANT: Load the carProfile relationship
    // $carExpenses = CarExpense::with(['creator:id,name', 'carProfile:id,rec_no,car_image'])
    //                 ->orderBy('created_at', 'desc')
    //                 ->get();

    // // Get currency rates for USD conversion
    // $currencyRates = \App\Models\Country::select('currency_type', 'currency_rate')
    //                 ->whereNotNull('currency_type')
    //                 ->whereNotNull('currency_rate')
    //                 ->pluck('currency_rate', 'currency_type');
    // return view('dashboards.car_expenses.index', compact('currencies', 'carProfiles', 'carExpenses', 'currencyRates', 'countries'));
// }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboards.car_expenses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Get stock_number from car_profile_id
            $stockNumber = null;
            if ($request->car_profile_id) {
                $carProfile = \App\Models\CarProfile::find($request->car_profile_id);
                $stockNumber = $carProfile ? $carProfile->rec_no : null;
            }

            // Validation rules
            $validator = Validator::make($request->all(), [
                'car_profile_id' => 'required|exists:car_profiles,id',
                'expenses_for' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|string|max:10',
            ]);

            // Check if request is AJAX
            if ($request->ajax()) {
                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }

                $currency_rate = Country::where('currency_type', $request->currency)->value('currency_rate');
                $usd_amount = $request->amount / $currency_rate;

                $account = BankCashAccount::find($request->account_id);

                if ($account && $request->amount > 0) {
                    if ($account->opening_balance < $request->amount) {
                        return response()->json(['error' => 'Insufficient balance in selected account.'], 400);
                    }
                    
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
                    'created_by' => auth()->id(), // Add authenticated user ID
                    'account_id' => $request->account_id,
                ]);

                if ($carExpense) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Car expense added successfully!',
                        'data' => $carExpense
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add car expense.'
                ], 500);
            } else {
                // Traditional form submission
                $request->validate([
                    'car_profile_id' => 'required|exists:car_profiles,id',
                    'expenses_for' => 'required|string|max:255',
                    'amount' => 'required|numeric|min:0',
                    'currency' => 'required|string|max:10',
                ]);

                CarExpense::create([
                    'car_profile_id' => $request->car_profile_id,
                    'stock_number' => $stockNumber,
                    'expenses_for' => $request->expenses_for,
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'created_by' => auth()->id(), // Add authenticated user ID
                ]);

                return redirect()->route('car-expenses.index')
                                ->with('success', 'Car expense created successfully.');
            }

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            } else {
                return redirect()->back()
                                ->with('error', 'An error occurred: ' . $e->getMessage())
                                ->withInput();
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $carExpense = CarExpense::with('carProfile')->findOrFail($id);
            
            // If AJAX request, return JSON
            if (request()->ajax()) {
                return response()->json($carExpense);
            }
            
            return view('dashboards.car_expenses.show', compact('carExpense'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Car expense not found.'
                ], 404);
            } else {
                return redirect()->route('car-expenses.index')
                                ->with('error', 'Car expense not found.');
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $carExpense = CarExpense::findOrFail($id);
            return view('dashboards.car_expenses.edit', compact('carExpense'));
        } catch (\Exception $e) {
            return redirect()->route('car-expenses.index')
                            ->with('error', 'Car expense not found.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Get stock_number from car_profile_id
            $stockNumber = null;
            if ($request->car_profile_id) {
                $carProfile = \App\Models\CarProfile::find($request->car_profile_id);
                $stockNumber = $carProfile ? $carProfile->rec_no : null;
            }

            // Validation rules
            $validator = Validator::make($request->all(), [
                'car_profile_id' => 'required|exists:car_profiles,id',
                'expenses_for' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|string|max:10',
            ]);

            $carExpense = CarExpense::findOrFail($id);

            // Check if request is AJAX
            if ($request->ajax()) {
                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }

                $updated = $carExpense->update([
                    'car_profile_id' => $request->car_profile_id,
                    'stock_number' => $stockNumber,
                    'expenses_for' => $request->expenses_for,
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                ]);

                if ($updated) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Car expense updated successfully!',
                        'data' => $carExpense
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update car expense.'
                ], 500);
            } else {
                // Traditional form submission
                $request->validate([
                    'car_profile_id' => 'required|exists:car_profiles,id',
                    'expenses_for' => 'required|string|max:255',
                    'amount' => 'required|numeric|min:0',
                    'currency' => 'required|string|max:10',
                ]);

                $carExpense->update([
                    'car_profile_id' => $request->car_profile_id,
                    'stock_number' => $stockNumber,
                    'expenses_for' => $request->expenses_for,
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                ]);

                return redirect()->route('car-expenses.index')
                                ->with('success', 'Car expense updated successfully.');
            }

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            } else {
                return redirect()->back()
                                ->with('error', 'An error occurred: ' . $e->getMessage())
                                ->withInput();
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $carExpense = CarExpense::findOrFail($id);
            
            if ($carExpense->delete()) {
                // Check if request is AJAX
                if (request()->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Car expense deleted successfully!'
                    ]);
                } else {
                    return redirect()->route('car-expenses.index')
                                    ->with('success', 'Car expense deleted successfully.');
                }
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete car expense.'
                ], 500);
            } else {
                return redirect()->route('car-expenses.index')
                                ->with('error', 'Failed to delete car expense.');
            }

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            } else {
                return redirect()->route('car-expenses.index')
                                ->with('error', 'An error occurred: ' . $e->getMessage());
            }
        }
    }
// Filter added by naqshbandi
    public function filter(Request $request)
    {
        try {
            $response = [];
            $user = auth()->user();

            ////////// For manager role only //////////////
            if ($user->hasRole('manager')) {
                $carExpenseQuery = CarExpense::whereNull('deleted_at')
                    ->where('created_by', $user->id)
                    ->whereHas('carProfile', function ($query) use ($user, $request) {
                        $query->where('country_id', $user->country_id)
                            ->where('assigned_manager_id', $user->id);
                            // ->where('car_status', '!=', 'sold');

                        if ($request->filled('rec_no')) {
                            $query->where('rec_no', $request->rec_no);
                        }
                    })
                    ->with([
                        'creator:id,name',
                        'carProfile:id,rec_no,car_image,located_yard,country_id',
                        'currencyInfo:id,currency_type,currency_rate'
                    ]);

                $grandTotal = number_format((clone $carExpenseQuery)->sum('usd_amount'), 2);
                $carExpenses = $carExpenseQuery->orderByDesc('created_at')->get();

                // Rec Nos
                $recNos = CarExpense::whereNull('deleted_at')
                    ->where('created_by', $user->id)
                    ->whereHas('carProfile', function ($query) use ($user) {
                        $query->where('assigned_manager_id', $user->id)
                            ->where('country_id', $user->country_id);
                            // ->where('car_status', '!=', 'sold');
                    })
                    ->with('carProfile:id,rec_no')
                    ->get()
                    ->pluck('carProfile.rec_no')
                    ->unique()
                    ->values();

                $response['rec_nos'] = $recNos;
                $response['expenses'] = view('dashboards.car_expenses.partials.expense_listing', compact('carExpenses'))->render();
                $response['grand_total_usd'] = $grandTotal;

                return response()->json($response);
            }
            ////////// For manager role only //////////////

            // For admin, Managers for selected country
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
                $managers = \App\Models\User::whereIn('id', $managerIds)->select('id', 'name')->get();

                $response['managers'] = $managers;
            }

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
            // Fetch Filtered Car Expenses
            if ($request->filled('country_id')) {
                $Expenses = CarExpense::whereNull('deleted_at')->with([
                        'creator:id,name',
                        'carProfile:id,rec_no,car_image,located_yard,country_id',
                        'currencyInfo:id,currency_type,currency_rate'
                    ])
                    ->whereHas('carProfile', function ($query) use ($request) {
                        $query->when($request->country_id, fn($q) => $q->where('country_id', $request->country_id))
                            ->when(!$request->filled('manager'), fn($q) => $q->whereNotNull('assigned_manager_id'))
                            ->when($request->manager, fn($q) => $q->where('assigned_manager_id', $request->manager))
                            // ->when($request->yard, fn($q) => $q->where('located_yard', $request->yard))
                            ->when($request->rec_no, fn($q) => $q->where('rec_no', $request->rec_no));
                    });
                    $grandTotal = number_format((clone $Expenses)->sum('usd_amount'), 2);
                    $carExpenses = $Expenses->orderByDesc('created_at')->get();

                $response['expenses'] = view('dashboards.car_expenses.partials.expense_listing',compact('carExpenses'))->render();
                $response['grand_total_usd'] = $grandTotal;
            }

            return response()->json($response);

        } catch (\Throwable $e) {
            \Log::error('Filter Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $user = auth()->user();
        $isManager = $user->hasRole('manager');
        $search = $request->input('search');

        $query = CarExpense::with([
                'creator:id,name',
                'account',
                'carProfile:id,rec_no,car_image,country_id,assigned_manager_id'
            ])->whereNull('deleted_at');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('stock_number', 'LIKE', "%{$search}%")
                ->orWhere('expenses_for', 'LIKE', "%{$search}%");
            });
        }

        if ($isManager) {
            $query->whereHas('carProfile', function ($q) use ($user) {
                $q->where('created_by', $user->id)
                ->where('country_id', $user->country_id)
                ->where('assigned_manager_id', $user->id);
            });
        }

        $carExpenses = $query->orderByDesc('created_at')->paginate(10);
        $countryQuery = Country::whereNotNull('currency_type')
            ->select('id', 'name', 'currency_type', 'currency_rate');

        if ($isManager) {
            $countryQuery->where('id', $user->country_id);
        }

        $countries = $countryQuery->get();
        $currencies = $countries->pluck('currency_type')->unique()->values();
        $currencyRates = $countries->pluck('currency_rate', 'currency_type');

        $carQuery = CarProfile::select('id', 'rec_no', 'chassis', 'car_image', 'country_id')
            ->where('car_status', '!=', 'sold')
            ->whereNotNull('rec_no');

        if ($isManager) {
            $carQuery->where('country_id', $user->country_id)
                    ->where('assigned_manager_id', $user->id);
        }

        $carProfiles = $carQuery->orderBy('rec_no')->get();
        if ($request->ajax()) {
            $grandTotalHtml = '
                <tfoot id="car-expense-tfoot">
                    <tr>
                        <th colspan="5" class="text-end"><strong>Grand Total:</strong></th>
                        <th id="car-expense-grand-total">
                            <strong class="text-success">$' . number_format($carExpenses->sum('usd_amount'), 2) . '</strong>
                            <small class="text-muted d-block">Total USD Amount</small>
                        </th>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
            ';

            return response()->json([
                'tbody' => view(
                    'dashboards.car_expenses.partials.expense_listing',
                    compact('currencies', 'carProfiles', 'carExpenses', 'currencyRates', 'countries')
                )->render(),
                'tfoot' => $grandTotalHtml,
            ]);
        }
    }

}