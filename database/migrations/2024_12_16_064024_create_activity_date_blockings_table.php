<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_date_blockings', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('amenity_id');
            // $table->string('activity_id');
            $table->string('blocking_status')->default('1');;
            $table->string('blocking_remarks');
            $table->date('date_blocking_start');
            $table->date('date_blocking_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_date_blockings');
    }
};
