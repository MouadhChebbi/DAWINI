<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RendezVous;
use App\Models\Disponibilite;
use App\Models\Blocage;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * CONTROLLER: RendezVousController
 * 
 * Handles Feature 5 — Planning & Appointment management:
 *   US 5.1 - Doctor sets availability slots
 *   US 5.2 - Patient searches doctors by specialty and/or location
 *   US 5.3 - Patient views available slots for a doctor
 *   US 5.4 - Patient books an appointment
 *   US 5.5 - Patient cancels an appointment (if > 24h away)
 *   US 5.6 - Email confirmation (handled in BookAppointment via Events)
 *   US 5.8 - Doctor views their agenda
 *   US 5.9 - Doctor blocks a time slot manually
 */
class RendezVousController extends Controller
{
    // =========================================================================
    // US 5.2 — SEARCH DOCTORS
    // =========================================================================

    /**
     * GET /api/medecins/recherche?specialite=cardiologie&ville=Paris
     * 
     * Allows a patient to find doctors by specialty and/or city.
     * Only returns validated, active doctors.
     */
    public function searchDoctors(Request $request)
    {
        $request->validate([
            'specialite' => 'nullable|string|max:100',
            'ville'      => 'nullable|string|max:100',
        ]);

        $query = User::where('role', 'medecin')
                     ->where('statut', 'active')
                     ->with('doctorProfile')
                     ->whereHas('doctorProfile', function ($q) use ($request) {
                         // Must be validated by admin
                         $q->where('valide', true);

                         // Filter by specialty if provided
                         if ($request->filled('specialite')) {
                             $q->where('specialite', 'like', '%' . $request->specialite . '%');
                         }

                         // Filter by city if provided
                         if ($request->filled('ville')) {
                             $q->where('ville', 'like', '%' . $request->ville . '%');
                         }
                     });

        return response()->json($query->paginate(10));
    }

    // =========================================================================
    // US 5.1 — DOCTOR SETS AVAILABILITY
    // =========================================================================

    /**
     * POST /api/disponibilites
     * 
     * Doctor creates a recurring weekly availability slot.
     * Example: "Every Monday 09:00–12:00, 30min appointments"
     */
    public function storeDisponibilite(Request $request)
    {
        $data = $request->validate([
            'jour_semaine'  => 'required|integer|between:1,7',
            'heure_debut'   => 'required|date_format:H:i',
            'heure_fin'     => 'required|date_format:H:i|after:heure_debut',
            'duree_creneau' => 'required|integer|in:15,20,30,45,60',
        ]);

        $dispo = $request->user()->disponibilites()->create($data);

        return response()->json([
            'message'       => 'Disponibilité créée.',
            'disponibilite' => $dispo,
        ], 201);
    }

    /**
     * GET /api/disponibilites
     * 
     * Returns the currently logged-in doctor's availability schedule.
     */
    public function myDisponibilites(Request $request)
    {
        $dispos = $request->user()->disponibilites()->orderBy('jour_semaine')->get();
        return response()->json($dispos);
    }

    /**
     * DELETE /api/disponibilites/{id}
     * 
     * Doctor removes an availability slot.
     */
    public function deleteDisponibilite(Request $request, int $id)
    {
        $dispo = Disponibilite::where('id', $id)
                              ->where('medecin_id', $request->user()->id)
                              ->firstOrFail();
        $dispo->delete();

        return response()->json(['message' => 'Disponibilité supprimée.']);
    }

    // =========================================================================
    // US 5.9 — DOCTOR BLOCKS A SLOT
    // =========================================================================

    /**
     * POST /api/blocages
     * 
     * Doctor manually blocks a datetime range (holiday, meeting, etc.)
     */
    public function storeBlocage(Request $request)
    {
        $data = $request->validate([
            'debut'  => 'required|date|after:now',
            'fin'    => 'required|date|after:debut',
            'raison' => 'nullable|string|max:255',
        ]);

        $blocage = $request->user()->blocages()->create($data);

        return response()->json([
            'message' => 'Créneau bloqué.',
            'blocage' => $blocage,
        ], 201);
    }

    // =========================================================================
    // US 5.3 — VIEW AVAILABLE SLOTS FOR A DOCTOR
    // =========================================================================

    /**
     * GET /api/medecins/{id}/creneaux?date=2024-06-10
     * 
     * Returns all available time slots for a given doctor on a given date.
     * 
     * Algorithm:
     *   1. Get doctor's recurring schedule for that day of the week
     *   2. Generate all possible slots (e.g. 09:00, 09:30, 10:00 ...)
     *   3. Remove slots that are already booked
     *   4. Remove slots that are blocked by the doctor
     *   5. Return the remaining free slots
     */
    public function getCreneaux(Request $request, int $medecinId)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date   = Carbon::parse($request->date);
        $dayNum = $date->isoWeekday(); // 1=Monday … 7=Sunday

        // Step 1: Get availability rules for this day of the week
        $dispos = Disponibilite::where('medecin_id', $medecinId)
                               ->where('jour_semaine', $dayNum)
                               ->get();

        if ($dispos->isEmpty()) {
            return response()->json(['creneaux' => [], 'message' => 'Pas de disponibilité ce jour.']);
        }

        // Step 2: Generate all theoretical slots from the availability windows
        $allSlots = [];
        foreach ($dispos as $dispo) {
            $current = Carbon::parse($date->toDateString() . ' ' . $dispo->heure_debut);
            $end     = Carbon::parse($date->toDateString() . ' ' . $dispo->heure_fin);

            while ($current->copy()->addMinutes($dispo->duree_creneau)->lte($end)) {
                $allSlots[] = $current->copy();
                $current->addMinutes($dispo->duree_creneau);
            }
        }

