<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesDetail extends Model
{
    protected $fillable=[
        'sale_id',
        'product_id',
        'product_name',
        'quantity',
        'unit',
        'unit_price',
        'original_price',
        'discount_amount',
        'total_price',
        'is_complimentary'
    ];
}
