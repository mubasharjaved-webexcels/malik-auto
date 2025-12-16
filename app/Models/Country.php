<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name',
        'currency_rate',
        'currency_type',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function carProfiles()
    {
        return $this->hasMany(CarProfile::class)->where('car_status', '!=', 'sold');
    }
    public function assignedCarProfiles()
    {
        return $this->hasMany(CarProfile::class)->where('assigned_manager_id', auth()->id())->where('car_status', '!=', 'sold');
    }

}
