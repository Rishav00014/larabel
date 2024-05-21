<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = [
        'customerId',
        'customerId2',
        'user_id',
        'credit',
        'debit',
        'transfer',
        'details',
        'type',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerId');
    }

    public function customer2()
    {
        return $this->belongsTo(Customer::class, 'customerId2');
    }
}