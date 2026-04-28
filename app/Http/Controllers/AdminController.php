<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RendezVous;
use Illuminate\Http\Request;

/**
 * CONTROLLER: AdminController
 * 
 * All admin-only endpoints. This controller requires:
 *   - auth:sanctum middleware (must be logged in)
 *   - role:admin middleware (must be an admin)
 * 
 * Features covered:
 *   Feature 3 - Patient management  (US 3.1–3.4)
 *   Feature 4 - Doctor management   (US 4.1–4.3)
 *   Feature 9 - Administration      (US 9.1–9.3)
 */
class AdminController extends Controller
{
    // =========================================================================
    // FEATURE 3 — PATIENT MANAGEMENT
    // =========================================================================

    /**
     * GET /api/admin/patients
     * 
     * US 3.4 - List all patients with optional search filter.
     */
    public function listPatients(Request $request)
    {
        $query = User::where('role', 'patient');

        // Optional search by name or email
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%$search%")
                  ->orWhere('prenom', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        // Return paginated results (20 per page)
        return response()->json($query->paginate(20));
    }

    /**
     * POST /api/admin/patients
     * 
     * US 3.1 - Admin manually creates a patient account.
     * The patient receives an 'active' account immediately.
     */
    public function createPatient(Request $request)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'telephone' => 'nullable|string|max:20',
            'adresse'   => 'nullable|string|max:255',
        ]);

        $patient = User::create(array_merge($data, [
            'role'   => 'patient',
            'statut' => 'active',
        ]));

        return response()->json([
            'message' => 'Patient créé avec succès.',
            'patient' => $patient,
        ], 201);
    }

    /**
     * PUT /api/admin/patients/{id}
     * 
     * US 3.2 - Admin updates a patient's information.
     */
    public function updatePatient(Request $request, int $id)
    {
        // Find the patient and make sure they are actually a patient
        $patient = User::where('id', $id)->where('role', 'patient')->firstOrFail();

        $data = $request->validate([
            'nom'       => 'sometimes|string|max:100',
            'prenom'    => 'sometimes|string|max:100',
            'email'     => 'sometimes|email|unique:users,email,' . $id,
            'telephone' => 'sometimes|nullable|string|max:20',
            'adresse'   => 'sometimes|nullable|string|max:255',
        ]);

        $patient->update($data);

        return response()->json(['message' => 'Patient mis à jour.', 'patient' => $patient]);
    }

    /**
     * PATCH /api/admin/patients/{id}/desactiver
     * 
     * US 3.3 - Admin deactivates a patient account.
     * Sets statut = 'inactive' (soft disable, not hard delete).
     */
    public function deactivatePatient(int $id)
    {
        $patient = User::where('id', $id)->where('role', 'patient')->firstOrFail();
        $patient->update(['statut' => 'inactive']);

        return response()->json(['message' => 'Compte patient désactivé.']);
    }

    // =========================================================================
    // FEATURE 4 — DOCTOR MANAGEMENT
    // =========================================================================

    /**
     * GET /api/admin/medecins
     * 
     * Lists all doctors, including their professional profile.
     * Can filter by validation status: ?valide=0 to see pending doctors.
     */
    public function listMedecins(Request $request)
    {
        $query = User::where('role', 'medecin')->with('doctorProfile');

        // Filter by validation status: ?valide=0 shows unvalidated doctors
        if ($request->has('valide')) {
            $query->whereHas('doctorProfile', fn($q) =>
                $q->where('valide', (bool) $request->query('valide'))
            );
        }

        return response()->json($query->paginate(20));
    }

    /**
     * PATCH /api/admin/medecins/{id}/valider
     * 
     * US 4.1 - Admin validates a doctor's account after verification.
     * Sets:
     *   - doctor_profiles.valide = true
     *   - users.statut = 'active'
     */
    public function validateMedecin(int $id)
    {
        $doctor = User::where('id', $id)->where('role', 'medecin')->firstOrFail();

        // Validate the professional profile
        $doctor->doctorProfile()->update(['valide' => true]);

        // Activate the user account so the doctor can log in
        $doctor->update(['statut' => 'active']);

        return response()->json(['message' => 'Compte médecin validé.']);
    }

    /**
     * PUT /api/admin/medecins/{id}
     * 
     * US 4.2 - Admin edits a doctor's profile.
     */
    public function updateMedecin(Request $request, int $id)
    {
        $doctor = User::where('id', $id)->where('role', 'medecin')->firstOrFail();

        // Update base user fields
        $userData = $request->validate([
            'nom'       => 'sometimes|string|max:100',
            'prenom'    => 'sometimes|string|max:100',
            'telephone' => 'sometimes|nullable|string|max:20',
        ]);
        $doctor->update($userData);

        // Update professional profile fields
        $profileData = $request->validate([
            'specialite'  => 'sometimes|string|max:100',
            'cabinet'     => 'sometimes|nullable|string|max:200',
            'ville'       => 'sometimes|nullable|string|max:100',
        ]);
        $doctor->doctorProfile()->update($profileData);

        return response()->json(['message' => 'Médecin mis à jour.']);
    }

    /**
     * PATCH /api/admin/medecins/{id}/desactiver
     * 
     * US 4.3 - Admin deactivates a doctor's account.
     */
    public function deactivateMedecin(int $id)
    {
        $doctor = User::where('id', $id)->where('role', 'medecin')->firstOrFail();
        $doctor->update(['statut' => 'inactive']);

        return response()->json(['message' => 'Compte médecin désactivé.']);
    }

    // =========================================================================
    // FEATURE 9 — ADMINISTRATION
    // =========================================================================

    /**
     * GET /api/admin/users
     * 
     * US 9.1 - View all users regardless of role.
     */
    public function listAllUsers(Request $request)
    {
        $query = User::query();

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        return response()->json($query->paginate(20));
    }

    /**
     * PATCH /api/admin/users/{id}/role
     * 
     * US 9.2 - Assign a role to a user.
     */
    public function assignRole(Request $request, int $id)
    {
        $data = $request->validate([
            'role' => 'required|in:patient,medecin,admin',
        ]);

        $user = User::findOrFail($id);
        $user->update(['role' => $data['role']]);

        return response()->json(['message' => 'Rôle mis à jour.', 'user' => $user]);
    }

    /**
     * GET /api/admin/statistiques
     * 
     * US 9.3 - Dashboard statistics for the admin panel.
     */
    public function statistiques()
{
    try {
        return response()->json([
            'total_patients'        => User::where('role', 'patient')->count(),
            'total_medecins'        => User::where('role', 'medecin')->where('statut', 'active')->count(),
            'medecins_en_attente'   => User::where('role', 'medecin')->where('statut', 'pending')->count(),
            'total_rendez_vous'     => \App\Models\RendezVous::count(),
            'rendez_vous_confirmes' => \App\Models\RendezVous::where('statut', 'confirme')->count(),
            'rendez_vous_annules'   => \App\Models\RendezVous::where('statut', 'annule')->count(),
            'rendez_vous_ce_mois'   => \App\Models\RendezVous::whereMonth('date_heure', now()->month)
                                        ->whereYear('date_heure', now()->year)
                                        ->count(),
        ]);
    } catch (\Exception $e) {
        // This will show the REAL error in the response so you can see it
        return response()->json(['message' => $e->getMessage()], 500);
    }
}
}
