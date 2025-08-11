<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sales extends Model
{
    protected $fillable = [
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

            if ($model->status === 'completed' && empty($model->invoice_number)) {
               dd();
                $model->invoice_number = self::generateInvoiceNumber();
            }
        });
    }
    protected static function generateInvoiceNumber()
    {
        $today = now()->format('Ymd');

        $lastInvoice = self::whereDate('created_at', now()->toDateString())
            ->whereNotNull('invoice_number')
            ->orderByDesc('invoice_number')
            ->first();

        $lastNumber = 0;

        if ($lastInvoice) {
            // Ambil 5 digit terakhir dari nomor invoice
            $lastNumber = (int) substr($lastInvoice->invoice_number, -5);
        }

        return 'KBM-' . $today . '-' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
