<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'order_no',
        'description',
        'status'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function child() {
        return $this->hasMany(Category::class, 'parent_id');
    }
    public function children() {
        return $this->hasMany(Category::class, 'parent_id')
            ->with('children');
    }
    public function parent() {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function parents() {
        return $this->belongsTo(Category::class, 'parent_id')->with('parent');
    }
}
