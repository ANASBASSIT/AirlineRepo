<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    // Create a reservation
    public function store(Request $request)
    {
        $request->validate([
            'trip_id' => 'required|exists:trips,id',
        ]);

        $trip = Trip::findOrFail($request->trip_id);

        if ($trip->available_seats <= 0) {
            return response()->json(['message' => 'No available seats'], 400);
        }

        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'trip_id' => $trip->id,
            'reservation_date' => now(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $trip->decrement('available_seats');

        return response()->json($reservation, 201);
    }

    // Update a reservation
    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        // Ensure the user owns the reservation
        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['message' => 'You do not have permission to update this reservation'], 403);
        }

        // Only allow updating specific fields (ex: reservation_date, notes, etc.)
        $reservation->update($request->only(['reservation_date', 'notes']));

        return response()->json([
            'message' => 'Reservation updated successfully',
            'reservation' => $reservation,
        ]);
    }

    // Cancel a reservation
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);

        // Ensure the user owns the reservation
        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['message' => 'You do not have permission to cancel this reservation'], 403);
        }

        // Increase available seats
        $reservation->trip->increment('available_seats');

        // Delete the reservation
        $reservation->delete();

        return response()->json(['message' => 'Reservation canceled successfully']);
    }
    // View all reservations (for admin)
    public function index(Request $request)
    {
        $reservations = Reservation::with(['trip', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(perPage: 5);
    
        return response()->json([
            'message' => 'Paginated reservations retrieved successfully',
            'reservations' => $reservations,
        ]);
    }

    // Admin-only: Approve a reservation
    public function approve($id)
    {
        $reservation = Reservation::findOrFail($id);
    
        // Ensure the reservation is paid
        if ($reservation->payment_status !== 'paid') {
            return response()->json(['message' => 'Reservation must be paid before approval'], 400);
        }
    
        // Approve the reservation
        $reservation->update(['status' => 'confirmed']);
    
        // Fetch paginated reservations (for admin to view)
        $reservations = Reservation::with(['trip', 'user'])
            ->orderBy('created_at', 'desc') // Optional: Sort by creation date
            ->paginate(5); // Paginate with 10 reservations per page
    
        return response()->json([
            'message' => 'Reservation approved successfully',
            'reservation' => $reservation,
            'all_reservations' => $reservations, // Include paginated reservations in the response
        ]);
    }

    // Admin-only: Cancel a reservation
    public function cancel($id)
    {
        $reservation = Reservation::findOrFail($id);

        // Cancel the reservation
        $reservation->update(['status' => 'canceled']);

        // Increase available seats
        $reservation->trip->increment('available_seats');

        return response()->json([
            'message' => 'Reservation canceled successfully',
            'reservation' => $reservation,
        ]);
    }
}
