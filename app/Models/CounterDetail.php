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
        'waiting_count',
        'called_count',
        'served_count',
        'canceled_count',
    ];

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }
}
