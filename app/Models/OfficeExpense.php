<?php
// app/Models/OfficeExpense.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'yard',
        'assigned_manager_id',
        'expense_name',
        'amount',
        'currency',
        'usd_amount',
        'account_id',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

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
    public function assignedManager()
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }
}
