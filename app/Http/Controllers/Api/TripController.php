<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TripController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $trips = Trip::where('available_seats', '>', 0)->get();

            Log::info('Trips retrieved successfully:', $trips->toArray());

            return response()->json([
                'message' => 'Trips retrieved successfully',
                'trips' => $trips,
            ]);
        } catch (\Exception $e) {
            Log::error('Trips index error:', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to retrieve trips',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Add a new trip (for admin)
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'flight_number' => 'required|string|max:255',
            'departure_city' => 'required|string|max:255',
            'destination_city' => 'required|string|max:255',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date',
            'price' => 'required|numeric',
            'available_seats' => 'required|integer',
        ]);
        $trip = Trip::create($request->all());
        return response()->json([
            'message' => 'Trip created successfully',
            'trip' => $trip,
        ], 201);
    }
}
