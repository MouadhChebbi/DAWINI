<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION: disponibilites
 * 
 * Stores a doctor's weekly recurring availability.
 * Example: "Every Monday from 09:00 to 12:00"
 * 
 * User Story 5.1: En tant que Médecin, je veux définir mes créneaux de disponibilité
 * 
 * NOTE: These are TEMPLATES for the weekly schedule.
 * Actual appointments are stored in the "rendez_vous" table.
 * Blocked slots are stored in "blocages" table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disponibilites', function (Blueprint $table) {
            $table->id();

            // Which doctor this availability belongs to
            $table->foreignId('medecin_id')->constrained('users')->onDelete('cascade');

            // Day of week: 1=Monday, 2=Tuesday ... 7=Sunday
            $table->tinyInteger('jour_semaine');

            $table->time('heure_debut');    // Start time e.g. "09:00:00"
            $table->time('heure_fin');      // End time   e.g. "12:00:00"

            // Duration of each appointment slot in minutes (e.g. 15, 20, 30)
            $table->integer('duree_creneau')->default(30);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disponibilites');
    }
};
