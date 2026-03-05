<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationGeneral extends Model
{
    /** @use HasFactory<\Database\Factories\NotificationGeneralFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'small_image', // ✅ Add small image field
        'large_image', // ✅ Add large image field
        'route',
        'type',
    ];

    public function seen_by_users()
    {
        return $this->hasMany(User_Notification_General::class, 'notification_general_id');
    }
}
