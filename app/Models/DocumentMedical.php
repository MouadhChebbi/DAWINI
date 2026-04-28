<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * MODEL: DocumentMedical
 * 
 * A medical file (PDF, image) uploaded by a doctor for a patient.
 */
class DocumentMedical extends Model
{
    protected $table = 'documents_medicaux';
    protected $fillable = [
        'patient_id',
        'medecin_id',
        'rendez_vous_id',
        'titre',
        'type',
        'fichier_path',
    ];

    /** The patient this document belongs to */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /** The doctor who uploaded it */
    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    /** The appointment it is linked to (optional) */
    public function rendezVous()
    {
        return $this->belongsTo(RendezVous::class, 'rendez_vous_id');
    }

    /**
     * Get a public URL to download the file.
     * Usage: $document->url
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->fichier_path);
    }
}
