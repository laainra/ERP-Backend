<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'reported_by',
        'quantity_target',
        'quantity_actual',
        'quantity_reject',
        'status_final',
        'storage_location',
        'report_date',
        'notes'
    ];

    public function order() {
        return $this->belongsTo(ProductionOrder::class, 'order_id');
    }

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function reporter() {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
