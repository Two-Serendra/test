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
            $table->decimal('base_rate', 10, 2);
            $table->decimal('discount', 5, 2)->default(0);
            $table->decimal('final_rate', 10, 2);


            // --- Concierge Approval ---
            $table->tinyInteger('concierge_approval')->default(0);
            $table->unsignedBigInteger('concierge_user_id')->nullable();
            $table->timestamp('concierge_action_at')->nullable();
            $table->text('concierge_remarks')->nullable();

            // --- Admin Approval ---
            $table->tinyInteger('admin_approval')->default(0);
            $table->unsignedBigInteger('admin_user_id')->nullable();
            $table->timestamp('admin_action_at')->nullable();
            $table->text('admin_remarks')->nullable();

            // --- Finance Approval ---
            $table->tinyInteger('finance_approval')->default(0);
            $table->unsignedBigInteger('finance_user_id')->nullable();
            $table->timestamp('finance_action_at')->nullable();
            $table->text('finance_remarks')->nullable();

            // --- Engineering Approval ---
            $table->tinyInteger('engineering_approval')->default(0);
            $table->unsignedBigInteger('engineering_user_id')->nullable();
            $table->timestamp('engineering_action_at')->nullable();
            $table->text('engineering_remarks')->nullable();

            // --- Manager Approval ---
            $table->tinyInteger('manager_approval')->default(0);
            $table->unsignedBigInteger('manager_user_id')->nullable();
            $table->timestamp('manager_action_at')->nullable();
            $table->text('manager_remarks')->nullable();

            // --- Booking Status ---
            // 0 = pending, 1 = approved, 2 = rejected
            $table->tinyInteger('booking_status')->default(0);

            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('concierge_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('admin_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('finance_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('engineering_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('manager_user_id')->references('id')->on('users')->nullOnDelete();

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
