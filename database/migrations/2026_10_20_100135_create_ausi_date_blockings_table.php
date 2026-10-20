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
        Schema::create('ausi_date_blockings', function (Blueprint $table) {
            $table->id();

            $table->date('start_date');
            $table->date('end_date');

            $table->string('reason', 255);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['start_date', 'end_date'],
                'ausi_date_blocks_active_dates_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ausi_date_blockings');
    }
};
