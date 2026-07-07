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
            $table->string('email')->nullable();
            $table->string('unit_no')->nullable();
            $table->string('resident_type')->nullable();
            $table->string('name')->nullable();
            $table->date('booking_date')->nullable();
            $table->string('booking_time_slot')->nullable();
            $table->string('srf_no')->nullable();
            $table->string('unit_area')->nullable();
            $table->text('remarks')->nullable();
            $table->tinyInteger('emergency')->default('0');
            $table->integer('booking_status')
            ->default(1)
             ->comment('0 Cancelled, 1 Scheduled, 2 Completed');
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
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
