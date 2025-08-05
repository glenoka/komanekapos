<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'price',
        'path_images',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeSearch($query,$value)
    {
        $query->where("name","like","%{$value}%");
    }
}
