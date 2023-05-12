<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use HasFactory, SoftDeletes;
    use SoftDeletes;

    protected $table = 'hotel';

    protected $fillable = [
        'name',
        'address',
        'hotline',
        'email',
        'description',
        'room_total',
        'parking_slot',
        'bathrooms',
        'rating',
        'created_by',
    ];

    public function user()
    {
        // hotel create by user has role admin
        return $this->belongsTo(User::class, 'created_by', 'id')->where('role', 'admin');
    }

    public function booking()
    {
        return $this->hasMany(Booking::class);
    }

    public function category()
    {
        return $this->hasMany(Category::class);
    }

    public function hotelImage()
    {
        return $this->hasMany(HotelImage::class);
    }
}
