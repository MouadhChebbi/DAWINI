<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * MODEL: User
 * 
 * Represents any user in the system (patient, médecin, admin).
 * The "role" column distinguishes between them.
 * 
 * We use Laravel Sanctum for API token authentication.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Fields that can be mass-assigned (e.g. User::create([...]))
     * Never put 'role' or 'statut' here — those should only be set explicitly.
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'adresse',
        'role',
        'statut',
    ];

    /**
     * Fields hidden from JSON output (e.g. API responses)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Auto-cast these columns to native PHP types
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed', // Laravel 10+ auto-hashes on set
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * A doctor has one professional profile.
     * Usage: $user->doctorProfile
     */
    public function doctorProfile()
    {
        return $this->hasOne(DoctorProfile::class, 'user_id');
    }

    /**
     * A doctor has many availability slots.
     * Usage: $doctor->disponibilites
     */
    public function disponibilites()
    {
        return $this->hasMany(Disponibilite::class, 'medecin_id');
    }

    /**
     * A doctor has many blocked slots.
     * Usage: $doctor->blocages
     */
    public function blocages()
    {
        return $this->hasMany(Blocage::class, 'medecin_id');
    }

    /**
     * A patient has many appointments (as the patient side).
     * Usage: $patient->rendezVousPatient
     */
    public function rendezVousPatient()
    {
        return $this->hasMany(RendezVous::class, 'patient_id');
    }

    /**
     * A doctor has many appointments (as the doctor side).
     * Usage: $doctor->rendezVousMedecin
     */
    public function rendezVousMedecin()
    {
        return $this->hasMany(RendezVous::class, 'medecin_id');
    }

    /**
     * A patient has many medical documents.
     * Usage: $patient->documents
     */
    public function documents()
    {
        return $this->hasMany(DocumentMedical::class, 'patient_id');
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /** Check if the user is a doctor */
    public function isMedecin(): bool
    {
        return $this->role === 'medecin';
    }

    /** Check if the user is a patient */
    public function isPatient(): bool
    {
        return $this->role === 'patient';
    }

    /** Check if the user is an admin */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Check if the account is active */
    public function isActive(): bool
    {
        return $this->statut === 'active';
    }
}
