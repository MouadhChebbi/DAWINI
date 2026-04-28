<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION: blocages
 * 
 * Stores manually blocked time slots by a doctor.
 * A blocked slot cannot receive appointments.
 * 
 * User Story 5.9: En tant que Médecin, je veux bloquer manuellement un créneau
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('medecin_id')->constrained('users')->onDelete('cascade');

            $table->dateTime('debut');  // Block starts at (e.g. 2024-06-10 14:00:00)
            $table->dateTime('fin');    // Block ends at   (e.g. 2024-06-10 16:00:00)

            $table->string('raison')->nullable(); // Optional reason (congé, formation, etc.)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocages');
    }
};
