<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IapPurchase extends Model
{
    protected $table = 'iap_purchases';

    protected $fillable = [
        'purchase_token',
        'order_id',
        'android_id',
        'product_id',
        'points_added',
        'status'
    ];

    protected $casts = [
        'points_added' => 'integer',
    ];
}
