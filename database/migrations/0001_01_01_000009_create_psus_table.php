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
        Schema::create('psus', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Foreign keys
            $table->foreignId('build_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');

            // PSU details
            $table->string('brand');
            $table->string('model');
            $table->integer('wattage');
            $table->string('efficiency_rating');
            $table->string('modular');
            $table->integer('pcie_connectors');
            $table->integer('sata_connectors');

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('base_price', 10, 2)->nullable()->after('price'); // new column for base/original price

            // Stock and media
            $table->integer('stock');
            $table->string('image')->nullable();
            $table->string('model_3d')->nullable();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psus');
    }
};
