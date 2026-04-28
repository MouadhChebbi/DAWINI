<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MODEL: DoctorProfile
 * 
 * Professional details for a doctor user.
 * Always accessed via the User model: $user->doctorProfile
 */
class DoctorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'specialite',
        'numero_rpps',
        'cabinet',
        'ville',
        'code_postal',
        'valide',
    ];

    protected $casts = [
        'valide' => 'boolean',
    ];

    /**
     * The user (doctor) this profile belongs to.
     * Usage: $profile->user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
