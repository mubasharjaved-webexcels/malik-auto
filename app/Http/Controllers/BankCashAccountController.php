<?php

namespace App\Http\Controllers;
use App\Models\BankCashAccount;
use App\Models\Country;
use App\Models\CarProfile;
use App\Models\AccountTransaction;
use App\Models\CarExpense;
use App\Models\OfficeExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankCashAccountController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'bank');

        $user = auth()->user();
        $query = BankCashAccount::where('type', $type);

        if ($user->hasRole('manager')) {
            $query->where('assigned_to', $user->id);
        }
        $accounts = $query->latest()->get();
        // $accounts = BankCashAccount::where('type', $type)->latest()->get();
        return view('dashboards.accounts.index', compact('accounts', 'type'));
    }

    public function create($type)
    {
        $countries = Country::all();
        $managers = \App\Models\User::role('manager')->select('id', 'name')->get();
        
        return view('dashboards.accounts.create', [
            'type' => $type,
            'countries' => $countries,
            'managers' => $managers,
            'account' => null,
            'isEdit' => false
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:bank,cash',
            'title' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'assigned_to' => 'nullable|exists:users,id',
            // 'currency_type' => 'required|string',
            'yard' => 'nullable|string',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $account = BankCashAccount::create($request->all());

        AccountTransaction::create([
                        'account_id' => $account->id,
                        'transaction_type' => 'Account Created',
                        'reference_id' => null,
                        'amount' => $account->opening_balance,
                        'flow_type' => 'credit',
                        'assigned_to' => $request->assigned_to,
                        'opening_balance' => $account->opening_balance,
                        'closing_balance' => $account->opening_balance,
                        'currency_type' => $account->country->currency_type,
                        'created_by' => auth()->id(),
                    ]);

        return redirect()->route('accounts.index', ['type' => $request->type])
                         ->with('success', ucfirst($request->type).' account added successfully!');
    }

    public function edit(BankCashAccount $account)
    {
        $countries = Country::all();
        $managers = \App\Models\User::role('manager')->select('id', 'name')->get();

        return view('dashboards.accounts.create', [
            'account' => $account,
            'managers' => $managers,
            'type' => $account->type,
            'countries' => $countries,
            'isEdit' => true
        ]);
    }

    public function update(Request $request, BankCashAccount $account)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'assigned_to' => 'nullable|exists:users,id',
            // 'currency_type' => 'required|string',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $oldTitle     = $account->title;
        $oldCountryId = (int) $account->country_id;
        $oldBalance   = $account->opening_balance;
        $oldAssignee  = $account->assigned_to;

        $newTitle     = $validated['title'];
        $newCountryId = (int) $validated['country_id'];
        $newBalance   = $validated['opening_balance'];
        $newAssignee  = $validated['assigned_to'] ?? null;
        
        if ($newBalance > $oldBalance) {
            $flowType = 'credit';
            $amount = $newBalance - $oldBalance;
            $balanceLabel = 'Balance Increased';
        } elseif ($newBalance < $oldBalance) {
            $flowType = 'debit';
            $amount = $oldBalance - $newBalance;
            $balanceLabel = 'Balance Decreased';
        } else {
            $flowType = null;
            $amount = 0;
            $balanceLabel = null;
        }

        $changes = [];
        if ($oldTitle !== $newTitle) {
            $changes[] = 'Title Changed';
        }
        if ($oldCountryId !== $newCountryId) {
            $changes[] = 'Country Changed';
        }
        if ($oldAssignee !== $newAssignee) {
            $assigneeName = \App\Models\User::find($newAssignee)?->name ?? 'No One';
            $changes[] = 'Assigned to '. $assigneeName;
        }
        if ($balanceLabel) {
            $changes[] = $balanceLabel;
        }

        $transactionType = empty($changes) ? 'Account Updated' : implode(' & ', $changes);

        $shouldLog = !empty($changes);

        if ($shouldLog) {
            AccountTransaction::create([
                            'account_id' => $account->id,
                            'transaction_type' => $transactionType,
                            'reference_id' => null,
                            'amount' => $amount,
                            'flow_type' => $flowType,
                            'assigned_to' => $oldAssignee !== $newAssignee ? $newAssignee : null,
                            'opening_balance' => $oldBalance,
                            'closing_balance' => $newBalance,
                            'currency_type' => $account->country->currency_type,
                            'created_by' => auth()->id(),
                        ]);
        }

        $account->update($validated);
        
        return redirect()->route('accounts.index', ['type' => $account->type])
            ->with('success', 'Account updated successfully.');
    }

    public function toggleStatus(BankCashAccount $account)
    {
        $account->status = $account->status === 'active' ? 'inactive' : 'active';
        $account->save();

        return redirect()->back()->with('success', 'Account status updated.');
    }

    public function getAccounts($countryId = null)
    {
        $user = auth()->user();
        $query = BankCashAccount::with('country:id,currency_type')->where('status', 'active');

        if ($user->hasRole('manager')) {
            $query->where('assigned_to', $user->id);
            // $query->where('country_id', $user->country_id);
        }elseif ($countryId && !$user->hasRole('admin')) {
            $query->where('country_id', $countryId);
        }
        $accounts = $query->select('id', 'title', 'country_id')->get();
        $data = $accounts->map(function ($account) {
            return [
                'id' => $account->id,
                'title' => $account->title,
                'currency_type' => optional($account->country)->currency_type,
            ];
        });

        return response()->json($data);
    }

    public function getStatement($accountId, $type)
    {
        $user = auth()->user();
        $account = BankCashAccount::with('country')->findOrFail($accountId);

        $startDate = request('start_date');
        $endDate = request('end_date');

        $query = AccountTransaction::with('user', 'assignedUser')->where('account_id', $accountId);
        if ($user->hasRole('manager')) {
            $query->where('created_by', $user->id);
        }
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $statements = $query->orderBy('created_at', 'desc')->paginate(25);

        $totals = AccountTransaction::where('account_id', $accountId)
                    ->when($startDate, function ($query) use ($startDate) {
                        return $query->where('created_at', '>=', $startDate);
                    })
                    ->when($endDate, function ($query) use ($endDate) {
                        return $query->where('created_at', '<=', $endDate);
                    })
                    ->selectRaw('
                        SUM(CASE WHEN flow_type = "credit" THEN amount ELSE 0 END) as total_credit_amount,
                        SUM(CASE WHEN flow_type = "debit" THEN amount ELSE 0 END) as total_debit_amount,
                        COUNT(CASE WHEN flow_type = "credit" THEN 1 END) as credit_count,
                        COUNT(CASE WHEN flow_type = "debit" THEN 1 END) as debit_count
                    ')
                    ->first();

        $closingBalance = $query->orderBy('created_at', 'desc')->value('closing_balance');

        return view('dashboards.accounts.statement', compact(
            'statements', 
            'account', 
            'type',
            'totals',
            'closingBalance',
            'startDate',
            'endDate'
        ));
    }

    public function journalVoucher()
    {
        $journalVoucher = AccountTransaction::with(['user', 'assignedUser'])
            ->where('is_jv', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('dashboards.accounts.journal-voucher', compact('journalVoucher'));
    }

    public function createVoucher()
    {
        $accounts = BankCashAccount::with('country')->get();
        return view('dashboards.accounts.create-voucher', compact('accounts'));
    }

    public function storeVoucher(Request $request)
    {
        $validated = $request->validate([
            'from_account' => 'required|different:to_account',
            'to_account'   => 'required',
            'amount'       => 'required|numeric|min:0.01',
            'jv_description'  => 'required|string',
        ], [
            'from_account.different' => 'You cannot transfer money into the same account.',
        ]);

        DB::beginTransaction();

        try {
            $fromAccount = BankCashAccount::with('country')->findOrFail($validated['from_account']);
            $toAccount   = BankCashAccount::with('country')->findOrFail($validated['to_account']);
            $amount      = $validated['amount'];

            if ($fromAccount->opening_balance < $amount) {
                DB::rollBack();
                return back()->with('error', 'Insufficient balance in sending account.');
            }

            $convertedAmount = $fromAccount->country_id !== $toAccount->country_id
                ? $this->getConversionAmount($amount, $fromAccount->country, $toAccount->country)
                : $amount;
                
            $randomNumber = rand(100000, 999999);
            $jvNo = 'JV-' . str_pad($randomNumber, 8, '0', STR_PAD_LEFT);
            AccountTransaction::create([
                'account_id'     => $fromAccount->id,
                'transaction_type'=> 'Transfer against '.$jvNo,
                'reference_id'   => $toAccount->id,
                'amount'         => $amount,
                'flow_type'      => 'debit',
                'opening_balance'=> $fromAccount->opening_balance,
                'closing_balance'=> $fromAccount->opening_balance - $amount,
                'currency_type'  => $fromAccount->country->currency_type,
                'is_jv'          => 1,
                'jv_no'          => $jvNo,
                'jv_description' => $validated['jv_description'],
                'created_by'     => auth()->id(),
            ]);

            AccountTransaction::create([
                'account_id'     => $toAccount->id,
                'transaction_type'=> 'Received against '.$jvNo,
                'reference_id'   => $fromAccount->id,
                'amount'         => $convertedAmount,
                'flow_type'      => 'credit',
                'opening_balance'=> $toAccount->opening_balance,
                'closing_balance'=> $toAccount->opening_balance + $convertedAmount,
                'currency_type'  => $toAccount->country->currency_type,
                'is_jv'          => 1,
                'jv_no'          => $jvNo,
                'jv_description' => $validated['jv_description'],
                'created_by'     => auth()->id(),
            ]);

            $fromAccount->update(['opening_balance' => $fromAccount->opening_balance - $amount]);
            $toAccount->update(['opening_balance' => $toAccount->opening_balance + $convertedAmount]);

            DB::commit();
            return redirect()->route('accounts.journal-voucher')->with('success', 'Transfer successful!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    private function getConversionAmount($amount, $fromCurrency, $toCurrency)
    {
        if ($fromCurrency->currency_type === $toCurrency->currency_type) {
            return $amount;
        }

        $amountInUSD = $amount / $fromCurrency->currency_rate;
        $convertedAmount = $amountInUSD * $toCurrency->currency_rate;

        return $convertedAmount;
    }
    
    public function getJvStatement($jvNo){
        $jvStatements = AccountTransaction::with('account')
            ->where('is_jv', 1)
            ->where('jv_no', $jvNo)
            ->get();

        return view('dashboards.accounts.jv-statement', compact('jvStatements'));
    }
    
    public function profitByCars(){
        $profitByCars = CarProfile::with('assignedManager')
            ->withSum('expenses', 'usd_amount')
            ->where('car_status', 'sold')
            ->paginate(20);

        return view('dashboards.accounts.profit-by-cars', compact('profitByCars'));
    }
    
    public function overallProfitloss()
    {        
        $countries = Country::select('id', 'name')->get();

        // Income: total sold price per country
        $income = CarProfile::select('country_id', DB::raw('SUM(sold_price) as total_sold'))
            ->groupBy('country_id')
            ->pluck('total_sold', 'country_id');
            
        $auction = CarProfile::select('country_id', DB::raw('SUM(price) as total_auction'))
            ->groupBy('country_id')
            ->pluck('total_auction', 'country_id');

        $carExpenses = CarExpense::join('car_profiles', 'car_expenses.car_profile_id', '=', 'car_profiles.id')
            ->select('car_profiles.country_id', DB::raw('SUM(car_expenses.usd_amount) as total_car_expenses'))
            ->groupBy('car_profiles.country_id')
            ->pluck('total_car_expenses', 'car_profiles.country_id');

        $officeExpenses = OfficeExpense::select('country_id', DB::raw('SUM(usd_amount) as total_office'))
            ->groupBy('country_id')
            ->pluck('total_office', 'country_id');

        $report = [];
        foreach ($countries as $country) {
            $cid = $country->id;

            $soldAgainstCountry     = $income[$cid] ?? 0;
            $auctionAgainstCountry = $auction[$cid] ?? 0;
            $carExpenseAgainstCountry     = $carExpenses[$cid] ?? 0;
            $officeExpenseAgainstCountry  = $officeExpenses[$cid] ?? 0;

            $totalExpenses = $auctionAgainstCountry + $carExpenseAgainstCountry + $officeExpenseAgainstCountry;
            $profitLoss    = $soldAgainstCountry - $totalExpenses;

            $report[$cid] = [
                'country'        => $country->name,
                'sold'           => $soldAgainstCountry,
                'auction'        => $auctionAgainstCountry,
                'car_expenses'   => $carExpenseAgainstCountry,
                'office_expenses'=> $officeExpenseAgainstCountry,
                'total_expenses' => $totalExpenses,
                'profit_loss'    => $profitLoss,
            ];
        }

        $totals = [
            'sold'           => array_sum(array_column($report, 'sold')),
            'auction'        => array_sum(array_column($report, 'auction')),
            'car_expenses'   => array_sum(array_column($report, 'car_expenses')),
            'office_expenses'=> array_sum(array_column($report, 'office_expenses')),
            'total_expenses' => array_sum(array_column($report, 'total_expenses')),
            'profit_loss'    => array_sum(array_column($report, 'profit_loss')),
        ];
        
        return view('dashboards.accounts.overall-profit-loss', compact('report', 'totals'));
    }

}

