<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DoctorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * CONTROLLER: AuthController
 * 
 * Handles Sprint 1 — Authentication & User Management:
 *   US 1.1 - Register with email & password
 *   US 1.2 - Login
 *   US 1.3 - Forgot / Reset password
 *   US 1.6 - Choose role on registration (patient or médecin)
 * 
 * Uses Laravel Sanctum for stateless API tokens.
 */
class AuthController extends Controller
{
    // =========================================================================
    // US 1.1 & 1.6 — REGISTRATION
    // =========================================================================

    /**
     * POST /api/register
     * 
     * Creates a new user account. If the role is 'medecin', also
     * creates a pending doctor profile that admin must validate.
     */
    public function register(Request $request)
    {
        // Validate all incoming fields
        $data = $request->validate([
            'nom'           => 'required|string|max:100',
            'prenom'        => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'telephone'     => 'nullable|string|max:20',
            'adresse'       => 'nullable|string|max:255',

            // Role must be explicitly chosen: 'patient' or 'medecin'
            // Admin accounts are NOT created via this endpoint
            'role'          => 'required|in:patient,medecin',

            // Doctor-specific fields (required only when role = medecin)
            'specialite'    => 'required_if:role,medecin|string|max:100',
            'numero_rpps'   => 'required_if:role,medecin|string|unique:doctor_profiles,numero_rpps',
            'cabinet'       => 'nullable|string|max:200',
            'ville'         => 'nullable|string|max:100',
            'code_postal'   => 'nullable|string|max:10',
        ]);

        // Doctors start as 'pending' — they cannot log in until admin validates them
        $statut = $data['role'] === 'medecin' ? 'pending' : 'active';

        // Create the user record
        $user = User::create([
            'nom'       => $data['nom'],
            'prenom'    => $data['prenom'],
            'email'     => $data['email'],
            'password'  => $data['password'], // Automatically hashed by User model cast
            'telephone' => $data['telephone'] ?? null,
            'adresse'   => $data['adresse'] ?? null,
            'role'      => $data['role'],
            'statut'    => $statut,
        ]);

        // If registering as a doctor, create the professional profile
        if ($data['role'] === 'medecin') {
            DoctorProfile::create([
                'user_id'      => $user->id,
                'specialite'   => $data['specialite'],
                'numero_rpps'  => $data['numero_rpps'],
                'cabinet'      => $data['cabinet'] ?? null,
                'ville'        => $data['ville'] ?? null,
                'code_postal'  => $data['code_postal'] ?? null,
                'valide'       => false, // Admin must validate
            ]);
        }

        return response()->json([
            'message' => $data['role'] === 'medecin'
                ? 'Inscription réussie. Votre compte est en attente de validation par un administrateur.'
                : 'Inscription réussie.',
            'user' => $user->only(['id', 'nom', 'prenom', 'email', 'role', 'statut']),
        ], 201);
    }

    // =========================================================================
    // US 1.2 — LOGIN
    // =========================================================================

    /**
     * POST /api/login
     * 
     * Authenticates the user and returns a Sanctum API token.
     * The token must be included in all subsequent requests as:
     *   Authorization: Bearer {token}
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Find the user by email
        $user = User::where('email', $data['email'])->first();

        // Verify email exists and password matches
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }

        // Block inactive accounts (disabled by admin)
        if ($user->statut === 'inactive') {
            return response()->json(['message' => 'Votre compte a été désactivé.'], 403);
        }

        // Block pending doctors (waiting for admin validation)
        if ($user->statut === 'pending') {
            return response()->json(['message' => 'Votre compte est en attente de validation.'], 403);
        }

        // Delete any old tokens and create a fresh one
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Connexion réussie.',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user->only(['id', 'nom', 'prenom', 'email', 'role']),
        ]);
    }

    // =====================================================================
// FORGOT PASSWORD — sends a 6-digit code by email
// =====================================================================
public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    // Generate a random 6-digit code
    $code = strval(random_int(100000, 999999));

    // Save it in the password_reset_tokens table
    \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        [
            'email'      => $request->email,
            'token'      => bcrypt($code), // store hashed
            'code'       => $code,         // store plain for email
            'created_at' => now(),
        ]
    );

    // Send the code by email
    try {
        \Mail::raw(
            "Bonjour,\n\nVotre code de réinitialisation de mot de passe est :\n\n" .
            "  ► $code ◄\n\n" .
            "Ce code expire dans 10 minutes.\n" .
            "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.",
            function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Code de réinitialisation — Dawini');
            }
        );
    } catch (\Exception $e) {
        \Log::warning('Mail non envoyé: ' . $e->getMessage());
    }

    return response()->json([
        'message' => 'Code envoyé par email.',
    ]);
}

// =====================================================================
// VERIFY CODE — checks the code is valid before allowing password change
// =====================================================================
public function verifyCode(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code'  => 'required|string|size:6',
    ]);

    $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

    if (!$record) {
        return response()->json(['message' => 'Aucune demande trouvée pour cet email.'], 400);
    }

    // Check expiry — 10 minutes
    if (now()->diffInMinutes($record->created_at) > 10) {
        return response()->json(['message' => 'Code expiré. Demandez un nouveau code.'], 400);
    }

    // Compare code
    if ($record->code !== $request->code) {
        return response()->json(['message' => 'Code incorrect.'], 400);
    }

    return response()->json(['message' => 'Code valide.']);
}

// =====================================================================
// RESET PASSWORD — actually changes the password
// =====================================================================
public function resetPassword(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'code'     => 'required|string|size:6',
        'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
    ]);

    $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

    if (!$record) {
        return response()->json(['message' => 'Demande invalide.'], 400);
    }

    if (now()->diffInMinutes($record->created_at) > 10) {
        return response()->json(['message' => 'Code expiré.'], 400);
    }

    if ($record->code !== $request->code) {
        return response()->json(['message' => 'Code incorrect.'], 400);
    }

    // Update password
    \App\Models\User::where('email', $request->email)
        ->update(['password' => bcrypt($request->password)]);

    // Delete used token
    \Illuminate\Support\Facades\DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->delete();

    return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
}

    // =========================================================================
    // LOGOUT
    // =========================================================================

    /**
     * POST /api/logout
     * 
     * Revokes the current user's API token.
     * Requires: Authorization: Bearer {token}
     */
    public function logout(Request $request)
    {
        // Delete only the token used in this request
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }
}
