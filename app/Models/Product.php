<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['sku', 'name', 'description', 'unit'];


    public function productionPlans() {
    return $this->hasMany(ProductionPlan::class);
    }


    public function productionOrders() {
    return $this->hasMany(ProductionOrder::class);
    }
}
