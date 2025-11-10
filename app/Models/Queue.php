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
    'done_at',
];

protected $casts = [
    'called_at' => 'datetime',
    'served_at' => 'datetime',
    'canceled_at' => 'datetime',
    'done_at' => 'datetime',
];


    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }
}
