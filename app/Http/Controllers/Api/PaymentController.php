<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class PaymentController extends Controller
{
    // payment
    public function store(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
        ]);

        $reservation = Reservation::find($request->reservation_id);

        Log::info('Reservation:', ['reservation' => $reservation]);
        Log::info('Authenticated User:', ['user' => Auth::user()]);

        if (!$reservation) {
            Log::error('Reservation not found:', ['reservation_id' => $request->reservation_id]);
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        if ($reservation->user_id !== Auth::id()) {
            Log::error('Unauthorized access:', [
                'reservation_user_id' => $reservation->user_id,
                'authenticated_user_id' => Auth::id(),
            ]);
            return response()->json(['message' => 'You do not have permission to pay for this reservation'], 403);
        }

        if ($reservation->payment_status === 'paid') {
            Log::warning('Reservation already paid:', ['reservation_id' => $reservation->id]);
            return response()->json(['message' => 'Reservation is already paid'], 400);
        }

        $reservation->update(['payment_status' => 'paid']);

        $payment = $reservation->payment()->create([
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_date' => now(),
        ]);

        Log::info('Payment successful:', ['payment' => $payment]);

        return response()->json([
            'message' => 'Payment successful',
            'payment' => $payment,
        ], 201);
    }
}
