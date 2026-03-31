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
        Schema::create('activity_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->nullable();
            $table->tinyInteger('activity_id');
            $table->tinyInteger('user_id')->nullable();
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
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('has_penalty')->default(false);
            $table->decimal('penalty_amount', 10, 2)->nullable();
            $table->boolean('penalty_waived')->default(false);
            $table->unsignedBigInteger('waived_by')->nullable();
            $table->timestamp('penalty_waived_at')->nullable();
            $table->unsignedBigInteger('penalty_applied_by')->nullable();
            $table->timestamp('penalty_applied_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->boolean('cancelled_within_12hrs')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations. 
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_bookings');
    }
};
