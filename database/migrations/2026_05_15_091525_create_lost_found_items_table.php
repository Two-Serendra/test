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
        Schema::create('lost_found_items', function (Blueprint $table) {

            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade');

            $table->string('unit_no')->nullable();
            $table->enum('report_type', ['lost', 'found']);
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->date('date_lost_found');
            $table->string('location')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', [
                'pending',
                'approved',
                'claimed',
                'returned',
                'archived',
                'rejected'
            ])->default('pending');
            $table->unsignedBigInteger('claimed_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lost_found_items');
    }
};