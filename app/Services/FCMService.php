<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FCMService
{
    public function sendPushNotification($token, $title, $body, $data = [], $imageUrl = null)
    {
        $messaging = Firebase::messaging();

        if (! empty($data)) {
            $message = $message->withData($data);
        }

        $notification = Notification::create()
            ->withTitle($title)
            ->withBody($body);

        // ✅ Add image if provided
        if ($imageUrl) {
            $notification = $notification->withImageUrl($imageUrl);
        }

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification);

        if (! empty($data)) {
            $message = $message->withData($data);
        }

        return $messaging->send($message);
    }

    public function sendToTopic($topic, $title, $body, $data = [], $imageUrl = null)
    {
        $messaging = Firebase::messaging();

        $notification = Notification::create()
            ->withTitle($title)
            ->withBody($body);

        // ✅ Add image if provided
        if ($imageUrl) {
            $notification = $notification->withImageUrl($imageUrl);
        }

        $message = CloudMessage::new()
            ->toTopic($topic)
            ->withNotification($notification);

        if (! empty($data)) {
            $message = $message->withData($data);
        }

        return $messaging->send($message);
    }
}
