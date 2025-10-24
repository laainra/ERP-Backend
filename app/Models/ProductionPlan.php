<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionPlan extends Model
{
    use HasFactory;
    protected $fillable = [
    'plan_code', 'product_id', 'quantity', 'target_finish_date', 'due_days', 'notes',
    'status', 'creator_id', 'approved_by', 'approved_at'
    ];


    public function product() {
    return $this->belongsTo(Product::class);
    }


    public function creator() {
    return $this->belongsTo(User::class, 'creator_id');
    }


    public function approver() {
    return $this->belongsTo(User::class, 'approved_by');
    }


    public function order() {
    return $this->hasOne(ProductionOrder::class, 'plan_id');
    }
}