        // Step 3: Get booked appointments for that day to exclude them
        $booked = RendezVous::where('medecin_id', $medecinId)
                            ->where('statut', 'confirme')
                            ->whereDate('date_heure', $date->toDateString())
                            ->pluck('date_heure')
                            ->map(fn($dt) => Carbon::parse($dt)->format('H:i'))
                            ->toArray();

        // Step 4: Get blocked slots for that day
        $blocages = Blocage::where('medecin_id', $medecinId)
                          ->where('debut', '<=', $date->copy()->endOfDay())
                          ->where('fin', '>=', $date->copy()->startOfDay())
                          ->get();

        // Step 5: Filter out booked and blocked slots
        $available = array_filter($allSlots, function (Carbon $slot) use ($booked, $blocages) {
            // Skip if this slot time is already booked
            if (in_array($slot->format('H:i'), $booked)) {
                return false;
            }

            // Skip if this slot falls within a blocage
            foreach ($blocages as $blocage) {
                if ($slot->between($blocage->debut, $blocage->fin)) {
                    return false;
                }
            }

            return true;
        });

        // Format for JSON response
        $result = array_map(fn($slot) => $slot->format('H:i'), array_values($available));

        return response()->json([
            'date'     => $date->toDateString(),
            'creneaux' => $result,
        ]);
    }

    // =========================================================================
    // US 5.4 — BOOK AN APPOINTMENT
    // =========================================================================

    /**
     * POST /api/rendez-vous
     * 
     * Patient books an appointment with a doctor.
     * After booking, a confirmation email is sent (US 5.6).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'medecin_id' => 'required|exists:users,id',
            'date_heure' => 'required|date|after:now',
            'motif'      => 'nullable|string|max:500',
        ]);

        $patient   = $request->user();
        $medecinId = $data['medecin_id'];
        $dateHeure = Carbon::parse($data['date_heure']);

        // Verify the doctor exists and is active
        $doctor = User::where('id', $medecinId)
                      ->where('role', 'medecin')
                      ->where('statut', 'active')
                      ->firstOrFail();

        // Double-check: this slot must still be available
        $alreadyBooked = RendezVous::where('medecin_id', $medecinId)
                                   ->where('date_heure', $dateHeure)
                                   ->where('statut', 'confirme')
                                   ->exists();

        if ($alreadyBooked) {
            return response()->json(['message' => 'Ce créneau n\'est plus disponible.'], 409);
        }

        // Get slot duration from doctor's disponibilite (default 30 if not found)
        $dayNum = $dateHeure->isoWeekday();
        $dispo  = Disponibilite::where('medecin_id', $medecinId)
                               ->where('jour_semaine', $dayNum)
                               ->first();
        $duree  = $dispo?->duree_creneau ?? 30;

        // Create the appointment
        $rdv = RendezVous::create([
            'patient_id' => $patient->id,
            'medecin_id' => $medecinId,
            'date_heure' => $dateHeure,
            'duree'      => $duree,
            'statut'     => 'confirme',
            'motif'      => $data['motif'] ?? null,
        ]);

        // TODO (US 5.6): Send confirmation email
        // event(new AppointmentBooked($rdv));

        return response()->json([
            'message'     => 'Rendez-vous confirmé.',
            'rendez_vous' => $rdv->load(['patient', 'medecin']),
        ], 201);
    }

    // =========================================================================
    // US 5.5 — CANCEL AN APPOINTMENT
    // =========================================================================

    /**
     * PATCH /api/rendez-vous/{id}/annuler
     * 
     * Patient cancels an appointment.
     * Only allowed if the appointment is more than 24h away.
     */
    public function cancel(Request $request, int $id)
    {
        $user = $request->user();

        // Find the appointment — patient can only cancel their own
        $rdv = RendezVous::where('id', $id)
                         ->where('patient_id', $user->id)
                         ->firstOrFail();

        // Business rule: cannot cancel if less than 24h away
        if (!$rdv->canBeCancelled()) {
            return response()->json([
                'message' => 'Annulation impossible : le rendez-vous est dans moins de 24h ou déjà annulé.',
            ], 422);
        }

        $rdv->update(['statut' => 'annule']);

        return response()->json(['message' => 'Rendez-vous annulé.']);
    }

    // =========================================================================
    // US 5.8 — DOCTOR'S AGENDA
    // =========================================================================

    /**
     * GET /api/agenda?date_debut=2024-06-01&date_fin=2024-06-30
     * 
     * Returns all confirmed appointments for the logged-in doctor
     * within a date range (defaults to current week if not specified).
     */
    public function agenda(Request $request)
    {
        $doctor = $request->user();

        $debut = Carbon::parse($request->query('date_debut', now()->startOfWeek()));
        $fin   = Carbon::parse($request->query('date_fin',   now()->endOfWeek()));

        $rdvs = RendezVous::where('medecin_id', $doctor->id)
                          ->whereBetween('date_heure', [$debut, $fin])
                          ->where('statut', 'confirme')
                          ->with('patient')                // Load patient info
                          ->orderBy('date_heure')
                          ->get();

        return response()->json(['rendez_vous' => $rdvs]);
    }

    // =========================================================================
    // US 6.1 — PATIENT'S APPOINTMENT HISTORY
    // =========================================================================

    /**
     * GET /api/mes-rendez-vous
     * 
     * Returns the logged-in patient's appointment history (all statuses).
     */
    public function myAppointments(Request $request)
    {
        $rdvs = RendezVous::where('patient_id', $request->user()->id)
                          ->with('medecin.doctorProfile')
                          ->orderBy('date_heure', 'desc')
                          ->paginate(15);

        return response()->json($rdvs);
    }
}
