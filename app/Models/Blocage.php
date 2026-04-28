<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MODEL: Blocage
 * 
 * A manually blocked time window for a doctor.
 * During a blocage, no appointments can be booked.
 */
class Blocage extends Model
{
    protected $fillable = [
        'medecin_id',
        'debut',    // Start datetime
        'fin',      // End datetime
        'raison',   // Optional note
    ];

    protected $casts = [
        'debut' => 'datetime',
        'fin'   => 'datetime',
    ];

    /** The doctor who owns this block */
    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }
}
