<?php
// app/Models/Category.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'name_ar', 'icon', 'sort_order', 'active'];
    protected $casts = ['active' => 'boolean'];

    public function items()
    {
        return $this->hasMany(MenuItem::class);
    }
}
