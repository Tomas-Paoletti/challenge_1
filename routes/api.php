<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TourController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\BookingController;

Route::apiResource('tours', TourController::class);
Route::apiResource('hotels', HotelController::class);

Route::prefix('bookings')->group(function () {
    Route::patch('/{id}/cancel', [BookingController::class, 'cancel']);
    Route::get('export', [BookingController::class, 'export'])->name('bookings.export');
    Route::apiResource('/', BookingController::class)->except(['export']);
});


