<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PhpParser\Node\NullableType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('amenity_id');
            $table->string('activity_name');
            $table->string('activity_image');
            $table->string('activity_description');
            $table->tinyInteger('activity_space');
            $table->tinyInteger('activity_max_booking');
            $table->string('activity_remarks')->nullable();
            $table->tinyInteger('activity_status')->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
