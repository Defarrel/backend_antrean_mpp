<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'counter_id',
        'status',
        'status_time',
        'duration',
    ];

    protected $casts = [
        'status_time' => 'datetime',
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }
}
