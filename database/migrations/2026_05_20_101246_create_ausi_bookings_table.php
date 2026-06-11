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

        Schema::create('ausi_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('email');
            $table->string('unit_no');
            $table->string('resident_type');
            $table->string('name');
            $table->date('booking_date');
            $table->string('booking_time_slot');
            $table->string('srf_no')->nullable();
            $table->string('unit_area');
            $table->text('remarks')->nullable();
            $table->tinyInteger('emergency')->default('0');
            $table->tinyInteger('booking_status')->default('1');
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ausi_bookings');
    }
};
