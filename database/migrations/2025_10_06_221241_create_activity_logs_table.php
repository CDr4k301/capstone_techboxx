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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // e.g., 'user_login', 'order_updated', 'invoice_created'
            $table->text('description')->nullable();
            $table->json('old_data')->nullable(); // Previous state
            $table->json('new_data')->nullable(); // New state
            $table->string('model_type')->nullable(); // e.g., App\Models\Order
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the affected model
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Staff/Admin who performed action
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['model_type', 'model_id']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
