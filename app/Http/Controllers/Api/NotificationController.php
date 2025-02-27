<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $notifications = Notification::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc') // Sort by creation date (newest first)
                ->paginate(10); // Paginate with 10 notifications per page

            return response()->json([
                'message' => 'Notifications retrieved successfully',
                'notifications' => $notifications,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching notifications:', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to retrieve notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
