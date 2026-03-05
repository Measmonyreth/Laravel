<?php

namespace App\Http\Controllers;

use App\Models\User_Notification_General;
use Illuminate\Http\Request;

class UserNotificationGeneralController extends Controller
{
    //
    public function store(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'noti_general_id' => 'required|exists:notification_generals,id',
           // 'status' => 'required|in:unread,read,archived',
        ]);

        $userNotification = User_Notification_General::create([
            'user_id' => $user->id,
            'notification_general_id' => $request->noti_general_id,
            'status' => 'read', // Default to 'read' when creating a new notification for the user
        ]);

        return response()->json([
            'message' => 'Notification created successfully',
            'notification' => $userNotification,
        ], 201);
    }

    public function getUserNotifications(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notifications = $user->seen_notifications()->get();

        return response()->json([
            'notifications' => $notifications,
        ], 200);
    }
}
