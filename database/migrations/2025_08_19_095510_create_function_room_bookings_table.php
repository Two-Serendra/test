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
        Schema::create('function_room_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('unit_no');
            $table->string('resident_type');
            $table->unsignedBigInteger('function_room_id');
            $table->string('purpose_of_event');
            $table->date('function_room_booking_date');
            $table->time('event_start_time');
            $table->time('event_end_time');
            $table->string('contact_number');
            $table->integer('pax');
            $table->string('payment_mode');
            $table->boolean('has_suppliers')->default(false);
            $table->string('authorization_file')->nullable();

            $table->decimal('room_total', 12, 2)->default(0);
            $table->decimal('addons_total', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            // --- Pricing snapshot ---
            $table->decimal('base_rate', 10, 2);   // Original rate at booking time
            $table->decimal('discount', 5, 2)->default(0); // Discount % at booking time
            $table->decimal('final_rate', 10, 2);  // Final discounted rate


            // --- Admin Approval ---
            $table->boolean('admin_approval')->default(false);
            $table->unsignedBigInteger('admin_approved_by')->nullable();
            $table->timestamp('admin_approved_at')->nullable();

            // --- Finance Approval ---
            $table->boolean('finance_approval')->default(false);
            $table->unsignedBigInteger('finance_approved_by')->nullable();
            $table->timestamp('finance_approved_at')->nullable();

            // --- Engineering Approval ---
            $table->boolean('engineering_approval')->default(false);
            $table->unsignedBigInteger('engineering_approved_by')->nullable();
            $table->timestamp('engineering_approved_at')->nullable();

            // --- Manager Approval ---
            $table->boolean('manager_approval')->default(false);
            $table->unsignedBigInteger('manager_approved_by')->nullable();
            $table->timestamp('manager_approved_at')->nullable();




            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admin_approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('finance_approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('engineering_approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('manager_approved_by')->references('id')->on('users')->nullOnDelete();

            $table->boolean('booking_status')->default(false);

            $table->unique(['function_room_id', 'function_room_booking_date'], 'unique_room_booking');

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('function_room_bookings');
    }


};
