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
        Schema::create('cpus', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('build_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');

            // CPU details
            $table->string('brand');
            $table->string('model');
            $table->string('socket_type');
            $table->integer('cores');
            $table->integer('threads');
            $table->decimal('base_clock', 4, 2);
            $table->decimal('boost_clock', 4, 2);
            $table->integer('tdp');
            $table->string('integrated_graphics');
            $table->string('generation');

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('base_price', 10, 2)->nullable()->after('price'); // New column for base/original price

            // Stock and media
            $table->integer('stock');
            $table->string('image')->nullable();
            $table->string('model_3d')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpus');
    }
};
