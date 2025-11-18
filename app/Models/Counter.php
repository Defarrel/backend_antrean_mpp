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

    protected $dates = ['deleted_at'];

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    public function details()
    {
        return $this->hasMany(CounterDetail::class);
    }

    public function admin()
    {
        return $this->hasOne(User::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
