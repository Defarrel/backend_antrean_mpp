<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_number',
        'counter_id',
        'guest_name',
        'status',
        'called_at',
        'served_at',
        'canceled_at',
    ];

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }
}
