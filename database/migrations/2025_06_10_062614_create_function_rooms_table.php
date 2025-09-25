<?php

use App\Models\FunctionRoomImages;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('function_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('function_room_section');
            $table->string('function_room_name');
            $table->string('function_room_capacity');
            $table->string('function_room_rate');
            $table->decimal('discount', 5, 2)->default(0);
            $table->string('function_room_description');
            $table->string('function_room_policy');
            $table->string('function_room_360');
            $table->boolean('featured')->default(false);
            $table->boolean('function_room_status')->default(1);
            $table->string('function_room_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('function_rooms');
    }
};
