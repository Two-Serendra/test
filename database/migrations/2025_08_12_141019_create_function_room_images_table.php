<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('function_room_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('function_room_id');
            $table->string('image');
            $table->timestamps();

            $table->foreign('function_room_id')
                ->references('id')
                ->on('function_rooms')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('function_room_images');
    }
};
