<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductList extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function details() {
        return $this->hasOne(ProductDetails::class, 'product_id', 'id');
    }
}
