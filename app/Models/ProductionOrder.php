<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    use HasFactory;
    protected $fillable = [
    'order_code', 'plan_id', 'product_id', 'quantity_target', 'quantity_actual', 'quantity_reject',
    'assigned_to', 'status', 'started_at', 'finished_at'
    ];


    public function plan() {
    return $this->belongsTo(ProductionPlan::class);
    }


    public function product() {
    return $this->belongsTo(Product::class);
    }


    public function assignedTo() {
    return $this->belongsTo(User::class, 'assigned_to');
    }


    public function logs()
    {
        return $this->hasMany(ProductionLog::class, 'order_id');
    }

    public function lastLog()
    {
        return $this->hasOne(ProductionLog::class, 'order_id')->latest('changed_at');
    }
}
