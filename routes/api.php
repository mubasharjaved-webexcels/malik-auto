<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AdminDashboardController;
use App\Http\Controllers\Api\V1\CarProfileController;
use App\Http\Controllers\Api\V1\CarExpensesController;
use App\Http\Controllers\Api\V1\OfficeExpensesController;
use App\Http\Controllers\Api\V1\BankCashAccountController;
use App\Http\Controllers\Api\V1\CurrencyRateController;
use App\Http\Controllers\Api\V1\UserRoleController;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public Routes (Guest)
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    // Protected Routes (Authenticated Users)
    Route::middleware('auth:sanctum')->group(function () {

        // Auth Routes
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
        Route::post('refresh-token', [AuthController::class, 'refreshToken']);

        // Dashboard Routes
        Route::get('dashboard', [AdminDashboardController::class, 'index']);
        Route::get('dashboard/stats', [AdminDashboardController::class, 'stats']);

        // Car Profiles Resource + Custom Routes
        Route::apiResource('car-profiles', CarProfileController::class);
        Route::prefix('car-profiles')->group(function () {
            Route::get('search', [CarProfileController::class, 'search']);
            Route::post('check-chassis', [CarProfileController::class, 'checkChassis']);
            Route::post('{id}/transfer', [CarProfileController::class, 'transferToCountry']);
        });
        Route::get('yards-by-country/{countryId}', [CarProfileController::class, 'getYardsByCountry']);
        Route::patch('/mark-car-received/{car}', [CarProfileController::class, 'markReceived']);

        // Car Expenses Resource + Custom Routes

        Route::prefix('car-expenses')->name('api.car-expenses.')->group(function () {
            Route::get('/', [CarExpensesController::class, 'index']);
            Route::post('/', [CarExpensesController::class, 'store']);
            Route::get('/metadata', [CarExpensesController::class, 'metadata']);
            Route::post('/filter', [CarExpensesController::class, 'filter']);
            Route::get('/search', [CarExpensesController::class, 'search']);
            Route::get('/{id}', [CarExpensesController::class, 'show']);
            Route::put('/{id}', [CarExpensesController::class, 'update']);
            Route::delete('/{id}', [CarExpensesController::class, 'destroy']);
        });

        // Office Expenses Routes
        Route::prefix('office-expenses')->name('api.office-expenses.')->group(function () {
            Route::get('/filter', [OfficeExpensesController::class, 'filter'])->name('filter');
            Route::get('/search', [OfficeExpensesController::class, 'search'])->name('search');
            Route::get('/yards/by-country', [OfficeExpensesController::class, 'getYards'])->name('getYards');
            Route::get('/export', [OfficeExpensesController::class, 'export'])->name('export');
            Route::get('/', [OfficeExpensesController::class, 'index'])->name('index');
            Route::post('/', [OfficeExpensesController::class, 'store'])->name('store');

            Route::get('/{id}', [OfficeExpensesController::class, 'show'])
                ->whereNumber('id')
                ->name('show');

            Route::put('/{id}', [OfficeExpensesController::class, 'update'])
                ->whereNumber('id')
                ->name('update');

            Route::delete('/{id}', [OfficeExpensesController::class, 'destroy'])
                ->whereNumber('id')
                ->name('destroy');
        });
        Route::get('/managers/by-country/{countryId}', [OfficeExpensesController::class, 'getManagersByCountry'])->name('getManagersByCountry');

        // Additional endpoints


        // Currency Rates Routes
        Route::prefix('currency-rates')->group(function () {
            Route::get('/', [CurrencyRateController::class, 'index']);
            Route::put('/{country}', [CurrencyRateController::class, 'update']);
        });

        // Helper/Utility Routes
        Route::get('countries', function () {
            return response()->json([
                'status' => 'success',
                'data' => \App\Models\Country::all()
            ]);
        });


        Route::get('managers-by-country/{countryId}', function ($countryId) {
            return response()->json([
                'status' => 'success',
                'data' => \App\Models\User::role('manager')
                    ->where('country_id', $countryId)
                    ->get(['id', 'name'])
            ]);
        });

        // Admin Only Routes
        Route::middleware('role:admin')->group(function () {

            // User Management
            Route::prefix('users/managers')->group(function () {
                Route::get('/', [UserRoleController::class, 'managers']);
                Route::get('{id}', [UserRoleController::class, 'show']);
                Route::put('{id}', [UserRoleController::class, 'update']);
                Route::put('{id}/toggle-status', [UserRoleController::class, 'toggleStatus']);
            });

            // Pending Sold Cars
            Route::get('pending-sold', [CarProfileController::class, 'getPendingRequests']);
            Route::put('pending-sold/{id}/process', [CarProfileController::class, 'processPendingRequest']);
            Route::prefix('accounts')->group(function () {
                Route::get('/edit/{account}', [BankCashAccountController::class, 'edit'])->name('accounts.edit');
                Route::put('/update/{account}', [BankCashAccountController::class, 'update'])->name('accounts.update');
                Route::put('/accounts/{account}/toggle-status', [BankCashAccountController::class, 'toggleStatus'])->name('accounts.toggleStatus');
            });
        });

        // Admin & Manager Routes
        Route::middleware('role:admin|manager')->group(function () {

            // Bank/Cash Accounts Resource
            Route::apiResource('accounts', BankCashAccountController::class)->except(['destroy']);

            // Additional Account Routes
            Route::prefix('accounts')->group(function () {
                Route::get('by-country/{countryId}', [BankCashAccountController::class, 'getAccounts']);
                Route::get('{id}/statement', [BankCashAccountController::class, 'getStatement']);
                Route::put('{id}/toggle-status', [BankCashAccountController::class, 'toggleStatus']);
            });

            // Journal Voucher Routes
            Route::prefix('journal-vouchers')->group(function () {
                Route::get('/', [BankCashAccountController::class, 'journalVoucher']);
                Route::post('/', [BankCashAccountController::class, 'storeVoucher']);
                Route::get('{id}/statement', [BankCashAccountController::class, 'getJvStatement']);
            });

            // Reports Routes
            Route::prefix('reports')->group(function () {
                Route::get('profit-by-cars', [BankCashAccountController::class, 'profitByCars']);
                Route::get('overall-profit-loss', [BankCashAccountController::class, 'overallProfitLoss']);
            });
        });
    });
});
