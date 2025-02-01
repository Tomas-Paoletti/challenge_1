<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;


    protected $fillable = [
        'tour_id',
        'hotel_id',
        'customer_name',
        'customer_email',
        'number_of_people',
        'booking_date',
        'booking_status',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function scopeHotel(Builder $query, $hotel_id) : Builder
    {
        return $query->where('hotel_id', $hotel_id);
    }

    public function scopeTour(Builder $query, $tour_id): Builder
    {
        return $query->where('tour_id', $tour_id);
    }

    public function scopeCustomerName(Builder $query, $customer_name): Builder
    {
        $searchTerm = '%' . str_replace(' ', '%', $customer_name) . '%';
        return $query->where('customer_name', 'like', $searchTerm);
    }

    Public function scopeCustomerEmail(Builder $query, $customer_email): Builder
    {
        return $query->where('customer_email', $customer_email);
    }


    public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('booking_date', [$startDate, $endDate]);
    }
}
