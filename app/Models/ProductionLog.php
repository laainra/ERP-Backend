<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLog extends Model
{
    use HasFactory;
    protected $fillable = ['order_id', 'old_status', 'new_status', 'note', 'changed_by', 'changed_at'];


    public $timestamps = false;


    protected $dates = ['changed_at'];


    public function order() {
    return $this->belongsTo(ProductionOrder::class, 'order_id');
    }


    public function changer() {
    return $this->belongsTo(User::class, 'changed_by');
    }
}
