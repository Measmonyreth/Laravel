<?php

namespace App\Http\Controllers;

use App\Models\NotificationGeneral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NotificationGeneralController extends Controller
{
    //

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'title' => 'required|string|max:255',
    //         'body' => 'required|string',
    //         //  'small_image' => 'nullable|url', // ✅ Add image validation
    //         //  'large_image' => 'nullable|url', // ✅ Add image validation
    //         'route' => 'required|string',
    //         'type' => 'required|string',
    //     ]);

    //     $pathSmallImage = null;
    //     $pathLargeImage = null;
    //     if ($request->hasFile('small_image')) {
    //         $img = $request->file('small_image');
    //         $path = Storage::disk('public')->put('notifications', $img);
    //         $pathSmallImage = $path;
    //     } elseif ($request->filled('small_image')) {
    //         // Case 2: URL input - download and save it
    //         $url = $request->small_image;
    //         $contents = file_get_contents($url);
    //         $filename = 'notifications/'.uniqid().'.'.pathinfo($url, PATHINFO_EXTENSION) ?: 'jpg';
    //         Storage::disk('public')->put($filename, $contents);
    //         $pathSmallImage = asset('storage/'.$filename);
    //     }

    //     if ($request->hasFile('large_image')) {
    //         $img = $request->file('large_image');
    //         $path = Storage::disk('public')->put('notifications', $img);
    //         $pathLargeImage = $path;
    //     } elseif ($request->filled('large_image')) {
    //         // Case 2: URL input - download and save it
    //         $url = $request->large_image;
    //         $contents = file_get_contents($url);
    //         $filename = 'notifications/'.uniqid().'.'.pathinfo($url, PATHINFO_EXTENSION) ?: 'jpg';
    //         Storage::disk('public')->put($filename, $contents);
    //         $pathLargeImage = asset('storage/'.$filename);
    //     }

    //     $notification = NotificationGeneral::create([
    //         'title' => $request->title,
    //         'body' => $request->body,
    //         'small_image' => $pathSmallImage, // ✅ Save small image URL
    //         'large_image' => $pathLargeImage, // ✅ Save large image URL
    //         'route' => $request->route,
    //         'type' => $request->type,
    //     ]);

    //     return response()->json(['message' => 'Notification created successfully', 'notification' => $notification], 201);
    // }

    public function index()
    {
        $notifications = NotificationGeneral::all();

        return response()->json(
            [
                'notifications' => $notifications,
            ],
            200
        );
    }

    public function show($id)
    {
        $notification = NotificationGeneral::findOrFail($id);

        return response()->json($notification);
    }

    public function update($id, Request $request)
    {
        $notification = NotificationGeneral::findOrFail($id);
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'small_image' => 'nullable|url', // ✅ Add image validation
            'large_image' => 'nullable|url', // ✅ Add image validation
            'route' => 'sometimes|required|string',
            'type' => 'sometimes|required|string',
        ]);

        if ($request->has('small_image')) {
            $pathSmallImage = $request->file('small_image');
            $path = Storage::disk('public')->put('notifications', $pathSmallImage);

            if ($notification->small_image && Storage::disk('public')->fileExists($notification->small_image)) {
                Storage::disk('public')->delete($notification->small_image);
            }

            $notification->small_image = $path;
            // ✅ Update small image URL
        }

        if ($request->has('large_image')) {
            $pathLargeImage = $request->file('large_image');
            $path = Storage::disk('public')->put('notifications', $pathLargeImage);

            if ($notification->large_image && Storage::disk('public')->fileExists($notification->large_image)) {
                Storage::disk('public')->delete($notification->large_image);
            }

            $notification->large_image = $path;
            // ✅ Update large image URL
        }

        $notification->update($request->only(['title', 'body', 'route', 'type']));

        return response()->json(['message' => 'Notification updated successfully', 'notification' => $notification], 200);

    }
}
