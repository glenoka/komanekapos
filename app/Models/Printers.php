<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Printers extends Model
{
    protected $fillable=[
        'name',
        'code',
        'description',
        'connection_type',
        'ip_address',
        'port',
        'mac_address'
    ];
}
