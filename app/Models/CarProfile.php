<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_name',
        'rec_no',
        'located_yard',
        'grade',
        'seats',
        'chassis',
        'shift',
        'mileage',
        'engine_cc',
        'dimension',
        'm3',
        'price',
        'fuel',
        'max_loading',
        'country_id',
        'assigned_manager_id',
        'car_status',
        'sold_status',
        'admin_approved_at',
        'sale_price',
        'booking_price',
        'is_booked',
        'suggested_sold_price',
        'sold_price',
        'transit_expense',
        'transit_expense_in_usd',
        'currency_type',
        'account_id',
        'car_image',
        'car_video',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    
    public function countryCurrency()
    {
    //     return $this->belongsTo(Country::class);
        return $this->hasOne(Country::class, 'currency_type', 'currency_type');
    }
    
    public function expenses()
    {
        return $this->hasMany(CarExpense::class, 'car_profile_id');
    }

    public function assignedManager()
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }
    
    public function galleryImages()
    {
        return $this->hasMany(GalleryImage::class);
    }
    
}
