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
        Schema::create('reference_pictures', function (Blueprint $table) {
            $table->id();
            $table->string('picture',125);
            $table->foreignId('barberId')->constrained('barbers')->onDelete('restrict');
            $table->unique(['picture', 'barberId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reference_pictures');
    }
};
