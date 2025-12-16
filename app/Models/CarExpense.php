<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_profile_id',
        'stock_number',
        'expenses_for',
        'amount',
        'currency',
        'usd_amount',
        'account_id',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the car profile that owns the car expense.
     */
    public function carProfile()
    {
        return $this->belongsTo(CarProfile::class);
    }

    /**
     * Get the user who created the car expense.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function currencyInfo()
    {
        return $this->hasOne(Country::class, 'currency_type', 'currency');
    }
    
    public function account()
    {
        return $this->belongsTo(BankCashAccount::class, 'account_id');
    }
    
}