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
        Schema::create('resident_details', function (Blueprint $table) {
            $table->id();
            $table->string('unit_no');
            $table->string('email');
            $table->enum('resident_type', ['OWNER', 'TENANT']);
            $table->string('invite_token')->nullable();
            $table->dateTime('last_token_sent_at')->nullable();
             $table->dateTime('token_expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resident_details');
    }
};
