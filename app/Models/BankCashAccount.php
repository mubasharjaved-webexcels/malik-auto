<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankCashAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'country_id',
        'assigned_to',
        'currency_type',
        'yard',
        'opening_balance',
        'status',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

