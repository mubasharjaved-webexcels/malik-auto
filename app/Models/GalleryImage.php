<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class GalleryImage extends Model
{
    use HasFactory;
    
    protected $fillable=['car_profile_id','path'];
    
    public function carProfile()
    {
        return $this->belongsTo(CarProfile::class);
    }
}
