<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * MODEL: RendezVous
 * 
 * An appointment between a patient and a doctor.
 * 
 * Possible statuses:
 *   confirme  → booked and upcoming
 *   annule    → cancelled
 *   termine   → past, completed appointment
 */
class RendezVous extends Model
{
    protected $table = 'rendez_vous';

    protected $fillable = [
        'patient_id',
        'medecin_id',
        'date_heure',
        'duree',
        'statut',
        'motif',
        'compte_rendu',
    ];

    protected $casts = [
        'date_heure' => 'datetime',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /** The patient who booked this appointment */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /** The doctor for this appointment */
    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    /** Medical documents attached to this appointment */
    public function documents()
    {
        return $this->hasMany(DocumentMedical::class, 'rendez_vous_id');
    }

    // =========================================================================
    // BUSINESS LOGIC HELPERS
    // =========================================================================

    /**
     * Can this appointment still be cancelled?
     * Rule: patient can cancel if more than 24h remain (US 5.5)
     */
    public function canBeCancelled(): bool
    {
        if ($this->statut !== 'confirme') {
            return false; // Already cancelled or done
        }

        // Check if appointment is more than 24h away
        return Carbon::now()->diffInHours($this->date_heure, false) > 24;
    }

    /**
     * Computed end time of the appointment.
     * Example: starts 10:00, duration 30min → ends 10:30
     */
    public function getHeureFinAttribute(): Carbon
    {
        return $this->date_heure->copy()->addMinutes($this->duree);
    }
}
