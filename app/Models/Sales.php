<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sales extends Model
{
    protected $fillable=[
        'invoice_number',
        'customer_id',
        'sale_date',
        'table_no',
        'sales_type',
        'order_type',
    'activity',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'total_items',
        'status',
        'user_id',
        'notes',
        'slug',
    ];
    public function detailSales()
    {
        return $this->hasMany(SalesDetail::class, 'sale_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            do {
                $model->slug = Str::random(10);
            } while (static::where('slug', $model->slug)->exists());
        });
        
        
    }
     public function getRouteKeyName()
    {
        return 'slug';
    }
}
