<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarProfile;
use App\Models\Country;
use App\Models\BankCashAccount;
use App\Models\AccountTransaction;
use App\Models\CarExpense;

class CarProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
        $user = auth()->user();
        $rawType = \DB::selectOne("SHOW COLUMNS FROM car_profiles WHERE Field = 'car_status'")->Type;
        preg_match('/^enum\((.*)\)$/', $rawType, $matches);
        $carStatuses = isset($matches[1]) ? array_map(fn($v) => trim($v, "'"), explode(',', $matches[1])) : [];
        $per_page_value = $request->input('per_page', 25); 

        if($user->hasRole('admin')) {
            $countries = Country::all();
            $query = CarProfile::query();
            if ($request->filled('country_id')) {
                $query->where('country_id', $request->country_id)->where('car_status', '!=', 'sold');
            }
            
            if ($user->hasRole('manager')) {
                $query->where('country_id', $user->country_id)
                    ->where('assigned_manager_id', $user->id);
            }
    
            if ($request->filled('country_id') && $user->hasRole('admin')) {
                $query->where('country_id', $request->country_id);
                
            }
    
            if($request->filled('assigned_manager_id')){
                $query->where('assigned_manager_id', $request->assigned_manager_id);
            }
    
            if ($request->filled('rec_no')) {
                $query->where('rec_no', $request->rec_no);
            }
    
            if ($request->filled('car_status')) {
                $query->where('car_status', $request->car_status);
            }
            $carProfiles = $query->with(['countryCurrency', 'expenses.currencyInfo', 'country', 'assignedManager'])
                                ->withSum('expenses', 'usd_amount')
                                ->latest()
                                ->paginate($per_page_value);
                            
                            
            $managerQuery = CarProfile::query();
            if ($user->hasRole('manager')) {
                $managerQuery->where('country_id', $user->country_id)
                        ->where('assigned_manager_id', $user->id);
            }
    
            $managerIdsQuery = CarProfile::query();
            if ($user->hasRole('manager')) {
                $managerIdsQuery->where('country_id', $user->country_id)
                                ->where('assigned_manager_id', $user->id);
            }
    
            if ($request->filled('country_id') && $user->hasRole('admin')) {
                $managerIdsQuery->where('country_id', $request->country_id);
            }
    
            $managerIds = $managerIdsQuery->select('assigned_manager_id')
                                        ->distinct()
                                        ->pluck('assigned_manager_id');
                            
            $managers = \App\Models\User::whereIn('id', $managerIds)->select('id', 'name')->get();
                             
            $recNosQuery = CarProfile::query();
            if ($user->hasRole('manager')) {
                $recNosQuery->where('country_id', $user->country_id)
                            ->where('assigned_manager_id', $user->id);
            }
            $recNos = $recNosQuery
                ->when($request->filled('country_id') && $user->hasRole('admin'), fn($q) => $q->where('country_id', $request->country_id))
                ->when($request->filled('assigned_manager_id'), fn($q) => $q->where('assigned_manager_id', $request->assigned_manager_id))
                ->select('rec_no')
                ->distinct()
                ->pluck('rec_no');
    

            return view('dashboards.car_profiles.index', compact('carProfiles', 'countries' ,'carStatuses','per_page_value','managers',''));

        }elseif($user->hasRole('manager')) {
            $countries = Country::where('id', $user->country_id)->get();
            $query = CarProfile::query();
            if ($user->hasRole('manager')) {
                $query->where('country_id', $user->country_id)
                    ->where('assigned_manager_id', $user->id);
            }
    
            if ($request->filled('country_id') && $user->hasRole('admin')) {
                $query->where('country_id', $request->country_id);
            }
    
            if($request->filled('assigned_manager_id')){
                $query->where('assigned_manager_id', $request->assigned_manager_id);
            }
    
            if ($request->filled('rec_no')) {
                $query->where('rec_no', $request->rec_no);
            }
    
            if ($request->filled('car_status')) {
                $query->where('car_status', $request->car_status);
            }
            $carProfiles = $query->with(['countryCurrency', 'expenses.currencyInfo', 'country', 'assignedManager'])
                                ->withSum('expenses', 'usd_amount')
                                ->latest()
                                ->paginate($per_page_value);
                                
            $managerQuery = CarProfile::query();
            if ($user->hasRole('manager')) {
                $managerQuery->where('country_id', $user->country_id)
                        ->where('assigned_manager_id', $user->id);
            }
    
            $managerIdsQuery = CarProfile::query();
            if ($user->hasRole('manager')) {
                $managerIdsQuery->where('country_id', $user->country_id)
                                ->where('assigned_manager_id', $user->id);
            }
    
            if ($request->filled('country_id') && $user->hasRole('admin')) {
                $managerIdsQuery->where('country_id', $request->country_id);
            }
    
            $managerIds = $managerIdsQuery->select('assigned_manager_id')
                                        ->distinct()
                                        ->pluck('assigned_manager_id');
                            
            $managers = \App\Models\User::whereIn('id', $managerIds)->select('id', 'name')->get();
                             
            $recNosQuery = CarProfile::query();
            if ($user->hasRole('manager')) {
                $recNosQuery->where('country_id', $user->country_id)
                            ->where('assigned_manager_id', $user->id);
            }
            $recNos = $recNosQuery
                ->when($request->filled('country_id') && $user->hasRole('admin'), fn($q) => $q->where('country_id', $request->country_id))
                ->when($request->filled('assigned_manager_id'), fn($q) => $q->where('assigned_manager_id', $request->assigned_manager_id))
                ->select('rec_no')
                ->distinct()
                ->pluck('rec_no');
            
            return view('dashboards.car_profiles.index', compact('carProfiles', 'countries' ,'carStatuses','per_page_value','managers','recNos'));


        }elseif($user->hasRole('salesperson')) {
            // $countries = Country::all();
            // $carProfiles = CarProfile::where('country_id', $user->country_id)->where('car_status', 'ready_for_sale')->latest()->paginate(10);
            // return view('dashboards.car_profiles.index', compact('carProfiles', 'countries'));
        } else {
            abort(403);
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $countries = Country::all();
        $defaultCountry = $countries->firstWhere('id', 1);

        $currencyType = $defaultCountry->currency_type ?? 'JPY';
        $conversionRate = $defaultCountry->currency_rate ?? 1;

        $accounts = $user->hasRole('manager') ? BankCashAccount::where('country_id', $user->country_id)->get() : BankCashAccount::all();
        
        return view('dashboards.car_profiles.create', compact('countries', 'currencyType', 'conversionRate', 'accounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'located_yard' => 'required|string|max:255',
            'grade' => 'required|string|max:100',
            'seats' => 'required|integer|min:1|max:99',
            'chassis' => 'required|string|max:255',
            'shift' => 'required|string|max:100',
            'mileage' => 'required|integer|min:0',
            'engine_cc' => 'required|numeric|min:0',
            'dimension' => 'nullable|string|max:255',
            'm3' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'fuel' => 'required|string|max:100',
            'max_loading' => 'required|string|max:100',
            'country_id' => 'required|exists:countries,id',
            'car_status' => 'required|string|in:new_arrival,in_transit,sold,ready_for_sale,under_maintenance',
            'sale_price' => 'nullable|numeric|min:0',
            'account_id' => 'nullable|exists:bank_cash_accounts,id',
            'car_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'car_gallery.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'car_video' => 'nullable|file|mimetypes:video/mp4|max:10240',
        ]);

        $data['located_yard'] = $request->input('new_yard') ?: $request->input('located_yard');

        if ($request->hasFile('car_image')) {
            $data['car_image'] = $request->file('car_image')->store('car_images', 'public');
        }

        if ($request->hasFile('car_video')) {
            $data['car_video'] = $request->file('car_video')->store('car_videos', 'public');
        }
        
        // Generate auto rec_no
        $last = CarProfile::orderBy('id', 'desc')->first();
        $nextId = $last ? $last->id + 1 : 1;
        $data['rec_no'] = 'Malik-auto#' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $carProfile=CarProfile::create($data);
        
        if ($request->hasFile('car_gallery')) {
            foreach ($request->file('car_gallery') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('storage/gallery_images'); // full path like /var/www/project/public/storage/gallery_images
        
                // Create the directory if it doesn't exist
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
        
                $file->move($destinationPath, $filename);
        
                // Save path as relative to public
                $carProfile->galleryImages()->create([
                    'path' => 'storage/gallery_images/' . $filename
                ]);
            }
        }

        

        return redirect()->route('car-profiles.index')->with('success', 'Car Profile created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $carProfile = CarProfile::findOrFail($id);
        return view('dashboards.car_profiles.show', compact('carProfile'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = auth()->user();
        
        $countries = match (true) {
            $user->hasRole('admin') => Country::all(),
            $user->hasRole('manager') => Country::where('id', $user->country_id)->get(),
            default => collect(), // Return empty collection for other roles
        };

        $carProfile = CarProfile::findOrFail($id);

        $country = $carProfile->country;
        $currencyType = $country->currency_type ?? 'JPY';
        $conversionRate = $country->currency_rate ?? 1;

        $accounts = $user->hasRole('manager') ? BankCashAccount::where('country_id', $user->country_id)->get() : BankCashAccount::all();

        return view('dashboards.car_profiles.edit', compact('carProfile', 'countries', 'currencyType', 'conversionRate', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    
    public function update(Request $request, CarProfile $carProfile)
    {
        if ($request->located_yard === '__add_new__') {
            $request->merge(['located_yard' => $request->new_yard]);
        }

        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        $isManager = $user->hasRole('manager');

        // Validation rules
        $rules = [
            'located_yard' => 'required|string|max:255',
            'grade' => 'required|string|max:100',
            'seats' => 'required|integer|min:1|max:99',
            'chassis' => 'required|string|max:255',
            'shift' => 'required|string|max:100',
            'mileage' => 'required|integer|min:0',
            'engine_cc' => 'required|numeric|min:0',
            'dimension' => 'required|string|max:255',
            'm3' => 'required|numeric|min:0',
            'fuel' => 'required|string|max:100',
            'max_loading' => 'required|string|max:100',
            'country_id' => 'required|exists:countries,id',
            'car_status' => 'required|string|in:new_arrival,in_transit,sold,ready_for_sale,under_maintenance',
            'sale_price' => 'nullable|numeric|min:0',
            'sold_price' => 'nullable|numeric|min:0',
            'account_id' => 'nullable|exists:bank_cash_accounts,id',
            'car_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'car_video' => 'nullable|file|mimetypes:video/mp4|max:10240',
        ];

        // Role-based price validation
        if ($isAdmin) {
            $rules['price'] = 'required|numeric|min:0';
        }

        if ($request->car_status === 'ready_for_sale' && empty($carProfile->sale_price)) {
            $rules['sale_price'] = 'required|numeric|min:0';
        }

        if ($request->car_status === 'sold') {
            $rules['sold_price'] = 'required|numeric|min:0';
        }

        $data = $request->validate($rules);

        // Handle image & video upload
        if ($request->hasFile('car_image')) {
            $data['car_image'] = $request->file('car_image')->store('car_images', 'public');
        }

        if ($request->hasFile('car_video')) {
            $data['car_video'] = $request->file('car_video')->store('car_videos', 'public');
        }

        $fromStatus = $carProfile->car_status;
        $toStatus = $request->car_status;

        // Manager handling SOLD logic
        if ($isManager && $toStatus === 'sold') {
            if ($request->sold_price == $carProfile->sale_price) {
                $data['car_status'] = 'sold';
                $data['sold_price'] = $request->sold_price;
                $data['sold_status'] = 'approved';
                $data['admin_approved_at'] = now();
                $data['account_id'] = $request->account_id ?? null;
                $this->calculateSoldPrice($request, $carProfile);
            } else {
                $data['car_status'] = 'pending_sold';
                $data['suggested_sold_price'] = $request->sold_price;
                $data['sold_price'] = null;
                $data['sold_status'] = 'pending_sold';
                $data['admin_approved_at'] = null;
            }
        }

        // Admin can directly marks as sold
        if ($isAdmin && $toStatus === 'sold') {
            $data['sold_price'] = $request->sold_price;
            $data['suggested_sold_price'] = null;
            $data['sold_status'] = 'approved';
            $data['admin_approved_at'] = now();
            $data['account_id'] = $request->account_id ?? null;
            $this->calculateSoldPrice($request, $carProfile);
        }

        // Handle clearing fields on status change
        if ($isAdmin || $isManager) {
            if ($fromStatus === 'ready_for_sale' && $fromStatus !== $toStatus && !in_array($toStatus, ['under_maintenance', 'sold'])) {
                if ($request->input('confirm_clear_sale') === '1') {
                    $data['sale_price'] = null;
                } else {
                    return back()->with('error', 'Sale price confirmation required.');
                }
            }

            // if ($fromStatus === 'sold' && $fromStatus !== $toStatus && $toStatus !== 'sold') {
            if ($fromStatus === 'sold' && $toStatus !== 'sold') {
                if ($request->input('confirm_clear_both') === '1') {
                    $data['sold_price'] = null;
                    $data['sold_status'] = null;
                    $data['admin_approved_at'] = null;
                } else {
                    return back()->with('error', 'Sold price confirmation required.');
                }
            }
        }

        $carProfile->update($data);

        return redirect()->route('car-profiles.index')->with('success', 'Car Profile updated successfully.');
    }

    private function calculateSoldPrice(Request $request, CarProfile $carProfile)
    {
        $account = BankCashAccount::find($request->account_id);
        if ($account) {
            $currency_rate = Country::where('currency_type',$account->currency_type)->value('currency_rate');
            $calculated_sold_price = $request->sold_price * $currency_rate;
        
            AccountTransaction::create([
                'account_id' => $account->id,
                'transaction_type' => 'Car Sold',
                'reference_id' => $carProfile->id,
                'amount' => $request->sold_price,
                'opening_balance' => $account->opening_balance,
                'closing_balance' => $account->opening_balance + $calculated_sold_price,
                'currency_type' => $account->currency_type,
                'created_by' => auth()->id(),
            ]);
            $account->opening_balance += $calculated_sold_price;
            $account->save();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $carProfile = CarProfile::findOrFail($id);
        // Soft-delete all related expenses (if any)
        $carProfile->expenses()->whereNull('deleted_at')->update(['deleted_at' => now()]);
        $carProfile->delete();
        return redirect()->route('car-profiles.index')->with('success', 'Car profile deleted successfully!');
    }

    public function transferToCountry(Request $request, CarProfile $carProfile)
    {
        
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'assigned_manager_id' => 'required|exists:users,id',
            'transit_expense' => 'nullable|numeric|min:0',
            'transit_expense_in_usd' => 'nullable|numeric|min:0',
            'currency_type' => 'nullable|string|max:10',
            'account_id' => 'nullable|exists:bank_cash_accounts,id',
        ]);

        $account = BankCashAccount::find($request->account_id);

        if ($account && $request->transit_expense > 0) {
            if ($account->opening_balance < $request->transit_expense) {
                return response()->json(['error' => 'Insufficient balance in selected account.'], 400);
            }

            AccountTransaction::create([
                'account_id' => $account->id,
                'transaction_type' => 'Car Transfer',
                'reference_id' => $carProfile->id,
                'amount' => $request->transit_expense,
                'opening_balance' => $account->opening_balance,
                'closing_balance' => $account->opening_balance - $request->transit_expense,
                'currency_type' => $account->currency_type,
                'created_by' => auth()->id(),
            ]);

            $account->opening_balance -= $request->transit_expense;
            $account->save();
        }
        $currency_rate = Country::where('currency_type',$request->currency_type)->value('currency_rate');
        
        $carProfile->update([
            'country_id' => $request->country_id,
            'assigned_manager_id' => $request->assigned_manager_id,
            'transit_expense' => $request->transit_expense,
            'transit_expense_in_usd' => $request->transit_expense / $currency_rate,
            'currency_type' => $request->currency_type,
            'car_status' => 'in_transit', 
            'account_id' => $request->account_id,
        ]);

        CarExpense::create([
            'car_profile_id' => $carProfile->id,
            'stock_number' => $carProfile->rec_no,
            'expenses_for' => 'Transit Expense',
            'amount' => $request->transit_expense,
            'currency' => $request->currency_type,
            'usd_amount' => $carProfile->transit_expense_in_usd,
            'created_by' => auth()->id(),
            'account_id' => $account->id,
        ]);

        return response()->json(['success' => 'Car transfered successfully.']);
    }

    public function filter(Request $request)
    {
        $user = auth()->user();
        $countries = Country::all();
        $query = CarProfile::query();

        if ($user->hasRole('manager')) {
            $query->where('country_id', $user->country_id)
                ->where('assigned_manager_id', $user->id);
        }

        if ($request->filled('country_id') && $user->hasRole('admin')) {
            $query->where('country_id', $request->country_id);
        }

        if($request->filled('assigned_manager_id')){
            $query->where('assigned_manager_id', $request->assigned_manager_id);
        }

        // if ($request->filled('located_yard')) {
        //     $query->where('located_yard', $request->located_yard);
        // }

        if ($request->filled('rec_no')) {
            $query->where('rec_no', $request->rec_no);
        }

        if ($request->filled('car_status')) {
            $query->where('car_status', $request->car_status);
        }
        $carProfiles = $query->with(['countryCurrency', 'expenses.currencyInfo', 'country', 'assignedManager'])
                            ->withSum('expenses', 'usd_amount')
                            ->latest()
                            ->paginate(5);

        $managerQuery = CarProfile::query();
        if ($user->hasRole('manager')) {
            $managerQuery->where('country_id', $user->country_id)
                    ->where('assigned_manager_id', $user->id);
        }

        $managerIdsQuery = CarProfile::query();
        if ($user->hasRole('manager')) {
            $managerIdsQuery->where('country_id', $user->country_id)
                            ->where('assigned_manager_id', $user->id);
        }

        if ($request->filled('country_id') && $user->hasRole('admin')) {
            $managerIdsQuery->where('country_id', $request->country_id);
        }

        $managerIds = $managerIdsQuery->select('assigned_manager_id')
                                    ->distinct()
                                    ->pluck('assigned_manager_id');
                                    
        $managers = \App\Models\User::whereIn('id', $managerIds)->select('id', 'name')->get();
        
        // $yardsQuery = CarProfile::query();
        // if ($user->hasRole('manager')) {
        //     $yardsQuery->where('country_id', $user->country_id)
        //             ->where('assigned_manager_id', $user->id);
        // }
        // $yards = $yardsQuery
        //     ->when($request->filled('country_id') && $user->hasRole('admin'), fn($q) => $q->where('country_id', $request->country_id))
        //     ->select('located_yard')
        //     ->distinct()
        //     ->pluck('located_yard');

        $recNosQuery = CarProfile::query();
        if ($user->hasRole('manager')) {
            $recNosQuery->where('country_id', $user->country_id)
                        ->where('assigned_manager_id', $user->id);
        }
        $recNos = $recNosQuery
            ->when($request->filled('country_id') && $user->hasRole('admin'), fn($q) => $q->where('country_id', $request->country_id))
            ->when($request->filled('assigned_manager_id'), fn($q) => $q->where('assigned_manager_id', $request->assigned_manager_id))
            // ->when($request->filled('located_yard'), fn($q) => $q->where('located_yard', $request->located_yard))
            ->select('rec_no')
            ->distinct()
            ->pluck('rec_no');

        $html = view('dashboards.car_profiles.partials.list', compact('carProfiles', 'countries'))->render();

        return response()->json([
            'html' => $html,
            // 'yards' => $yards,
            'managers' => $managers,
            'recNos' => $recNos,
        ]);
    }

    // old function
    // public function filter(Request $request)
    // {
    //     $countries = Country::all();
    //     $query = CarProfile::query();

    //     if ($request->filled('country_id')) {
    //         $query->where('country_id', $request->country_id);
    //     }

    //     if ($request->filled('located_yard')) {
    //         $query->where('located_yard', $request->located_yard);
    //     }

    //     if ($request->filled('rec_no')) {
    //         $query->where('rec_no', $request->rec_no);
    //     }

    //     $carProfiles = $query->with(['countryCurrency', 'expenses.currencyInfo', 'country'])->latest()->paginate(5);

    //     // Dependent dropdown values
    //     $yards = CarProfile::query()
    //         ->when($request->filled('country_id'), fn($q) => $q->where('country_id', $request->country_id))
    //         ->select('located_yard')->distinct()->pluck('located_yard');

    //     $recNos = CarProfile::query()
    //         ->when($request->filled('country_id'), fn($q) => $q->where('country_id', $request->country_id))
    //         ->when($request->filled('located_yard'), fn($q) => $q->where('located_yard', $request->located_yard))
    //         ->select('rec_no')->distinct()->pluck('rec_no');

    //     $html = view('dashboards.car_profiles.partials.list', compact('carProfiles', 'countries'))->render();

    //     return response()->json([
    //         'html' => $html,
    //         'yards' => $yards,
    //         'recNos' => $recNos,
    //     ]);
    // }

    public function getYardsByCountry($countryId)
    {
        $yards = CarProfile::where('country_id', $countryId)
            ->distinct()
            ->pluck('located_yard');

        return response()->json(['yards' => $yards]);
    }

    public function markReceived(CarProfile $car)
    {
        // Only managers can mark cars as received
        if (auth()->user()->hasRole('manager') && $car->car_status === 'in_transit') {
            $car->update(['car_status' => 'under_maintenance']);

            return redirect()->back()->with('success', 'Car marked as Received. Full options are now available.');
        }

        return redirect()->back()->with('error', 'Action not allowed.');
    }
    
    public function pendingSold()
    {
        $pendingCars = CarProfile::where('car_status', 'pending_sold')
            ->where('sold_status', 'pending_sold')
            ->with('assignedManager')->withSum('expenses', 'usd_amount')
            ->latest()
            ->get();

        return view('dashboards.car_profiles.pending_sold', compact('pendingCars'));
    }

    public function soldPriceApproval(Request $request, $id)
    {
        $action = $request->input('action');
        $car = CarProfile::where('id', $id)
            ->where('car_status', 'pending_sold')
            ->where('sold_status', 'pending_sold')
            ->firstOrFail();

        if ($action === 'approve') {
            $car->update([
                'car_status' => 'sold',
                'sold_price' => $car->suggested_sold_price,
                'sold_status' => 'approved',
                'admin_approved_at' => now(),
                'suggested_sold_price' => $car->suggested_sold_price,
            ]);

            return back()->with('success', 'Car marked as sold and approved successfully.');
        }

        if ($action === 'reject') {
            $car->update([
                'sold_status' => 'rejected',
                'car_status' => 'under_maintenance',
                'admin_approved_at' => null,
            ]);

            return back()->with('error', 'Sold price has been rejected.');
        }

        return back()->with('error', 'Invalid action.');
    }

}
