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
        Schema::create('add_on_function_room_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('function_room_booking_id'); // FK to bookings
            $table->unsignedBigInteger('add_on_id'); // FK to add ons
            $table->integer('qty');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->foreign('function_room_booking_id')
                ->references('id')->on('function_room_bookings')
                ->onDelete('cascade');

            $table->foreign('add_on_id')
                ->references('id')->on('add_ons')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_on_function_room_bookings');
    }
};
