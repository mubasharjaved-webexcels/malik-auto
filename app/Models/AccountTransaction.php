<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class AccountTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'transaction_type',
        'reference_id',
        'amount',
        'flow_type',
        'assigned_to',
        'opening_balance',
        'closing_balance',
        'currency_type',
        'is_jv',
        'jv_no',
        'jv_description',
        'created_by',
    ];

    public function account()
    {
        return $this->belongsTo(BankCashAccount::class, 'account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

}