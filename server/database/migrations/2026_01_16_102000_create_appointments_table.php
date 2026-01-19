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
            $table->foreignId('barberId')->constrained('barbers')->onDelete('restrict');
            $table->foreignId('userId')->constrained('users')->onDelete('restrict');
            $table->date('appointmentDate');
            $table->unique(['barberId', 'userId', 'appointmentDate']);

            $table->string('status',50);
            $table->string('cancelledBy', 50);
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
