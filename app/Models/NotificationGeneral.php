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
        'large_image', // ✅ Add large image field
        'big_image', // ✅ Add big image field
        'route',
        'type',
    ];

    public function seen_by_users()
    {
        return $this->hasMany(User_Notification_General::class, 'notification_general_id');
    }

    // protected $appends = ['large_image_url', 'big_image_url'];

    // public function getLargeImageUrlAttribute()
    // {
    //     return $this->large_image
    //         ? asset('storage/'.$this->large_image)
    //         : null;
    // }

    // public function getBigImageUrlAttribute()
    // {
    //     return $this->big_image
    //         ? asset('storage/'.$this->big_image)
    //         : null;
    // }
}
