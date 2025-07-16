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
        Schema::create('walkin_work_permits', function (Blueprint $table) {
            $table->id();
            $table->string('permit_type');
            $table->string('unit_no');
            $table->string('section');
            $table->string('no_days');
            $table->date('work_permit_booking_date');
            $table->string('under_renovation');
            $table->string('description');
            $table->string('contractor');
            $table->string('approved_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('walkin_work_permits');
    }
};
