<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('cart_item_id')->constrained()->onDelete('cascade');
            $table->timestamp('checkout_date');
            $table->decimal('total_cost', 10,2);
            $table->string('payment_method');
            $table->string('payment_status');
            $table->string('pickup_status')->nullable();
            $table->timestamp('pickup_date')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
