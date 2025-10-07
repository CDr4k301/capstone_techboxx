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
        Schema::create('motherboards', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('build_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');

            // Motherboard details
            $table->string('brand');
            $table->string('model');
            $table->string('socket_type');
            $table->string('chipset');
            $table->string('form_factor');
            $table->string('ram_type');
            $table->integer('max_ram');
            $table->integer('ram_slots');
            $table->integer('max_ram_speed');
            $table->integer('pcie_slots');
            $table->integer('m2_slots');
            $table->integer('sata_ports');
            $table->integer('usb_ports');
            $table->string('wifi_onboard');

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('base_price', 10, 2)->nullable()->after('price'); // New column for base/original price

            // Stock and media
            $table->integer('stock');
            $table->string('image')->nullable();
            $table->string('model_3d')->nullable();
            $table->string('supported_cpu')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motherboards');
    }
};
