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
        Schema::create('pest_control_bookings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('transaction_no')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('unit_no');
            $table->string('resident_type');
            $table->string('name');
            $table->date('booking_date');
            $table->string('booking_time_slot');
            $table->string('srf_no')->nullable();
            $table->string('unit_area');
            $table->text('remarks')->nullable();
            $table->tinyInteger('charged_type')->default('1'); // 1 = Free, 2 = Charged
            $table->tinyInteger('emergency')->default('0');
            $table->tinyInteger('booking_status')->default('1');
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('has_penalty')->default(false);
            $table->decimal('penalty_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pest_control_bookings');
    }
};
