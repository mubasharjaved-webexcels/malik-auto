<?php
// app/Http/Controllers/Api/OfficeExpenseApiController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreOfficeExpenseRequest;
use App\Http\Requests\Api\V1\UpdateOfficeExpenseRequest;
use App\Http\Resources\Api\V1\OfficeExpenseResource;
use App\Http\Resources\Api\V1\OfficeExpenseCollection;
use App\Models\OfficeExpense;
use App\Models\Country;
use App\Models\BankCashAccount;
use App\Models\AccountTransaction;
use App\Models\User;
use App\Models\CarProfile;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OfficeExpensesController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of office expenses.
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            $expenseQuery = OfficeExpense::with(['country', 'creator', 'currencyInfo', 'account'])
                ->orderBy('created_at', 'desc');

            if ($user->hasRole('manager')) {
                $expenseQuery->where('country_id', $user->country_id)
                    ->where('assigned_manager_id', $user->id)
                    ->where('created_by', $user->id);
            }

            $perPage = $request->input('per_page', 15);
            $expenses = $expenseQuery->paginate($perPage);

            return $this->success(
                $expenses,
                'Office expenses retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Error retrieving office expenses: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created office expense.
     */
    public function store(StoreOfficeExpenseRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = auth()->user();

            // Calculate USD amount
            $currency_rate = Country::where('currency_type', $validated['currency'])->value('currency_rate');

            if (!$currency_rate) {
                return $this->error('Invalid currency type', 422);
            }

            $usd_amount = $validated['amount'] / $currency_rate;

            // Handle account transaction
            if (isset($validated['account_id']) && $validated['account_id']) {
                $account = BankCashAccount::find($validated['account_id']);

                if (!$account) {
                    return $this->error('Account not found', 404);
                }

                if ($account && $validated['amount'] > 0) {
                    if ($account->opening_balance < $validated['amount']) {
                        return $this->error(
                            'Insufficient balance in selected account',
                            422,
                            ['account_id' => ['Insufficient balance in selected account.']]
                        );
                    }

                    AccountTransaction::create([
                        'account_id' => $account->id,
                        'transaction_type' => $validated['expense_name'],
                        'reference_id' => null,
                        'amount' => $validated['amount'],
                        'flow_type' => 'debit',
                        'opening_balance' => $account->opening_balance,
                        'closing_balance' => $account->opening_balance - $validated['amount'],
                        'currency_type' => $validated['currency'],
                        'created_by' => $user->id,
                    ]);

                    $account->opening_balance -= $validated['amount'];
                    $account->save();
                }
            }

            // Determine country and manager based on role
            $isManager = $user->hasRole('manager');
            $countryId = $isManager ? $user->country_id : $validated['country_id'];
            $managerId = $isManager ? $user->id : ($validated['assigned_manager_id'] ?? null);

            $expense = OfficeExpense::create([
                'country_id' => $countryId,
                'assigned_manager_id' => $managerId,
                'expense_name' => $validated['expense_name'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'usd_amount' => $usd_amount,
                'account_id' => $validated['account_id'] ?? null,
                'created_by' => $user->id
            ]);

            $expense->load(['country', 'creator', 'currencyInfo', 'account']);

            return $this->success(
                new OfficeExpenseResource($expense),
                'Office expense created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->error(
                'Error creating office expense: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified office expense.
     */
    public function show($id)
    {
        try {
            $expense = OfficeExpense::with(['country', 'creator', 'currencyInfo', 'account'])
                ->findOrFail($id);

            // Authorization check
            $user = auth()->user();
            if ($user->hasRole('manager')) {
                if (
                    $expense->country_id !== $user->country_id ||
                    $expense->created_by !== $user->id
                ) {
                    return $this->error('Unauthorized access', 403);
                }
            }

            return $this->success(
                new OfficeExpenseResource($expense),
                'Office expense retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Office expense not found', 404);
        }
    }

    /**
     * Update the specified office expense.
     */
    public function update(UpdateOfficeExpenseRequest $request, $id)
    {
        try {
            $expense = OfficeExpense::findOrFail($id);
            $validated = $request->validated();
            $user = auth()->user();

            // Authorization check
            if ($user->hasRole('manager')) {
                if (
                    $expense->country_id !== $user->country_id ||
                    $expense->created_by !== $user->id
                ) {
                    return $this->error('Unauthorized access', 403);
                }
            }

            $isManager = $user->hasRole('manager');
            $countryId = $isManager ? $user->country_id : $validated['country_id'];
            $managerId = $isManager ? $user->id : ($validated['assigned_manager_id'] ?? null);

            // Track changes for transaction logging
            $changes = [];
            if ($expense->country_id != $countryId) $changes[] = 'country changed';
            if ($expense->assigned_manager_id != $managerId) $changes[] = 'manager changed';
            if ($expense->expense_name != $validated['expense_name']) $changes[] = 'expense name changed';
            if ($expense->account_id != ($validated['account_id'] ?? null)) $changes[] = 'account changed';
            if ($expense->amount != $validated['amount']) $changes[] = 'amount changed';

            if (!empty($changes)) {
                // Calculate USD amount
                $currency_rate = Country::where('currency_type', $validated['currency'])->value('currency_rate');

                if (!$currency_rate) {
                    return $this->error('Invalid currency type', 422);
                }

                $usd_amount = $validated['amount'] / $currency_rate;

                $expense->update([
                    'country_id' => $countryId,
                    'assigned_manager_id' => $managerId,
                    'expense_name' => $validated['expense_name'],
                    'account_id' => $validated['account_id'] ?? null,
                    'amount' => $validated['amount'],
                    'usd_amount' => $usd_amount,
                    'currency' => $validated['currency']
                ]);

                // Handle account transaction if account is specified
                if (isset($validated['account_id']) && $validated['account_id']) {
                    $account = BankCashAccount::find($validated['account_id']);

                    if (!$account) {
                        return $this->error('Account not found', 404);
                    }

                    if ($account && $validated['amount'] > 0) {
                        if ($account->opening_balance < $validated['amount']) {
                            return $this->error(
                                'Insufficient balance in selected account',
                                422,
                                ['account_id' => ['Insufficient balance in selected account.']]
                            );
                        }

                        AccountTransaction::create([
                            'account_id' => $account->id,
                            'transaction_type' => count($changes) > 1 ? implode(' & ', $changes) : $changes[0],
                            'reference_id' => null,
                            'amount' => $validated['amount'],
                            'flow_type' => 'debit',
                            'opening_balance' => $account->opening_balance,
                            'closing_balance' => $account->opening_balance - $validated['amount'],
                            'currency_type' => $validated['currency'],
                            'created_by' => $user->id,
                        ]);

                        $account->opening_balance -= $validated['amount'];
                        $account->save();
                    }
                }
            }

            $expense->load(['country', 'creator', 'currencyInfo', 'account']);

            return $this->success(
                new OfficeExpenseResource($expense),
                'Office expense updated successfully'
            );
        } catch (\Exception $e) {
            return $this->error(
                'Error updating office expense: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified office expense.
     */
    public function destroy($id)
    {
        try {
            $expense = OfficeExpense::findOrFail($id);
            $user = auth()->user();

            // Authorization check
            if ($user->hasRole('manager')) {
                if (
                    $expense->country_id !== $user->country_id ||
                    $expense->created_by !== $user->id
                ) {
                    return $this->error('Unauthorized access', 403);
                }
            }

            $expense->delete();

            return $this->success(
                null,
                'Office expense deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->error(
                'Error deleting office expense: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Filter office expenses based on criteria.
     */
    public function filter(Request $request)
    {
        try {
            $user = auth()->user();

            $query = OfficeExpense::with(['country', 'creator', 'currencyInfo', 'account']);

            if ($user->hasRole('manager')) {
                $query->where('assigned_manager_id', $user->id)
                    ->where('created_by', $user->id)
                    ->where('country_id', $user->country_id);
            } else {
                if ($request->country_id) {
                    $query->where('country_id', $request->country_id);
                }

                if ($request->assigned_manager_id) {
                    $query->where('assigned_manager_id', $request->assigned_manager_id);
                }

                if ($request->yard) {
                    $query->where('yard', $request->yard);
                }
            }

            $expenses = $query->get();
            $grandTotal = $expenses->sum('usd_amount');

            return $this->success([
                'expenses' => OfficeExpenseResource::collection($expenses),
                'grand_total' => number_format($grandTotal, 2),
                'total_count' => $expenses->count()
            ], 'Expenses filtered successfully');
        } catch (\Exception $e) {
            return $this->error(
                'Error filtering expenses: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Search office expenses.
     */
    public function search(Request $request)
    {
        try {
            $user = auth()->user();
            $search = $request->input('search');

            $query = OfficeExpense::with(['country', 'creator', 'currencyInfo', 'account'])
                ->orderBy('created_at', 'desc');

            if ($user->hasRole('manager')) {
                $query->where('country_id', $user->country_id)
                    ->where('created_by', $user->id)
                    ->where('assigned_manager_id', $user->id);
            }

            if (!empty($search)) {
                $query->where('expense_name', 'LIKE', '%' . $search . '%');
            }

            $perPage = $request->input('per_page', 15);
            $expenses = $query->paginate($perPage);

            return $this->success(
                $expenses,
                'Search results retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->error(
                'Error searching expenses: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get yards by country.
     */
    public function getYards(Request $request)
    {
        try {
            $countryId = $request->input('country_id');

            if (!$countryId) {
                return $this->error('Country ID is required', 422);
            }

            $yards = CarProfile::where('country_id', $countryId)
                ->whereNotNull('located_yard')
                ->distinct()
                ->pluck('located_yard')
                ->filter()
                ->values();

            return $this->success(
                ['yards' => $yards],
                'Yards retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->error(
                'Error fetching yards: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get managers by country.
     */
    public function getManagersByCountry($countryId)
    {
        try {
            $managers = User::role('manager')
                ->where('country_id', $countryId)
                ->select('id', 'name', 'email')
                ->get();

            return $this->success(
                ['managers' => $managers],
                'Managers retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->error(
                'Error fetching managers: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Export office expenses to CSV.
     */
    public function export(Request $request)
    {
        try {
            $query = OfficeExpense::with(['country', 'creator', 'currencyInfo']);

            if ($request->country_id) {
                $query->where('country_id', $request->country_id);
            }

            if ($request->yard) {
                $query->where('yard', $request->yard);
            }

            if ($request->assigned_manager_id) {
                $query->where('assigned_manager_id', $request->assigned_manager_id);
            }

            $filteredExpenses = $query->get();
            $filename = 'office_expenses_' . now()->format('Y-m-d_H-i-s') . '.csv';

            return response()->streamDownload(function () use ($filteredExpenses) {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, [
                    'ID',
                    'Country',
                    'Manager',
                    'Expense Name',
                    'Amount',
                    'Currency',
                    'USD Amount',
                    'Created By',
                    'Created At'
                ]);

                foreach ($filteredExpenses as $key => $expense) {
                    fputcsv($handle, [
                        $key + 1,
                        $expense->country->name ?? 'N/A',
                        $expense->assignedManager->name ?? 'N/A',
                        $expense->expense_name,
                        $expense->amount,
                        $expense->currency,
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
        } catch (\Exception $e) {
            return $this->error(
                'Error exporting expenses: ' . $e->getMessage(),
                500
            );
        }
    }
}
