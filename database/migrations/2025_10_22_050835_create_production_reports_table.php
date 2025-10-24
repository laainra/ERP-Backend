<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('production_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->integer('quantity_target');
            $table->integer('quantity_actual');
            $table->integer('quantity_reject')->default(0);
            $table->string('status_final')->default('completed');
            $table->string('storage_location')->nullable(); // diserahkan ke gudang mana
            $table->date('report_date')->default(now());
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_reports');
    }
};
