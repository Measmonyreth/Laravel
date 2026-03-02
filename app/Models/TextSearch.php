<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextSearch extends Model
{
    /** @use HasFactory<\Database\Factories\TextSearchFactory> */
    use HasFactory;

    protected $fillable = [
        'text',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
