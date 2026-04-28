<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| API ROUTES — DAWINI
|--------------------------------------------------------------------------
|
| All routes here are prefixed with /api (configured in bootstrap/app.php).
|
| Middleware used:
|   auth:sanctum  → requires a valid Bearer token
|   role:X        → requires the user to have role X (our custom middleware)
|
| Route groups are organized by feature from the backlog.
|
*/

// =============================================================================
// PUBLIC ROUTES — No authentication required
// =============================================================================

// Feature 1 — Authentication (US 1.1, 1.2, 1.3, 1.6)
Route::post('/register',        [AuthController::class, 'register']);
Route::post('/login',           [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password',  [AuthController::class, 'resetPassword']);
Route::post('/verify-reset-code', [AuthController::class, 'verifyCode']);

// Feature 5 — Public doctor search (US 5.2)
// Patients search BEFORE logging in, so this is public
Route::get('/medecins/recherche', [RendezVousController::class, 'searchDoctors']);

// Feature 5 — View available slots (US 5.3)
// Also public — patient can browse slots before logging in
Route::get('/medecins/{id}/creneaux', [RendezVousController::class, 'getCreneaux']);


// =============================================================================
// AUTHENTICATED ROUTES — Require valid Sanctum token
// =============================================================================
    
Route::middleware('auth:sanctum')->group(function () {

    // -------------------------------------------------------------------------
    // Auth — Logout
    // -------------------------------------------------------------------------
    Route::post('/logout', [AuthController::class, 'logout']);

    // -------------------------------------------------------------------------
    // Feature 2 — Profile Management (US 2.1, 2.2, 2.3)
    // -------------------------------------------------------------------------
    Route::get('/profile',            [ProfileController::class, 'show']);
    Route::put('/profile',            [ProfileController::class, 'update']);

    // Doctor-specific profile update — only doctors can call this
    Route::put('/profile/medecin',
        [ProfileController::class, 'updateDoctorProfile']
    )->middleware('role:medecin');


    // -------------------------------------------------------------------------
    // Feature 5 — Appointments (patient side)
    // -------------------------------------------------------------------------
    Route::middleware('role:patient')->group(function () {
        // US 5.4 — Book appointment
        Route::post('/rendez-vous',                      [RendezVousController::class, 'store']);

        // US 5.5 — Cancel appointment
        Route::patch('/rendez-vous/{id}/annuler',        [RendezVousController::class, 'cancel']);

        // US 6.1 — Patient views their appointment history
        Route::get('/mes-rendez-vous',                   [RendezVousController::class, 'myAppointments']);

        // US 6.2 — Patient views their medical documents
        Route::get('/mes-documents',                     [DocumentController::class, 'index']);
        Route::get('/mes-documents/{id}/download',       [DocumentController::class, 'download']);
    });


    // -------------------------------------------------------------------------
    // Feature 5 — Appointments (doctor side)
    // -------------------------------------------------------------------------
    Route::middleware('role:medecin')->group(function () {
        // US 5.1 — Manage availability schedule
        Route::get('/disponibilites',         [RendezVousController::class, 'myDisponibilites']);
        Route::post('/disponibilites',        [RendezVousController::class, 'storeDisponibilite']);
        Route::delete('/disponibilites/{id}', [RendezVousController::class, 'deleteDisponibilite']);

        // US 5.9 — Manually block a time slot
        Route::post('/blocages', [RendezVousController::class, 'storeBlocage']);

        // US 5.8 — Doctor views their agenda
        Route::get('/agenda', [RendezVousController::class, 'agenda']);

        // US 6.3 — Doctor uploads a medical document for a patient
        Route::post('/documents',                              [DocumentController::class, 'store']);
        Route::get('/patients/{patientId}/documents',          [DocumentController::class, 'patientDocuments']);
    });


    // -------------------------------------------------------------------------
    // Features 3, 4, 9 — Admin panel
    // All routes here require role = admin
    // -------------------------------------------------------------------------
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        // Feature 9 — User management (US 9.1, 9.2, 9.3)
        Route::get('/users',                  [AdminController::class, 'listAllUsers']);
        Route::patch('/users/{id}/role',      [AdminController::class, 'assignRole']);
        Route::get('/statistiques',           [AdminController::class, 'statistiques']);

        // Feature 3 — Patient management (US 3.1, 3.2, 3.3, 3.4)
        Route::get('/patients',               [AdminController::class, 'listPatients']);
        Route::post('/patients',              [AdminController::class, 'createPatient']);
        Route::put('/patients/{id}',          [AdminController::class, 'updatePatient']);
        Route::patch('/patients/{id}/desactiver', [AdminController::class, 'deactivatePatient']);

        // Feature 4 — Doctor management (US 4.1, 4.2, 4.3)
        Route::get('/medecins',               [AdminController::class, 'listMedecins']);
        Route::patch('/medecins/{id}/valider',    [AdminController::class, 'validateMedecin']);
        Route::put('/medecins/{id}',          [AdminController::class, 'updateMedecin']);
        Route::patch('/medecins/{id}/desactiver', [AdminController::class, 'deactivateMedecin']);
    });

});
