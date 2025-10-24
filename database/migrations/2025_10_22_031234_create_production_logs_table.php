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
        Schema::create('production_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained('production_orders')->onDelete('cascade');
        $table->string('old_status')->nullable();
        $table->string('new_status');
        $table->text('note')->nullable();
        $table->foreignId('changed_by')->constrained('users');
        $table->timestamp('changed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_logs');
    }
};
