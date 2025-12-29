<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CarProfile;
use App\Models\Country;
use App\Models\BankCashAccount;
use App\Models\AccountTransaction;
use App\Models\CarExpense;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CarProfileController extends Controller
{
    use ApiResponse;

    /**
     * Get all car profiles with filters
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $perPage = $request->input('per_page', 25);

            if (!$user->hasRole(['admin', 'manager'])) {
                return $this->error('Unauthorized access', 403);
            }

            $countries = $user->hasRole('admin')
                ? Country::all()
                : Country::where('id', $user->country_id)->get();

            $query = CarProfile::query();

            // Apply role-based filters
            if ($user->hasRole('manager')) {
                $query->where('country_id', $user->country_id)
                    ->where('assigned_manager_id', $user->id);
            }

            // Apply request filters
            if ($request->filled('country_id')) {
                $query->where('country_id', $request->country_id);
                if ($user->hasRole('admin')) {
                    $query->where('car_status', '!=', 'sold');
                }
            }

            if ($request->filled('assigned_manager_id')) {
                $query->where('assigned_manager_id', $request->assigned_manager_id);
            }

            if ($request->filled('rec_no')) {
                $query->where('rec_no', $request->rec_no);
            }

            if ($request->filled('car_status')) {
                $query->where('car_status', $request->car_status);
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
                ->withSum('expenses', 'usd_amount')
                ->latest()
                ->paginate($perPage);

            // Get car statuses from enum
            $rawType = \DB::selectOne("SHOW COLUMNS FROM car_profiles WHERE Field = 'car_status'")->Type;
            preg_match('/^enum\((.*)\)$/', $rawType, $matches);
            $carStatuses = isset($matches[1]) ? array_map(fn($v) => trim($v, "'"), explode(',', $matches[1])) : [];

            // Get managers
            $managerIdsQuery = CarProfile::query();
            if ($user->hasRole('manager')) {
                $managerIdsQuery->where('country_id', $user->country_id)
                    ->where('assigned_manager_id', $user->id);
            }
            if ($request->filled('country_id') && $user->hasRole('admin')) {
                $managerIdsQuery->where('country_id', $request->country_id);
            }

            $managerIds = $managerIdsQuery->select('assigned_manager_id')->distinct()->pluck('assigned_manager_id');
            $managers = \App\Models\User::whereIn('id', $managerIds)->select('id', 'name')->get();

            // Get rec numbers
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

            return $this->success([
                'car_profiles' => $carProfiles,
                'countries' => $countries,
                'car_statuses' => $carStatuses,
                'managers' => $managers,
                'rec_nos' => $recNos,
            ], 'Car profiles retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve car profiles', 500, $e->getMessage());
        }
    }

    /**
     * Get data for create form
     */
    public function createData()
    {
        try {
            $user = auth()->user();
            $countries = Country::all();
            $defaultCountry = $countries->firstWhere('id', 1);

            $accounts = $user->hasRole('manager')
                ? BankCashAccount::where('country_id', $user->country_id)->get()
                : BankCashAccount::all();

            return $this->success([
                'countries' => $countries,
                'currency_type' => $defaultCountry->currency_type ?? 'JPY',
                'conversion_rate' => $defaultCountry->currency_rate ?? 1,
                'accounts' => $accounts,
            ], 'Create data retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve create data', 500, $e->getMessage());
        }
    }

    /**
     * Store a new car profile
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'car_name' => 'nullable|string|max:255',
                'located_yard' => 'nullable|string|max:255',
                'grade' => 'required|string|max:100',
                'seats' => 'required|integer|min:1|max:99',
                'chassis' => 'required|string|max:255|unique:car_profiles,chassis',
                'shift' => 'required|string|max:100',
                'mileage' => 'required|integer|min:0',
                'engine_cc' => 'required|numeric|min:0',
                'dimension' => 'nullable|string|max:255',
                'price' => 'required|numeric|min:0',
                'fuel' => 'required|string|max:100',
                'max_loading' => 'required|numeric|min:0',
                'country_id' => 'required|exists:countries,id',
                'car_status' => 'required|string|in:new_arrival,in_transit,sold,ready_for_sale,under_maintenance,booked',
                'sale_price' => 'nullable|numeric|min:0',
                'booking_price' => 'nullable|numeric|min:0',
                'account_id' => 'nullable|exists:bank_cash_accounts,id',
                'car_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'car_gallery.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
                'car_video' => 'nullable|file|mimetypes:video/mp4|max:10240',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 422, $validator->errors());
            }

            $data = $validator->validated();
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
            $carProfile = CarProfile::create($data);

            // Handle gallery images
            // Handle gallery images (SERVER / STORAGE)
            if ($request->hasFile('car_gallery')) {
                foreach ($request->file('car_gallery') as $file) {

                    $path = $file->store('gallery_images', 'public');
                    // storage/app/public/gallery_images

                    $carProfile->galleryImages()->create([
                        'path' => $path // gallery_images/filename.jpg
                    ]);
                }
            }


            return $this->success($carProfile->load('galleryImages'), 'Car profile created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create car profile', 500, $e->getMessage());
        }
    }

    /**
     * Get single car profile
     */
    public function show($car_profile)
    {
        try {
            $carProfile = CarProfile::with(['country', 'assignedManager', 'expenses', 'galleryImages'])
                ->withSum('expenses', 'usd_amount')
                ->findOrFail($car_profile);

            return $this->success($carProfile, 'Car profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Car profile not found', 404);
        }
    }
    /**
     * Update car profile
     */
    public function update(Request $request, $car_profile)
    {



        try {
            $carProfile = CarProfile::findOrFail($car_profile);
            $user = auth()->user();
            $isAdmin = $user->hasRole('admin');
            $isManager = $user->hasRole('manager');

            if ($request->located_yard === '__add_new__') {
                $request->merge(['located_yard' => $request->new_yard]);
            }
            $rules = [
                'car_name' => 'nullable|string|max:255',
                'located_yard' => 'nullable|string|max:255',
                'grade' => 'required|string|max:100',
                'seats' => 'required|integer|min:1|max:99',
                'chassis' => ['required', 'string', 'max:255', Rule::unique('car_profiles', 'chassis')->ignore($car_profile)],
                'shift' => 'required|string|max:100',
                'mileage' => 'required|integer|min:0',
                'engine_cc' => 'required|numeric|min:0',
                'dimension' => 'required|string|max:255',
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

            if ($isAdmin) {
                $rules['price'] = 'required|numeric|min:0';
            }

            if ($request->car_status === 'ready_for_sale' && empty($carProfile->sale_price)) {
                $rules['sale_price'] = 'required|numeric|min:0';
            }

            if ($request->car_status === 'sold') {
                $rules['sold_price'] = 'required|numeric|min:0';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->error('Validation failed', 422, $validator->errors());
            }

            $data = $validator->validated();

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
                        return $this->error('Sale price confirmation required', 400);
                    }
                }

                if ($fromStatus === 'sold' && $toStatus !== 'sold') {
                    if ($request->input('confirm_clear_both') === '1') {
                        $data['sold_price'] = null;
                        $data['car_status'] = $toStatus;
                        $data['suggested_sold_price'] = null;
                        $data['admin_approved_at'] = null;
                    } else {
                        return $this->error('Sold price confirmation required', 400);
                    }
                }
            }

            $carProfile->update($data);

            return $this->success($carProfile->load('galleryImages'), 'Car profile updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update car profile', 500, $e->getMessage());
        }
    }

    /**
     * Delete car profile
     */
    public function destroy($id)
    {
        try {
            $carProfile = CarProfile::findOrFail($id);
            $carProfile->expenses()->whereNull('deleted_at')->update(['deleted_at' => now()]);
            $carProfile->delete();

            return $this->success(null, 'Car profile deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete car profile', 500, $e->getMessage());
        }
    }

    /**
     * Transfer car to country
     */
    public function transferToCountry(Request $request, $id)
    {
        try {
            $carProfile = CarProfile::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'country_id' => 'required|exists:countries,id',
                'assigned_manager_id' => 'required|exists:users,id',
                'transit_expense' => 'nullable|numeric|min:0',
                'transit_expense_in_usd' => 'nullable|numeric|min:0',
                'currency_type' => 'nullable|string|max:10',
                'account_id' => 'nullable|exists:bank_cash_accounts,id',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 422, $validator->errors());
            }

            $account = BankCashAccount::find($request->account_id);

            if ($account && $request->transit_expense > 0) {
                if ($account->opening_balance < $request->transit_expense) {
                    return $this->error('Insufficient balance in selected account', 400);
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

            $currency_rate = Country::where('currency_type', $request->currency_type)->value('currency_rate');

            $carProfile->update([
                'country_id' => $request->country_id,
                'assigned_manager_id' => $request->assigned_manager_id,
                'car_status' => 'in_transit',
            ]);

            CarExpense::create([
                'car_profile_id' => $carProfile->id,
                'stock_number' => $carProfile->rec_no,
                'expenses_for' => 'Transit Expense',
                'amount' => $request->transit_expense,
                'currency' => $request->currency_type,
                'usd_amount' => $request->transit_expense / $currency_rate,
                'created_by' => auth()->id(),
                'account_id' => $account->id ?? null,
            ]);

            return $this->success($carProfile, 'Car transferred successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to transfer car', 500, $e->getMessage());
        }
    }

    /**
     * Mark car as received
     */
    public function markReceived($id)
    {
        try {
            $car = CarProfile::findOrFail($id);

            if (auth()->user()->hasRole('manager') && in_array($car->car_status, ['in_transit', 'rejected'])) {
                $car->update(['car_status' => 'under_maintenance']);
                return $this->success($car, 'Successfully acknowledged. Full options are now available.');
            }

            return $this->error('Action not allowed', 403);
        } catch (\Exception $e) {
            return $this->error('Failed to mark as received', 500, $e->getMessage());
        }
    }

    /**
     * Get pending requests (pending_sold and booked)
     */
    public function getPendingRequests()
    {
        try {
            $getPendingRequests = CarProfile::where('car_status', 'pending_sold')
                ->orWhere(function ($query) {
                    $query->where('car_status', 'booked')
                        ->where('is_booked', 0);
                })
                ->with('assignedManager', 'country')
                ->withSum('expenses', 'usd_amount')
                ->latest()
                ->get();

            $bankAccounts = BankCashAccount::where('status', 'active')->get();

            return $this->success([
                'pending_requests' => $getPendingRequests,
                'bank_accounts' => $bankAccounts,
            ], 'Pending requests retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve pending requests', 500, $e->getMessage());
        }
    }

    /**
     * Process pending request (approve/reject)
     */
    public function processPendingRequest(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:approve,reject',
                'type' => 'required|in:sold,booked',
                'account_id' => 'required_if:action,approve|exists:bank_cash_accounts,id',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 422, $validator->errors());
            }

            $action = $request->input('action');
            $type = $request->input('type');

            $allowedStatuses = [
                'sold' => 'pending_sold',
                'booked' => 'booked',
            ];

            // First check if car exists at all
            $carExists = CarProfile::where('id', $id)->exists();

            if (!$carExists) {
                return $this->error('Car not found', 404);
            }

            // Then check for the specific status
            $car = CarProfile::where('id', $id)
                ->where('car_status', $allowedStatuses[$type])
                ->with('assignedManager')
                ->first();

            if (!$car) {
                $currentStatus = CarProfile::where('id', $id)->value('car_status');
                return $this->error(
                    "Car is not in {$allowedStatuses[$type]} status. Current status: {$currentStatus}",
                    400
                );
            }

            if ($type === 'sold') {
                if ($action === 'approve') {
                    if ($car->suggested_sold_price === null) {
                        return $this->error('Sold price is not set for this car', 400);
                    }

                    $approvedAmount = ($car->is_booked === 1 && $car->booking_price !== null)
                        ? $car->suggested_sold_price - $car->booking_price
                        : $car->suggested_sold_price;

                    $this->calculateTransactionPrices($request, $car, $approvedAmount, 'sold');

                    $car->update([
                        'car_status' => 'sold',
                        'sold_price' => $approvedAmount,
                        'admin_approved_at' => now(),
                    ]);

                    return $this->success($car, 'Car marked as sold and approved successfully');
                }

                if ($action === 'reject') {
                    $car->update([
                        'car_status' => 'rejected',
                        'admin_approved_at' => null,
                    ]);

                    return $this->success($car, 'Sold price has been rejected');
                }
            }

            if ($type === 'booked') {
                if ($action === 'approve') {
                    if ($car->booking_price === null || $car->booking_price <= 0) {
                        return $this->error('Booking price is not set for this car', 400);
                    }

                    $this->calculateTransactionPrices($request, $car, $car->booking_price, 'booked');

                    $car->update(['is_booked' => 1]);

                    return $this->success($car, 'Car booking price approved');
                }

                if ($action === 'reject') {
                    $car->update([
                        'car_status' => 'under_maintenance',
                        'is_booked' => 0,
                    ]);

                    return $this->success($car, 'Booking price has been rejected');
                }
            }

            return $this->error('Invalid action', 400);
        } catch (\Exception $e) {
            return $this->error('Failed to process pending request', 500, $e->getMessage());
        }
    }

    /**
     * Check if chassis exists
     */
    public function checkChassis(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'chassis' => 'required|string|max:255',
                'id' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 422, $validator->errors());
            }

            $query = CarProfile::where('chassis', $request->chassis);

            if ($request->filled('id')) {
                $query->where('id', '!=', $request->id);
            }

            $exists = $query->exists();

            return $this->success(['exists' => $exists], 'Chassis check completed');
        } catch (\Exception $e) {
            return $this->error('Failed to check chassis', 500, $e->getMessage());
        }
    }

    /**
     * Get yards by country
     */
    public function getYardsByCountry($countryId)
    {
        try {
            $yards = CarProfile::where('country_id', $countryId)
                ->distinct()
                ->pluck('located_yard');

            return $this->success(['yards' => $yards], 'Yards retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve yards', 500, $e->getMessage());
        }
    }

    /**
     * Private helper for transaction calculations
     */
    private function calculateTransactionPrices(Request $request, CarProfile $carProfile, ?float $amount, string $type)
    {
        // Add validation at the start
        if ($amount === null || $amount <= 0) {
            throw new \Exception('Invalid amount provided for transaction');
        }

        $account = BankCashAccount::find($request->account_id);

        if (!$account) {
            throw new \Exception('Bank account not found');
        }

        $countryInfo = Country::where('id', $account->country_id)
            ->select('currency_rate', 'currency_type')
            ->first();

        if (!$countryInfo) {
            throw new \Exception('Country information not found for the selected account');
        }

        $managerName = $carProfile->assignedManager?->name ?? 'Unassigned';
        $calculated_sold_price = $amount * $countryInfo->currency_rate;

        AccountTransaction::create([
            'account_id' => $account->id,
            'transaction_type' => $carProfile->rec_no . " {$type} by " . $managerName,
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
