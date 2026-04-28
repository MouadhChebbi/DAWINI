<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION: documents_medicaux
 * 
 * Stores uploaded medical documents (PDF, images, etc.)
 * Each document belongs to a patient and was uploaded by a doctor.
 * 
 * User Stories:
 *   6.2 - Patient accesses their medical documents
 *   6.3 - Doctor adds a consultation report for a patient
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents_medicaux', function (Blueprint $table) {
            $table->id();

            // The patient this document belongs to
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');

            // The doctor who uploaded it
            $table->foreignId('medecin_id')->constrained('users')->onDelete('cascade');

            // Optionally linked to a specific appointment
            $table->foreignId('rendez_vous_id')->nullable()->constrained('rendez_vous')->onDelete('set null');

            $table->string('titre');                        // Human-readable title
            $table->string('type')->default('compte_rendu'); // compte_rendu, ordonnance, analyse...
            $table->string('fichier_path');                 // Path in storage (e.g. documents/patient_1/report.pdf)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_medicaux');
    }
};
