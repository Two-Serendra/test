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
        Schema::create('activity_blockings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();

            $table->string('day'); // Monday, Tuesday, etc

            $table->time('start_time');
            $table->time('end_time');

            $table->string('remarks')->nullable(); // Basketball Clinic, Maintenance, etc

            $table->boolean('repeat_weekly')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_blockings');
    }
};
