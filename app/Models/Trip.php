<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_number',
        'departure_city',
        'destination_city',
        'departure_time',
        'arrival_time',
        'price',
        'available_seats',
    ];

    // Relationships
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}