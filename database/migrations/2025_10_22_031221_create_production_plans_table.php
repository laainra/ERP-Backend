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
        Schema::create('production_plans', function (Blueprint $table) {
        $table->id();
        $table->string('plan_code')->unique();
        $table->foreignId('product_id')->constrained()->onDelete('restrict');
        $table->unsignedInteger('quantity');
        $table->date('target_finish_date')->nullable();
        $table->text('notes')->nullable();
        $table->enum('status',['created','pending_approval','approved','rejected','in_process'])->default('created');
        $table->foreignId('creator_id')->constrained('users');
        $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('approved_at')->nullable();
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
        Schema::dropIfExists('production_plans');
    }
};
