<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'category_id', 'name', 'name_ar',
        'description', 'description_ar',
        'price', 'image', 'available', 'sort_order'
    ];

    protected $casts = [
        'price'     => 'float',
        'available' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
