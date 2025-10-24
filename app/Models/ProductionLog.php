<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_type',
        'plan_id',
        'order_id',
        'old_status',
        'new_status',
        'note',
        'changes',
        'changed_by',
        'changed_at',
    ];

    public function order() {
        return $this->belongsTo(ProductionOrder::class, 'order_id');
    }

    public function plan() {
        return $this->belongsTo(ProductionPlan::class, 'plan_id');
    }

    public function changedBy() {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
