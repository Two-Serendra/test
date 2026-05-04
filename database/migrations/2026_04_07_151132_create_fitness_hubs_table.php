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
        Schema::create('fitness_hubs', function (Blueprint $table) {
            $table->id();
            $table->string('fitness_hub_name');
            $table->string('fitness_hub_image');
            $table->longText('fitness_hub_description');
            $table->longText('fitness_hub_remarks')->nullable();
            $table->tinyInteger('fitness_hub_max_booking');
            $table->tinyInteger('fitness_hub_status')->default('1');
            $table->time('fitness_hub_start_time');
            $table->time('fitness_hub_end_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fitness_hubs');
    }
};
