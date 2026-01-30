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
        Schema::create('amenity_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->nullable();
            $table->tinyInteger('activity_id');
            $table->string('lobby');
            $table->string('resident_type');
            $table->string('unit');
            $table->string('name');
            $table->string('contact_number')->nullable();
            $table->string('booking_type');
            $table->tinyInteger('booking_status')->default('1');
            $table->date('booking_date');
            $table->time('booking_start_time'); 
            $table->time('booking_end_time'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
