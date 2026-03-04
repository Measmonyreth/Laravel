<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class clone_card extends Model
{
    /** @use HasFactory<\Database\Factories\CloneCardFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_number',
        'expiry_date',
        'amount',
        'cardholder_name',
        'cvv',
        'status',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
