<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ausi_inspection_results', function (Blueprint $table) {
            $table->foreignId('ausi_booking_id')
                ->constrained('ausi_bookings')
                ->cascadeOnDelete();

            $table->foreignId('inspection_item_id')
                ->constrained('ausi_inspection_items')
                ->cascadeOnDelete();
            $table->string('status');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ausi_inspections');
    }
};
