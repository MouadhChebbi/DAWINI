<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION: rendez_vous
 * 
 * Core appointments table. Links a patient to a doctor at a given time.
 * 
 * User Stories:
 *   5.3 - Patient consults available slots
 *   5.4 - Patient books an appointment
 *   5.5 - Patient cancels appointment (if > 24h before)
 *   5.6 - Confirmation email sent after booking
 *   5.8 - Doctor views their agenda
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->id();

            // Who is the appointment for?
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');

            // Which doctor?
            $table->foreignId('medecin_id')->constrained('users')->onDelete('cascade');

            // When does the appointment start?
            $table->dateTime('date_heure');

            // Duration in minutes (copied from doctor's disponibilite at booking time)
            $table->integer('duree')->default(30);

            // Appointment lifecycle:
            //   'confirme'  → booked successfully
            //   'annule'    → cancelled by patient or doctor
            //   'termine'   → appointment is past and done
            $table->enum('statut', ['confirme', 'annule', 'termine'])->default('confirme');

            // Optional note from patient when booking
            $table->text('motif')->nullable();

            // Optional note from doctor after consultation
            $table->text('compte_rendu')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendez_vous');
    }
};
