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
    protected $appends = [
        'car_image_url',
        'car_video_url',
        'car_gallery_urls',
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
    /**
     * Full URL for main car image
     */

    public function getCarImageUrlAttribute()
    {
        if (!$this->car_image) {
            return null;
        }

        // Simply prepend /storage/ to the path
        return url('storage/' . $this->car_image);
    }

    public function getCarVideoUrlAttribute()
    {
        if (!$this->car_video) {
            return null;
        }

        return url('storage/' . $this->car_video);
    }

    public function getCarGalleryUrlsAttribute()
    {
        return $this->galleryImages->map(function ($image) {
            // path already contains 'storage/', so just prepend base URL
            return url('storage/' . $image->path);
        })->toArray();
    }
}
