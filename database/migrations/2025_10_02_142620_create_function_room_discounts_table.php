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
        Schema::create('function_room_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('function_room_id')->constrained()->onDelete('cascade');
            $table->decimal('discount', 5, 2);
            $table->text('remarks')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('function_room_discounts');
    }
};
