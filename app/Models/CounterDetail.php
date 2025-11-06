<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounterDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'counter_id',
        'date',
        'total_queues',
        'served',
        'called',
        'canceled',
        'avg_duration',
    ];

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }
}
