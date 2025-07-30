<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable=[
        'header_receipt',
        'footer_receipt',
        'print_logo_on_receipt',
        'receipt_copies',
        'auto_print_receipt',
        'currency_code',
        'currency_symbol',
        'tax_name',
        'default_tax_rate'
    ];
}
