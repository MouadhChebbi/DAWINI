<?php

namespace App\Http\Controllers;

use App\Models\DocumentMedical;
use App\Models\RendezVous;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * CONTROLLER: DocumentController
 *
 * Handles Feature 6 — Medical History:
 *   US 6.2 - Patient views their medical documents
 *   US 6.3 - Doctor uploads a consultation report for a patient
 */
class DocumentController extends Controller
{
    // =========================================================================
    // US 6.2 — PATIENT VIEWS DOCUMENTS
    // =========================================================================

    /**
     * GET /api/mes-documents
     *
     * Returns all medical documents belonging to the logged-in patient.
     */
    public function index(Request $request)
    {
        $docs = DocumentMedical::where('patient_id', $request->user()->id)
                               ->with('medecin:id,nom,prenom')
                               ->orderBy('created_at', 'desc')
                               ->get();

        return response()->json($docs);
    }

    /**
     * GET /api/mes-documents/{id}/download
     *
     * Streams the file directly to the patient.
     * Only accessible by the patient who owns it.
     */
    public function download(Request $request, int $id)
    {
        $doc = DocumentMedical::where('id', $id)
                              ->where('patient_id', $request->user()->id)
                              ->firstOrFail();

        // Check the file actually exists on disk
        if (!Storage::disk('private')->exists($doc->fichier_path)) {
            return response()->json(['message' => 'Fichier introuvable.'], 404);
        }

        // Stream the file as a download response
        return Storage::disk('private')->download($doc->fichier_path);
    }

    // =========================================================================
    // US 6.3 — DOCTOR UPLOADS REPORT
    // =========================================================================

    /**
     * POST /api/documents
     *
     * Doctor uploads a medical document for a patient.
     * Can optionally link it to a specific appointment.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id'     => 'required|exists:users,id',
            'rendez_vous_id' => 'nullable|exists:rendez_vous,id',
            'titre'          => 'required|string|max:255',
            'type'           => 'required|in:compte_rendu,ordonnance,analyse,autre',
            // Accept PDF or common image formats, max 10MB
            'fichier'        => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $doctor = $request->user();

        // If linked to an appointment, verify the doctor owns that appointment
        if (!empty($data['rendez_vous_id'])) {
            RendezVous::where('id', $data['rendez_vous_id'])
                      ->where('medecin_id', $doctor->id)
                      ->firstOrFail();
        }

        // Store the file in the 'private' disk (not publicly accessible)
        // Path example: documents/patient_5/report_2024.pdf
        $path = $request->file('fichier')->store(
            'documents/patient_' . $data['patient_id'],
            'private'
        );

        $doc = DocumentMedical::create([
            'patient_id'     => $data['patient_id'],
            'medecin_id'     => $doctor->id,
            'rendez_vous_id' => $data['rendez_vous_id'] ?? null,
            'titre'          => $data['titre'],
            'type'           => $data['type'],
            'fichier_path'   => $path,
        ]);

        return response()->json([
            'message'  => 'Document ajouté.',
            'document' => $doc,
        ], 201);
    }

    // =========================================================================
    // DOCTOR VIEWS PATIENT DOCUMENTS
    // =========================================================================

    /**
     * GET /api/patients/{patientId}/documents
     *
     * Doctor views all documents for a specific patient.
     * Only allowed if the doctor has had at least one appointment with the patient.
     */
    public function patientDocuments(Request $request, int $patientId)
    {
        $doctor = $request->user();

        // Security: doctor can only access documents of patients they've seen
        $hasRelationship = RendezVous::where('medecin_id', $doctor->id)
                                     ->where('patient_id', $patientId)
                                     ->exists();

        if (!$hasRelationship) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $docs = DocumentMedical::where('patient_id', $patientId)
                               ->with('medecin:id,nom,prenom')
                               ->orderBy('created_at', 'desc')
                               ->get();

        return response()->json($docs);
    }
}