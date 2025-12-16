<?php
// app/Http/Controllers/OfficeExpensesController.php

namespace App\Http\Controllers;

use App\Models\OfficeExpense;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\BankCashAccount;
use App\Models\AccountTransaction;
use App\Models\CarProfile;

class OfficeExpensesController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $countries = $user->hasRole('manager') ? Country::where('id', $user->country_id)->get() : Country::all();
        $currencies = $countries->pluck('currency_type')->filter()->unique();
        $currencyRates = $countries->pluck('currency_rate', 'currency_type');

        $expenseQuery = OfficeExpense::with(['country', 'creator', 'currencyInfo', 'account'])->orderBy('created_at', 'desc');

        if ($user->hasRole('manager')) {
            // $assignedManagers = CarProfile::where('assigned_manager_id', $user->id)->where('country_id', $user->country_id)->pluck('located_yard')->unique();
            $expenseQuery->where('country_id', $user->country_id)->where('assigned_manager_id', $user->id)->where('created_by', $user->id);
            $accounts = BankCashAccount::where('assigned_to', $user->id)->get();
        } else {
            $accounts = BankCashAccount::all();
        }
        $processedExpenses = $expenseQuery->paginate(10);
        return view('dashboards.office_expenses.index', compact('countries', 'currencies', 'processedExpenses', 'accounts','currencyRates'));
    }

    public function filter(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('manager')) {
            $processedExpenses = OfficeExpense::with(['country', 'creator', 'currencyInfo'])
                ->where('assigned_manager_id', $user->id)
                ->where('created_by', $user->id)
                ->where('country_id', $user->country_id)
                ->get();
        } else {
            $query = OfficeExpense::with(['country', 'creator', 'currencyInfo']);

            if ($request->country_id) {
                $query->where('country_id', $request->country_id);
            }

            if ($request->assigned_manager_id) {
                    $query->where('assigned_manager_id', $request->assigned_manager_id);
            }
            
            $processedExpenses = $query->get();
        }
        
        $view = view('dashboards.office_expenses.partials.office_expense_rows', compact('processedExpenses'))->render();
        $grandTotal = number_format($processedExpenses->sum('usd_amount'), 2);

        return response()->json([
            'html' => $view,
            'grand_total' => $grandTotal,
        ]);
    }

    // public function getYardsByCountry($countryId)
    // {
    //     $yards = getYardsByCountry($countryId);
    //     return response()->json(['yards' => $yards]);
    // }

    public function getManagersByCountry($countryId)
    {
        $managers = \App\Models\User::role('manager')->where('country_id', $countryId)->select('id', 'name')->get();
        
        return response()->json($managers);
    }

    public function downloadCSV(Request $request)
    {
        $query = OfficeExpense::with(['country', 'creator', 'currencyInfo']);

        if ($request->country_id) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->yard) {
            $query->where('yard', $request->yard);
        }

        $filteredExpenses = $query->get();

        $filename = 'office_expenses_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://output', 'w');

        // CSV headers
        $headers = [
            'ID', 'Country', 'Manager', 'Expense Name', 'Amount', 'Currency', 'USD Amount', 'Created By', 'Created At'
        ];

        // Start response
        return response()->streamDownload(function () use ($handle, $filteredExpenses, $headers) {
            fputcsv($handle, $headers);

            foreach ($filteredExpenses as $key => $expense) {
            fputcsv($handle, [
                $key+1,
                $expense->country->name ?? 'N/A',
                $expense->creator->name ?? 'N/A',
                $expense->expense_name,
                $expense->amount,
                $expense->currency,
                // number_format($expense->amount, 2) . ' ' . $expense->currency . ' ÷ ' . number_format(optional($expense->currencyInfo)->currency_rate ?? 1, 2),
                $expense->usd_amount,
                $expense->creator->name ?? 'N/A',
                $expense->created_at->format('d-m-Y H:i'),
            ]);
        }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'nullable|exists:countries,id',
            // 'yard' => 'required|string|max:255',
            'assigned_manager_id' => 'nullable|exists:users,id',
            'expense_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'account_id' => 'sometimes|nullable|exists:bank_cash_accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $currency_rate = Country::where('currency_type', $request->currency)->value('currency_rate');
        $usd_amount = $request->amount / $currency_rate;

        $account = BankCashAccount::find($request->account_id);

        if ($account && $request->amount > 0) {
            if ($account->opening_balance < $request->amount) {    
                return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'account_id' => ['Insufficient balance in selected account.']
                ]
            ], 422);
                // return response()->json(['error' => 'Insufficient balance in selected account.'], 400);
            }
            
            AccountTransaction::create([
                'account_id' => $account->id,
                'transaction_type' => $request->expense_name,
                'reference_id' => null,
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
        try {
            $user = auth()->user();
            $isManager = $user->hasRole('manager');
            $countryId = $isManager ? $user->country_id : $request->country_id;
            $managerId = $isManager ? $user->id : $request->manager;

            OfficeExpense::create([
                'country_id' => $countryId,
                // 'yard' => $request->yard,
                'assigned_manager_id' => $managerId,
                'expense_name' => $request->expense_name,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'usd_amount' => $usd_amount,
                'account_id' => $account->id,
                'created_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Office expense created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating office expense: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $expense = OfficeExpense::with(['country', 'creator', 'account'])->findOrFail($id);
            $accounts = auth()->user()->hasRole('manager') ? BankCashAccount::with('country')->where('country_id', $expense->country_id)->get() : BankCashAccount::with('country')->get();
            return response()->json([
                'success' => true,
                'data' => $expense,
                'accounts' => $accounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Office expense not found'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'nullable|exists:countries,id',
            'expense_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'account_id' => 'sometimes|nullable|exists:bank_cash_accounts,id',
            'currency' => 'required|string|max:10'
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $expense = OfficeExpense::findOrFail($id);
            // $currency_rate = Country::where('currency_type', $request->currency)->value('currency_rate');
            // $usd_amount = $request->amount / $currency_rate;
            
            $user = auth()->user();
            $isManager = $user->hasRole('manager');
            $countryId = $isManager ? $user->country_id : $request->country_id;
            $managerId = $isManager ? $user->id : $request->manager;

            $changes = [];

            if ($expense->country_id != $countryId) {
                $changes[] = 'country changed';
            }
            if ($expense->assigned_manager_id != $managerId) {
                $changes[] = 'manager changed';
            }
            if ($expense->expense_name != $request->expense_name) {
                $changes[] = 'expense name changed';
            }
            if ($expense->account_id != $request->account_id) {
                $changes[] = 'account changed';
            }
            if ($expense->amount != $request->amount) {
                $changes[] = 'amount changed';
            }

            if (!empty($changes)) {
                $expense->update([
                    'country_id' => $countryId,
                    'assigned_manager_id' => $managerId,
                    'expense_name' => $request->expense_name,
                    'account_id' => $request->account_id,
                    'amount' => $request->amount,
                    'usd_amount' => $request->usdAmount,
                    'currency' => $request->currency
                ]);

                $account = BankCashAccount::find($request->account_id);

                if ($account && $request->amount > 0) {
                    if ($account->opening_balance < $request->amount) {    
                        return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => [
                            'account_id' => ['Insufficient balance in selected account.']
                        ]
                    ], 422);
                        // return response()->json(['error' => 'Insufficient balance in selected account.'], 400);
                    }
                    AccountTransaction::create([
                        'account_id' => $account->id,
                        'transaction_type' => count($changes) > 1 ? implode(' & ', $changes) : $changes[0],
                        'reference_id' => null,
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
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Office expense updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating office expense: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getYards(Request $request)
    {
        $countryId = $request->get('country_id');
        
        if (!$countryId) {
            return response()->json([
                'success' => false,
                'message' => 'Country ID is required'
            ]);
        }

        try {
            $yards = \App\Models\CarProfile::where('country_id', $countryId)
                ->whereNotNull('located_yard')
                ->distinct()
                ->pluck('located_yard')
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $yards
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching yards: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $expense = OfficeExpense::findOrFail($id);
            $expense->delete();

            return response()->json([
                'success' => true,
                'message' => 'Office expense deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting office expense: ' . $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search');

        $countries = $user->hasRole('manager') 
            ? Country::where('id', $user->country_id)->get() 
            : Country::all();

        $currencies = $countries->pluck('currency_type')->filter()->unique();
        $currencyRates = $countries->pluck('currency_rate', 'currency_type');

        $expenseQuery = OfficeExpense::with(['country', 'creator', 'currencyInfo', 'account'])
            ->orderBy('created_at', 'desc');

        if ($user->hasRole('manager')) {
            $expenseQuery->where('country_id', $user->country_id)
                        ->where('created_by', $user->id)
                        ->where('assigned_manager_id', $user->id);

            $accounts = BankCashAccount::where('assigned_to', $user->id)->get();
        } else {
            $accounts = BankCashAccount::all();
        }

        if (!empty($search)) {
            $expenseQuery->where('expense_name', 'LIKE', '%' . $search . '%');
        }

        $processedExpenses = $expenseQuery->paginate(10);
        
        if ($request->ajax()) {
            $rows = view('dashboards.office_expenses.partials.office_expense_rows', compact('processedExpenses'))->render();

            $grandTotal = '
                <tr>
                <th colspan="5" class="text-end"><strong>Grand Total</strong></th>
                <th id="car-expense-grand-total">
                    <strong class="text-success">
                    $' . number_format($processedExpenses->sum('usd_amount'), 2) . '
                    </strong>
                    <small class="text-muted d-block">Total USD Amount</small> 
                </th>
                <th colspan="4"></th>
                </tr>
            ';

            return response()->json([
                'tbody' => $rows,
                'tfoot' => $grandTotal,
                'pagination' => $processedExpenses->links('pagination::bootstrap-5')->toHtml(),
            ]);
        }

        return view('dashboards.office_expenses.index', compact(
            'countries',
            'currencies',
            'processedExpenses',
            'accounts',
            'currencyRates'
        ));
    }

}