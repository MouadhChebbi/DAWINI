<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION: doctor_profiles
 * 
 * Stores doctor-specific professional information.
 * Each doctor (user with role=medecin) has ONE profile here.
 * Linked to users table via user_id (foreign key).
 * 
 * User Story 2.2: En tant que médecin, je veux ajouter mes informations professionnelles
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();

            // Link to the users table — deleting the user also deletes the profile
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('specialite');           // Medical specialty (e.g. cardiologie)
            $table->string('numero_rpps')->unique(); // French national doctor ID number
            $table->string('cabinet')->nullable();   // Practice/clinic name
            $table->string('ville')->nullable();     // City (used for location search)
            $table->string('code_postal')->nullable();

            // Whether admin has validated this doctor's account
            // Doctors start as 'pending' until admin approves (US 4.1)
            $table->boolean('valide')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
