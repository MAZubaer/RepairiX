<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'service_id',
        'message',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function serviceRecord()
    {
        return $this->belongsTo(ServiceRecord::class, 'service_id', 'service_id');
    }
}
