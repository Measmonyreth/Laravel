<?php

namespace App\Http\Controllers;

use App\Models\NotificationGeneral;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function sendToUser(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'image' => 'nullable|url',       // ✅ Add image validation
        ]);

        $response = $this->fcmService->sendPushNotification(
            $request->token,
            $request->title,
            $request->body,
            [
                'route' => $request->route ?? 'home',
                'user_id' => $request->user_id ?? null,
            ],
            $request->image ?? null          // ✅ Pass image URL
        );

        return response()->json(['success' => true, 'response' => $response]);
    }

    public function sendToTopic(Request $request)
    {
        $request->validate([
            'topic' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
           // 'large_image' => 'nullable|url',       // ✅ Add image validation
            'big_image' => 'nullable|url',       // ✅ Add image validation
        ]);

        $response = $this->fcmService->sendToTopic(
            $request->topic,
            $request->title,
            $request->body,
            [
                'route' => $request->route ?? 'home',
                'type' => $request->type ?? 'general',
                'title' => $request->title,
                'body' => $request->body,
                'large_image' => $request->large_image ?? null,
                'big_image' => $request->big_image ?? null,
            ],
            $request->image ?? null          // ✅ Pass image URL
        );

        $pathSmallImage = null;
        $pathLargeImage = null;
        if ($request->hasFile('large_image')) {
            $img = $request->file('large_image');
            $pathSmallImage = Storage::disk('public')->put('notifications', $img);

        } elseif ($request->filled('large_image')) {
            $url = $request->large_image;
            $contents = file_get_contents($url);
            $extension = pathinfo($url, PATHINFO_EXTENSION) ?: 'jpg';
            $filename = 'notifications/'.uniqid().'.'.$extension;
            Storage::disk('public')->put($filename, $contents);
            $pathSmallImage = $filename; // ✅ Store relative path only
        }

        if ($request->hasFile('big_image')) {
            $img = $request->file('big_image');
            $pathLargeImage = Storage::disk('public')->put('notifications', $img);

        } elseif ($request->filled('big_image')) {
            $url = $request->big_image;
            $contents = file_get_contents($url);
            $extension = pathinfo($url, PATHINFO_EXTENSION) ?: 'jpg';
            $filename = 'notifications/'.uniqid().'.'.$extension;
            Storage::disk('public')->put($filename, $contents);
            $pathLargeImage = $filename; // ✅ Store relative path only
        }

        $notification = NotificationGeneral::create([
            'title' => $request->title,
            'body' => $request->body,
            'big_image' => $pathLargeImage ?? null,
            'large_image' => $pathSmallImage ?? null,
            'route' => $request->route,
            'type' => $request->type,
        ]);

        // CREATE NOTIFICATION GENERAL

        return response()->json(['success' => true, 'response' => $response]);
    }
}
