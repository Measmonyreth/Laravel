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
        if(User_Notification_General::where('user_id', $user->id)->where('notification_general_id', $request->noti_general_id)->exists()){
            return response()->json([
                'message' => 'Notification already exists',
            ], 409);
        }

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

    public function getAllUserSeenNotifications()
    {
        $notifications = User_Notification_General::where('status', 'read')->get();
        $count = User_Notification_General::where('status', 'read')->count();
        return response()->json([
            'notifications' => $notifications,
            'count' => $count,
        ], 200);
    }
}
