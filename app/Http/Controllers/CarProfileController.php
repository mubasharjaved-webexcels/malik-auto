<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarProfile;
use App\Models\Country;
use App\Models\BankCashAccount;
use App\Models\AccountTransaction;
use App\Models\CarExpense;
use Illuminate\Validation\Rule;

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
    

            return view('dashboards.car_profiles.index', compact('carProfiles', 'countries' ,'carStatuses','per_page_value','managers','recNos'));

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
            'car_name' => 'nullable|string|max:255',
            'located_yard' => 'nullable|string|max:255',
            'grade' => 'required|string|max:100',
            'seats' => 'required|integer|min:1|max:99',
            'chassis' => 'required|string|max:255|unique:car_profiles,chassis',
            'shift' => 'required|string|max:100',
            'mileage' => 'required|integer|min:0',
            'engine_cc' => 'required|numeric|min:0',
            'dimension' => 'nullable|string|max:255',
            // 'm3' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'fuel' => 'required|string|max:100',
            'max_loading' => 'required|string|max:100',
            'country_id' => 'required|exists:countries,id',
            'car_status' => 'required|string|in:new_arrival,in_transit,sold,ready_for_sale,under_maintenance,booked',
            'sale_price' => 'nullable|numeric|min:0',
            'booking_price' => 'nullable|numeric|min:0',
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
            'car_name' => 'nullable|string|max:255',
            'located_yard' => 'nullable|string|max:255',
            'grade' => 'required|string|max:100',
            'seats' => 'required|integer|min:1|max:99',
            'chassis' => [
                'required',
                'string',
                'max:255',
                Rule::unique('car_profiles', 'chassis')->ignore($carProfile->id),
            ],
            'shift' => 'required|string|max:100',
            'mileage' => 'required|integer|min:0',
            'engine_cc' => 'required|numeric|min:0',
            'dimension' => 'required|string|max:255',
            // 'm3' => 'nullable|numeric|min:0',
            'fuel' => 'required|string|max:100',
            'max_loading' => 'required|string|max:100',
            'country_id' => 'required|exists:countries,id',
            'car_status' => 'required|string|in:new_arrival,in_transit,sold,ready_for_sale,under_maintenance,rejected,booked',
            'sale_price' => 'nullable|numeric|min:0',
            'booking_price' => 'nullable|numeric|min:0',
            'sold_price' => 'nullable|numeric|min:0',
            'account_id' => 'nullable|exists:bank_cash_accounts,id',
            'car_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'car_gallery.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
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
            $data['car_status'] = 'pending_sold';
            $data['suggested_sold_price'] = $request->sold_price;
            $data['sold_price'] = null;
            $data['admin_approved_at'] = null;
        }

        if ($isManager && $toStatus === 'booked') {
            $data['is_booked'] = 0;
        }

        if ($isAdmin && $toStatus === 'sold') {
            $data['sold_price'] = $request->sold_price;
            $data['suggested_sold_price'] = null;
            $data['car_status'] = 'sold';
            $data['admin_approved_at'] = now();
            $data['account_id'] = $request->account_id ?? null;
            $this->calculateTransactionPrices($request, $carProfile, $request->sold_price, 'sold');
        }

        // Handle clearing fields on status change
        if ($isAdmin || $isManager) {
            if ($fromStatus === 'ready_for_sale' && $fromStatus !== $toStatus && !in_array($toStatus, ['under_maintenance', 'sold', 'booked'])) {
                if ($request->input('confirm_clear_sale') === '1') {
                    $data['sale_price'] = null;
                    $data['booking_price'] = null;
                } else {
                    return back()->with('error', 'Sale price confirmation required.');
                }
            }

            // if ($fromStatus === 'sold' && $fromStatus !== $toStatus && $toStatus !== 'sold') {
            if ($fromStatus === 'sold' && $toStatus !== 'sold') {
                if ($request->input('confirm_clear_both') === '1') {
                    $data['sold_price'] = null;
                    $data['car_status'] = $toStatus;
                    $data['suggested_sold_price'] = null;
                    $data['admin_approved_at'] = null;
                } else {
                    return back()->with('error', 'Sold price confirmation required.');
                }
            }
        }

        $carProfile->update($data);

        return redirect()->route('car-profiles.index')->with('success', 'Car Profile updated successfully.');
    }

    private function calculateTransactionPrices(Request $request, CarProfile $carProfile, float $amount, string $type)
    {
        $account = BankCashAccount::find($request->account_id);
        if ($account) {
            $countryInfo = Country::where('id',$account->country_id)->select('currency_rate', 'currency_type')->first();
            $calculated_sold_price = $amount * $countryInfo->currency_rate;
        
            AccountTransaction::create([
                'account_id' => $account->id,
                'transaction_type' => $carProfile->rec_no." {$type} by ".$carProfile->assignedManager->name,
                'reference_id' => $carProfile->id,
                'amount' => $amount,
                'flow_type' => 'credit',
                'opening_balance' => $account->opening_balance,
                'closing_balance' => $account->opening_balance + $calculated_sold_price,
                'currency_type' => $countryInfo->currency_type,
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
        // $currencyType = Country::where('id',$account->country_id)->value('currency_type');

        if ($account && $request->transit_expense > 0) {
            if ($account->opening_balance < $request->transit_expense) {
                return response()->json(['error' => 'Insufficient balance in selected account.'], 400);
            }

            AccountTransaction::create([
                'account_id' => $account->id,
                'transaction_type' => 'Transit Expense',
                'reference_id' => $carProfile->id,
                'amount' => $request->transit_expense,
                'flow_type' => 'debit',
                'opening_balance' => $account->opening_balance,
                'closing_balance' => $account->opening_balance - $request->transit_expense,
                'currency_type' => $request->currency_type,
                'created_by' => auth()->id(),
            ]);

            $account->opening_balance -= $request->transit_expense;
            $account->save();
        }
        $currency_rate = Country::where('currency_type',$request->currency_type)->value('currency_rate');
        
        $carProfile->update([
            'country_id' => $request->country_id,
            'assigned_manager_id' => $request->assigned_manager_id,
            'car_status' => 'in_transit', 
            // 'transit_expense' => $request->transit_expense,
            // 'transit_expense_in_usd' => $request->transit_expense / $currency_rate,
            // 'currency_type' => $request->currency_type,
            // 'account_id' => $request->account_id,
        ]);

        CarExpense::create([
            'car_profile_id' => $carProfile->id,
            'stock_number' => $carProfile->rec_no,
            'expenses_for' => 'Transit Expense',
            'amount' => $request->transit_expense,
            'currency' => $request->currency_type,
            'usd_amount' => $request->transit_expense / $currency_rate,
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

        $html = view('dashboards.car_profiles.partials.list', compact('carProfiles', 'countries'))->render();

        return response()->json([
            'html' => $html,
            'managers' => $managers,
            'recNos' => $recNos,
        ]);
    }

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
        if (auth()->user()->hasRole('manager') && $car->car_status === 'in_transit' || $car->car_status === 'rejected') {
            $car->update(['car_status' => 'under_maintenance']);

            return redirect()->back()->with('success', 'Successfully acknowledged. Full options are now available.');
        }

        return redirect()->back()->with('error', 'Action not allowed.');
    }
    
    public function getPendingRequests()
    {
        $getPendingRequests = CarProfile::where('car_status', 'pending_sold')
            ->orWhere(function ($query) {
                $query->where('car_status', 'booked')
                    ->where('is_booked', 0);
            })
            ->with('assignedManager', 'country')->withSum('expenses', 'usd_amount')
            ->latest()
            ->get();

        $bankAccounts = BankCashAccount::where('status', 'active')->get();

        return view('dashboards.car_profiles.pending_sold', compact('getPendingRequests', 'bankAccounts'));
    }

    public function processPendingRequestAction(Request $request, $id)
    {
        $action = $request->input('action');
        $type = $request->input('type'); 

        $allowedStatuses = [
            'sold' => 'pending_sold',
            'booked' => 'booked',
        ];

        if (!isset($allowedStatuses[$type])) {
            return back()->with('error', 'Invalid approval type.');
        }

        $car = CarProfile::where('id', $id)
            ->where('car_status', $allowedStatuses[$type])
            ->with('assignedManager')
            ->firstOrFail();
            // ->with('assignedManager')->where('car_status', 'pending_sold')->firstOrFail();

        if ($type === 'sold') {
            if ($action === 'approve') {
                $validated = $request->validate([
                    'account_id' => 'required|exists:bank_cash_accounts,id',
                ]);

                if ($car->is_booked === 1 && $car->booking_price !== null) {
                    $approvedAmount = $car->suggested_sold_price - $car->booking_price;
                }else{
                    $approvedAmount = $car->suggested_sold_price;
                }

                $this->calculateTransactionPrices($request, $car, $approvedAmount, 'sold');

                $car->update([
                    'car_status' => 'sold',
                    'sold_price' => $approvedAmount,
                    'admin_approved_at' => now(),
                ]);

                return back()->with('success', 'Car marked as sold and approved successfully.');
            }

            if ($action === 'reject') {
                $car->update([
                    'car_status' => 'rejected',
                    'admin_approved_at' => null,
                ]);

                return back()->with('error', 'Sold price has been rejected.');
            }
        }
        
        if ($type === 'booked') {
            if ($action === 'approve') {
                $validated = $request->validate([
                    'account_id' => 'required|exists:bank_cash_accounts,id',
                ]);

                $this->calculateTransactionPrices($request, $car, $car->booking_price, 'booked');
                $car->update([
                    'is_booked' => 1,
                ]);
                return back()->with('success', 'Car booking price approved.');
            }
            if ($action === 'reject') {
                $car->update([
                    'car_status' => 'under_maintenance',
                    'is_booked' => 0,
                ]);

                return back()->with('error', 'Booking price has been rejected.');
            }
        }

        return back()->with('error', 'Invalid action.');
    }

    public function checkChassis(Request $request)
    {
        $request->validate([
            'chassis' => 'required|string|max:255',
        ]);

        $query = CarProfile::where('chassis', $request->chassis);

        if ($request->filled('id')) {
            $query->where('id', '!=', $request->id);
        }

        $exists = $query->exists();

        return response()->json(['exists' => $exists]);
    }

    public function search(Request $request)
    {
        $user = auth()->user();
        $per_page_value = $request->input('per_page', 25);

        $query = CarProfile::query();

        if ($user->hasRole('admin')) {
            $countries = Country::all();
        } elseif ($user->hasRole('manager')) {
            $query->where('country_id', $user->country_id)
                ->where('assigned_manager_id', $user->id);

            $countries = Country::where('id', $user->country_id)->get();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rec_no', 'like', "%{$search}%")
                ->orWhere('chassis', 'like', "%{$search}%")
                ->orWhere('car_status', 'like', "%{$search}%");
            });
        }

        $carProfiles = $query->with(['countryCurrency', 'expenses.currencyInfo', 'country', 'assignedManager'])
                            ->withSum('expenses', 'usd_amount')->latest()
                            ->paginate($per_page_value);

        if ($request->ajax()) {
            return view('dashboards.car_profiles.partials.list', compact('carProfiles', 'per_page_value', 'countries'))->render();
        }

        return view('dashboards.car_profiles.index', compact('carProfiles', 'per_page_value', 'countries'));
    }
    
}
