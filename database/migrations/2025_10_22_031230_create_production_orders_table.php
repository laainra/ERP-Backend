<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_orders', function (Blueprint $table) {
        $table->id();
        $table->string('order_code')->unique();
        $table->foreignId('plan_id')->constrained('production_plans')->onDelete('cascade');
        $table->foreignId('product_id')->constrained()->onDelete('restrict');
        $table->unsignedInteger('quantity_target');
        $table->unsignedInteger('quantity_done')->default(0);
        $table->unsignedInteger('quantity_remaining')->default(0);
        $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
        $table->enum('status',['waiting','in_process','finished','cancelled'])->default('waiting');
        $table->timestamp('started_at')->nullable();
        $table->timestamp('finished_at')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_orders');
    }
};
