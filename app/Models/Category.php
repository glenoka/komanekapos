<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'status',
    ];
    public function products()
{
    return $this->hasMany(Product::class, 'category_id', 'id');
}
    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($model) {
            // Slug dasar dari name
            $slug = Str::slug($model->name);
            $originalSlug = $slug;
            $counter = 1;
    
            // Cek sampai dapat slug yang unik
            while (static::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
    
            $model->slug = $slug;
        });
    }
}
