<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_Notification_General extends Model
{
    /** @use HasFactory<\Database\Factories\UserNotificationGeneralFactory> */
    use HasFactory;

        protected $fillable = [
            'user_id',
            'notification_general_id',
            'status', // 'unread', 'read', 'archived'
        ];

        public function user(){
            return $this->belongsTo(User::class);
        }
        public function notification_general(){
            return $this->belongsTo(NotificationGeneral::class);
        }
}
