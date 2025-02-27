<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\AuthController;

// Public routes (anyone)
Route::post('/register', [AuthController::class, 'register']); // User registration
Route::post('/login', [AuthController::class, 'login']); // User login

// Admin Check
Route::middleware('is_admin')->get('/test-admin', function () {
    return response()->json(['message' => 'You have admin access!']);
});

// Protected routes for (admin)
Route::middleware(['auth:api', 'is_admin'])->group(function () {

    // Add Trip 
    Route::post('/admin/trips', [TripController::class, 'store'])->name('trips.store');

    // Manage Reservations Routes
    Route::get('/admin/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::post('/admin/reservations/{id}/approve', [ReservationController::class, 'approve'])->name('reservations.approve');
    Route::post('/admin/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
});

// Protected routes for authenticated users
Route::middleware('auth:api')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Trips
    Route::get('/trips', [TripController::class, 'index']);

    // Reservations
    Route::post('/reservations', [ReservationController::class, 'store']);

    // Payments
    Route::post('/payments', [PaymentController::class, 'store']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
});
