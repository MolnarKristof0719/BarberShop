<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barberId')
                ->constrained('barbers')
                ->onDelete('restrict');

            $table->foreignId('userId')
                ->constrained('users')
                ->onDelete('restrict');

            $table->date('appointmentDate');
            $table->time('appointmentTime');

            // egy barbernek egy idősáv = 1 foglalás
            $table->unique(['barberId', 'appointmentDate', 'appointmentTime'], 'uniq_barber_slot');

            $table->enum('status', ['booked', 'cancelled', 'completed'])
                ->default('booked');

            $table->enum('cancelledBy', ['none', 'barber', 'customer'])
                ->default('none');

            // gyorsabb lekérdezésekhez (available slots)
            $table->index(['barberId', 'appointmentDate'], 'idx_barber_date');
            $table->index(['userId', 'appointmentDate'], 'idx_user_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
