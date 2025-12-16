<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ManagerDashboardController;
use App\Http\Controllers\SalesDashboardController;
use App\Http\Controllers\CarProfileController;
use App\Http\Controllers\CurrencyRateController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\CarExpensesController;
use App\Http\Controllers\OfficeExpensesController;
use App\Http\Controllers\BankCashAccountController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Routes
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return redirect()->route('role.dashboard'); // central redirect
    })->name('dashboard');

    Route::get('/role-dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('manager')) {
            return redirect()->route('manager.dashboard');
        } elseif ($user->hasRole('salesperson')) {
            return redirect()->route('sales.dashboard');
        }

        abort(403);
    })->name('role.dashboard');
    // -------------------Habib Routs start-----------------
    Route::prefix('car-expenses')->name('car-expenses.')->group(function () {
        Route::get('/', [CarExpensesController::class, 'index'])->name('index');
        Route::get('/data', [CarExpensesController::class, 'getData'])->name('data');
        Route::post('/store', [CarExpensesController::class, 'store'])->name('store');
        Route::get('/{id}', [CarExpensesController::class, 'show'])->name('show');
        Route::put('/{id}', [CarExpensesController::class, 'update'])->name('update');
        Route::delete('/{id}', [CarExpensesController::class, 'destroy'])->name('destroy');
        Route::post('/filter', [CarExpensesController::class, 'filter'])->name('filter');
    });

    Route::prefix('office-expenses')->name('office-expenses.')->group(function () {
        Route::get('/', [OfficeExpensesController::class, 'index'])->name('index');
        Route::get('/data', [OfficeExpensesController::class, 'getData'])->name('getData');
        Route::get('/yards', [OfficeExpensesController::class, 'getYards'])->name('getYards');
        Route::post('/', [OfficeExpensesController::class, 'store'])->name('store');
        Route::get('/{id}', [OfficeExpensesController::class, 'show'])->name('show');
        Route::put('/{id}', [OfficeExpensesController::class, 'update'])->name('update');
        Route::delete('/{id}', [OfficeExpensesController::class, 'destroy'])->name('destroy');
        Route::post('/filter', [OfficeExpensesController::class, 'filter'])->name('filter');    // by naqshbandi
        Route::post('/export', [OfficeExpensesController::class, 'downloadCSV'])->name('export');    // by naqshbandi
    });
 // -------------------Habib Routs end-----------------
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->name('admin.dashboard')
    ->middleware('role:admin');

    Route::get('/manager/dashboard', [ManagerDashboardController::class, 'index'])
    ->name('manager.dashboard')
    ->middleware('role:manager');

    Route::get('/sales/dashboard', [SalesDashboardController::class, 'index'])
    ->name('sales.dashboard')
    ->middleware('role:salesperson');

    Route::prefix('dashboards')->group(function () {
        Route::resource('car-profiles', CarProfileController::class);
        Route::get('/filter', [CarProfileController::class, 'filter'])->name('filter');
    });
    Route::get('/yards-by-country/{countryId}', [CarProfileController::class, 'getYardsByCountry'])->name('yards.by.country');
    Route::post('/car-profiles/{carProfile}/transfer', [CarProfileController::class, 'transferToCountry'])->name('car-profiles.transfer');
    Route::patch('/mark-car-received/{car}', [CarProfileController::class, 'markReceived'])->name('markCarReceived');
    
    Route::get('/car-profiles/search', [CarProfileController::class, 'search'])->name('car-profiles.search');
    Route::get('/car-expense/search', [CarExpensesController::class, 'search'])->name('car-expense.search');
    Route::get('/office-expense/search', [OfficeExpensesController::class, 'search'])->name('office-expense.search');

    Route::get('/get-managers/{country}', function ($countryId) {
        return response()->json(
            \App\Models\User::role('manager')->where('country_id', $countryId)->get(['id', 'name'])
        );
    })->name('get.managers');

    Route::get('/currency-rates', [CurrencyRateController::class, 'index'])->name('currency.rates');
    Route::put('/currencies/{country}', [CurrencyRateController::class, 'update'])->name('currencies.update');
    
    Route::middleware('role:admin')->group(function () {
        Route::get('/users/managers', [UserRoleController::class, 'managers'])->name('users.managers');
        Route::get('/managers/{manager}/edit', [UserRoleController::class, 'edit'])->name('managers.edit');
        Route::put('/managers/{manager}', [UserRoleController::class, 'update'])->name('managers.update');
        Route::put('/managers/{user}/toggle-status', [UserRoleController::class, 'managerToggleStatus'])->name('manager.toggleStatus');
        
        // Route::get('/users/salespersons', [UserRoleController::class, 'salespersons'])->name('users.salespersons');
        Route::get('/pending-sold', [CarProfileController::class, 'getPendingRequests'])->name('car_profiles.getPendingRequests');
        
        Route::put('/car-profiles/{id}/handle-pending-requests', [CarProfileController::class, 'processPendingRequestAction'])
            ->name('car_profiles.processPendingRequestAction');
    });
    
    Route::middleware('role:admin|manager')->prefix('accounts')->group(function () {
        Route::get('/', [BankCashAccountController::class, 'index'])->name('accounts.index');
        Route::get('/create/{type}', [BankCashAccountController::class, 'create'])->name('accounts.create');
        Route::post('/store', [BankCashAccountController::class, 'store'])->name('accounts.store');
        Route::get('/get-accounts/{countryId?}', [BankCashAccountController::class, 'getAccounts'])->name('accounts.get');
        Route::get('/get-statement/{account}/{type}', [BankCashAccountController::class, 'getStatement'])->name('accounts.statement');
        Route::get('/get-managers/{countryId}', [OfficeExpensesController::class, 'getManagersByCountry']);
        Route::get('/journal-voucher', [BankCashAccountController::class, 'journalVoucher'])->name('accounts.journal-voucher');
        Route::get('/create-voucher', [BankCashAccountController::class, 'createVoucher'])->name('accounts.create-voucher');
        Route::post('/store-voucher', [BankCashAccountController::class, 'storeVoucher'])->name('accounts.store-voucher');
        Route::get('/get-jv-statement/{jv}', [BankCashAccountController::class, 'getJvStatement'])->name('accounts.jv-statement');
        Route::get('/profit-by-cars', [BankCashAccountController::class, 'profitByCars'])->name('accounts.profit_by_cars');
        Route::get('/overall-profit-loss', [BankCashAccountController::class, 'overallProfitloss'])->name('accounts.overall_profit_loss');

    });
    Route::middleware('role:admin')->prefix('accounts')->group(function () {
        Route::get('/edit/{account}', [BankCashAccountController::class, 'edit'])->name('accounts.edit');
        Route::put('/update/{account}', [BankCashAccountController::class, 'update'])->name('accounts.update');
        Route::put('/accounts/{account}/toggle-status', [BankCashAccountController::class, 'toggleStatus'])->name('accounts.toggleStatus');
    });
    
    Route::post('/check-chassis', [CarProfileController::class, 'checkChassis'])->name('check.chassis');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Route::get('/forgot-password', function () {
//     return view('auth.forgot-password');
// })->name('password.request');