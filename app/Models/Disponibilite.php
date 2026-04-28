<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MODEL: Disponibilite
 * 
 * A recurring weekly time window when a doctor is available.
 * E.g. "Every Tuesday 14:00–17:00 with 20-minute slots"
 */
class Disponibilite extends Model
{
    protected $fillable = [
        'medecin_id',
        'jour_semaine',   // 1–7 (Monday to Sunday)
        'heure_debut',    // "09:00"
        'heure_fin',      // "12:00"
        'duree_creneau',  // 30 (minutes per appointment)
    ];

    /** The doctor this schedule belongs to */
    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }
}
