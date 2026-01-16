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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barberId')->constrained('barbers');
            $table->foreignId('userId')->constrained('users');
            $table->date('appointmentDate');
            $table->unique(['barberId', 'userId', 'appointmentDate']);

            $table->string('status');
            $table->string('cancelledBy');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
