<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * CONTROLLER: ProfileController
 * 
 * Handles user profile management (Feature 2):
 *   US 2.1 - Complete profile (name, phone, address)
 *   US 2.2 - Doctor adds professional info (specialty, RPPS, cabinet)
 *   US 2.3 - User updates their personal info
 */
class ProfileController extends Controller
{
    // =========================================================================
    // US 2.1 & 2.3 — VIEW & UPDATE PERSONAL PROFILE
    // =========================================================================

    /**
     * GET /api/profile
     * 
     * Returns the authenticated user's profile.
     * For doctors, also includes the doctor_profiles data.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Load doctor profile relationship if user is a doctor
        if ($user->isMedecin()) {
            $user->load('doctorProfile');
        }

        return response()->json(['user' => $user]);
    }

    /**
     * PUT /api/profile
     * 
     * Updates personal information for any user type.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'nom'       => 'sometimes|string|max:100',
            'prenom'    => 'sometimes|string|max:100',
            'telephone' => 'sometimes|nullable|string|max:20',
            'adresse'   => 'sometimes|nullable|string|max:255',
        ]);

        // Only update fields that were actually sent
        $user->update($data);

        return response()->json([
            'message' => 'Profil mis à jour.',
            'user'    => $user->fresh(),
        ]);
    }

    // =========================================================================
    // US 2.2 — DOCTOR PROFESSIONAL PROFILE
    // =========================================================================

    /**
     * PUT /api/profile/medecin
     * 
     * Updates or creates a doctor's professional information.
     * Only accessible by users with role = medecin.
     */
    public function updateDoctorProfile(Request $request)
    {
        $user = $request->user();

        // Safety check — this route should be protected by middleware,
        // but we double-check here
        if (!$user->isMedecin()) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $data = $request->validate([
            'specialite'  => 'sometimes|string|max:100',
            'numero_rpps' => 'sometimes|string|unique:doctor_profiles,numero_rpps,' . $user->doctorProfile?->id,
            'cabinet'     => 'sometimes|nullable|string|max:200',
            'ville'       => 'sometimes|nullable|string|max:100',
            'code_postal' => 'sometimes|nullable|string|max:10',
        ]);

        // updateOrCreate: update if profile exists, otherwise create it
        $profile = $user->doctorProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return response()->json([
            'message' => 'Profil professionnel mis à jour.',
            'profile' => $profile,
        ]);
    }
}
