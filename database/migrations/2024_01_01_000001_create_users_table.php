<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION: users
 * 
 * This is the main users table shared by ALL roles:
 *   - patient
 *   - medecin (doctor)
 *   - admin
 * 
 * We use a single table with a "role" column (Single Table Inheritance pattern).
 * Extra role-specific data is stored in related tables (see doctor_profiles, patient_profiles).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // --- Basic identity ---
            $table->string('nom');               // Last name
            $table->string('prenom');            // First name
            $table->string('email')->unique();   // Login email (must be unique)
            $table->string('password');          // Bcrypt hashed password
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();

            // --- Role system ---
            // Determines which features this user can access
            $table->enum('role', ['patient', 'medecin', 'admin'])->default('patient');

            // --- Account status ---
            // 'pending'  → doctor waiting for admin validation
            // 'active'   → normal usable account
            // 'inactive' → disabled by admin
            $table->enum('statut', ['pending', 'active', 'inactive'])->default('active');

            $table->rememberToken();            // For "remember me" sessions
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();               // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
