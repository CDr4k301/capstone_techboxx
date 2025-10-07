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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('order_id')->nullable()->constrained('checkouts')->onDelete('cascade');
            $table->foreignId('build_id')->nullable()->constrained('ordered_builds')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade');// staff
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
