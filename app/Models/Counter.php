<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Counter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'counter_code',
        'name',
        'description',
        'quota',
        'schedule_start',
        'schedule_end',
    ];

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }
    
    public function details()
    {
        return $this->hasMany(CounterDetail::class);
    }

}
