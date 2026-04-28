<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\Disponibilite;
use App\Models\Blocage;
use App\Models\RendezVous;
use App\Models\DocumentMedical;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * SEEDER: DatabaseSeeder
 *
 * Fills the database with realistic Algerian medical data for demo/testing.
 *
 * Run with:
 *   php artisan migrate:fresh --seed
 *
 * Creates:
 *   - 1 admin
 *   - 8 doctors (different specialties, cities)
 *   - 10 patients
 *   - Availability schedules for each doctor
 *   - Some blocked slots
 *   - Past and upcoming appointments
 *   - Medical documents
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding Dawini database...');

        $this->seedAdmin();
        $doctors  = $this->seedDoctors();
        $patients = $this->seedPatients();
        $this->seedDisponibilites($doctors);
        $this->seedBlocages($doctors);
        $this->seedRendezVous($doctors, $patients);
        $this->seedDocuments($doctors, $patients);

        $this->command->info('✅ Done! Database seeded successfully.');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin',   'admin@dawini.com',           'Admin1234!'],
                ['Médecin', 'karim.benali@dawini.com',    'Doctor1234!'],
                ['Patient', 'sara.amrani@gmail.com',      'Patient1234!'],
            ]
        );
    }

    // =========================================================================
    // ADMIN
    // =========================================================================
    private function seedAdmin(): void
    {
        User::create([
            'nom'       => 'Admin',
            'prenom'    => 'Dawini',
            'email'     => 'admin@dawini.com',
            'password'  => Hash::make('Admin1234!'),
            'role'      => 'admin',
            'statut'    => 'active',
            'telephone' => '+213 21 000 000',
            'adresse'   => 'Alger, Algérie',
        ]);

        $this->command->info('  ✓ Admin created');
    }

    // =========================================================================
    // DOCTORS — 8 doctors across Algeria
    // =========================================================================
    private function seedDoctors(): array
    {
        $doctorsData = [
            [
                'user' => [
                    'nom' => 'Benali', 'prenom' => 'Karim',
                    'email' => 'karim.benali@dawini.com',
                    'telephone' => '+213 555 100 001',
                    'adresse' => '12 Rue Didouche Mourad, Alger',
                ],
                'profile' => [
                    'specialite' => 'Cardiologie',
                    'numero_rpps' => 'RPPS1000001',
                    'cabinet' => 'Clinique El Azhar',
                    'ville' => 'Alger',
                    'code_postal' => '16000',
                ],
                'jours' => [1, 3, 5], // Lun, Mer, Ven
            ],
            [
                'user' => [
                    'nom' => 'Meziani', 'prenom' => 'Fatima',
                    'email' => 'fatima.meziani@dawini.com',
                    'telephone' => '+213 555 100 002',
                    'adresse' => '45 Boulevard Zighoud Youcef, Oran',
                ],
                'profile' => [
                    'specialite' => 'Pédiatrie',
                    'numero_rpps' => 'RPPS1000002',
                    'cabinet' => 'Cabinet Médical Meziani',
                    'ville' => 'Oran',
                    'code_postal' => '31000',
                ],
                'jours' => [1, 2, 4], // Lun, Mar, Jeu
            ],
            [
                'user' => [
                    'nom' => 'Boudiaf', 'prenom' => 'Youcef',
                    'email' => 'youcef.boudiaf@dawini.com',
                    'telephone' => '+213 555 100 003',
                    'adresse' => '8 Rue Ben Badis, Constantine',
                ],
                'profile' => [
                    'specialite' => 'Dermatologie',
                    'numero_rpps' => 'RPPS1000003',
                    'cabinet' => 'Dermato Center Constantine',
                    'ville' => 'Constantine',
                    'code_postal' => '25000',
                ],
                'jours' => [2, 4, 6], // Mar, Jeu, Sam
            ],
            [
                'user' => [
                    'nom' => 'Khelil', 'prenom' => 'Amina',
                    'email' => 'amina.khelil@dawini.com',
                    'telephone' => '+213 555 100 004',
                    'adresse' => '22 Avenue de l\'ALN, Annaba',
                ],
                'profile' => [
                    'specialite' => 'Gynécologie',
                    'numero_rpps' => 'RPPS1000004',
                    'cabinet' => 'Cabinet Khelil',
                    'ville' => 'Annaba',
                    'code_postal' => '23000',
                ],
                'jours' => [1, 3, 5],
            ],
            [
                'user' => [
                    'nom' => 'Bensalem', 'prenom' => 'Omar',
                    'email' => 'omar.bensalem@dawini.com',
                    'telephone' => '+213 555 100 005',
                    'adresse' => '5 Rue des Frères Bouadou, Alger',
                ],
                'profile' => [
                    'specialite' => 'Ophtalmologie',
                    'numero_rpps' => 'RPPS1000005',
                    'cabinet' => 'Vision Plus',
                    'ville' => 'Alger',
                    'code_postal' => '16000',
                ],
                'jours' => [2, 3, 4],
            ],
            [
                'user' => [
                    'nom' => 'Hadj', 'prenom' => 'Nadia',
                    'email' => 'nadia.hadj@dawini.com',
                    'telephone' => '+213 555 100 006',
                    'adresse' => '15 Rue Hassiba Ben Bouali, Alger',
                ],
                'profile' => [
                    'specialite' => 'Neurologie',
                    'numero_rpps' => 'RPPS1000006',
                    'cabinet' => 'Neuro Clinic Alger',
                    'ville' => 'Alger',
                    'code_postal' => '16000',
                ],
                'jours' => [1, 4, 5],
            ],
            [
                'user' => [
                    'nom' => 'Cherif', 'prenom' => 'Rachid',
                    'email' => 'rachid.cherif@dawini.com',
                    'telephone' => '+213 555 100 007',
                    'adresse' => '30 Rue Larbi Ben Mhidi, Oran',
                ],
                'profile' => [
                    'specialite' => 'Orthopédie',
                    'numero_rpps' => 'RPPS1000007',
                    'cabinet' => 'Ortho Center Oran',
                    'ville' => 'Oran',
                    'code_postal' => '31000',
                ],
                'jours' => [1, 2, 3],
            ],
            [
                // This one is pending (not yet validated by admin)
                'user' => [
                    'nom' => 'Tlemcani', 'prenom' => 'Samir',
                    'email' => 'samir.tlemcani@dawini.com',
                    'telephone' => '+213 555 100 008',
                    'adresse' => '7 Rue Emir Abdelkader, Tlemcen',
                ],
                'profile' => [
                    'specialite' => 'Radiologie',
                    'numero_rpps' => 'RPPS1000008',
                    'cabinet' => 'Radio Imaging Tlemcen',
                    'ville' => 'Tlemcen',
                    'code_postal' => '13000',
                ],
                'jours' => [2, 4],
                'pending' => true, // ← not validated yet
            ],
        ];

        $doctors = [];
        foreach ($doctorsData as $data) {
            $isPending = $data['pending'] ?? false;

            $user = User::create([
                'nom'       => $data['user']['nom'],
                'prenom'    => $data['user']['prenom'],
                'email'     => $data['user']['email'],
                'password'  => Hash::make('Doctor1234!'),
                'telephone' => $data['user']['telephone'],
                'adresse'   => $data['user']['adresse'],
                'role'      => 'medecin',
                'statut'    => $isPending ? 'pending' : 'active',
            ]);

            DoctorProfile::create(array_merge($data['profile'], [
                'user_id' => $user->id,
                'valide'  => !$isPending,
            ]));

            $user->_jours = $data['jours']; // attach for disponibilites seeding
            $doctors[] = $user;
        }

        $this->command->info('  ✓ ' . count($doctors) . ' doctors created (1 pending)');
        return $doctors;
    }

    // =========================================================================
    // PATIENTS — 10 patients
    // =========================================================================
    private function seedPatients(): array
    {
        $patientsData = [
            ['nom' => 'Amrani',    'prenom' => 'Sara',    'email' => 'sara.amrani@gmail.com',    'telephone' => '+213 555 200 001', 'adresse' => 'Alger'],
            ['nom' => 'Djebbar',   'prenom' => 'Mohamed', 'email' => 'med.djebbar@gmail.com',    'telephone' => '+213 555 200 002', 'adresse' => 'Oran'],
            ['nom' => 'Ouali',     'prenom' => 'Leila',   'email' => 'leila.ouali@gmail.com',    'telephone' => '+213 555 200 003', 'adresse' => 'Constantine'],
            ['nom' => 'Brahim',    'prenom' => 'Hichem',  'email' => 'hichem.brahim@gmail.com',  'telephone' => '+213 555 200 004', 'adresse' => 'Annaba'],
            ['nom' => 'Mansouri',  'prenom' => 'Rania',   'email' => 'rania.mansouri@gmail.com', 'telephone' => '+213 555 200 005', 'adresse' => 'Alger'],
            ['nom' => 'Zerrouki',  'prenom' => 'Tarek',   'email' => 'tarek.zerrouki@gmail.com', 'telephone' => '+213 555 200 006', 'adresse' => 'Blida'],
            ['nom' => 'Hamidi',    'prenom' => 'Yasmine', 'email' => 'yasmine.hamidi@gmail.com', 'telephone' => '+213 555 200 007', 'adresse' => 'Sétif'],
            ['nom' => 'Belkacem',  'prenom' => 'Walid',   'email' => 'walid.belkacem@gmail.com', 'telephone' => '+213 555 200 008', 'adresse' => 'Tizi Ouzou'],
            ['nom' => 'Ferhat',    'prenom' => 'Imane',   'email' => 'imane.ferhat@gmail.com',   'telephone' => '+213 555 200 009', 'adresse' => 'Béjaïa'],
            ['nom' => 'Slimani',   'prenom' => 'Nassim',  'email' => 'nassim.slimani@gmail.com', 'telephone' => '+213 555 200 010', 'adresse' => 'Alger'],
        ];

        $patients = [];
        foreach ($patientsData as $data) {
            $patients[] = User::create([
                'nom'       => $data['nom'],
                'prenom'    => $data['prenom'],
                'email'     => $data['email'],
                'password'  => Hash::make('Patient1234!'),
                'telephone' => $data['telephone'],
                'adresse'   => $data['adresse'],
                'role'      => 'patient',
                'statut'    => 'active',
            ]);
        }

        $this->command->info('  ✓ ' . count($patients) . ' patients created');
        return $patients;
    }

    // =========================================================================
    // DISPONIBILITES — Weekly schedules for each doctor
    // =========================================================================
    private function seedDisponibilites(array $doctors): void
    {
        // Morning and afternoon slots with different durations
        $schedules = [
            ['heure_debut' => '08:30', 'heure_fin' => '12:00', 'duree_creneau' => 30],
            ['heure_debut' => '14:00', 'heure_fin' => '17:30', 'duree_creneau' => 30],
        ];

        foreach ($doctors as $doctor) {
            // Skip the pending doctor
            if ($doctor->statut === 'pending') continue;

            foreach ($doctor->_jours as $jour) {
                foreach ($schedules as $schedule) {
                    Disponibilite::create([
                        'medecin_id'    => $doctor->id,
                        'jour_semaine'  => $jour,
                        'heure_debut'   => $schedule['heure_debut'],
                        'heure_fin'     => $schedule['heure_fin'],
                        'duree_creneau' => $schedule['duree_creneau'],
                    ]);
                }
            }
        }

        $this->command->info('  ✓ Availability schedules created');
    }

    // =========================================================================
    // BLOCAGES — Some manually blocked slots
    // =========================================================================
    private function seedBlocages(array $doctors): void
    {
        $activeDoctors = array_filter($doctors, fn($d) => $d->statut === 'active');
        $doctor1 = array_values($activeDoctors)[0]; // Dr Benali
        $doctor2 = array_values($activeDoctors)[1]; // Dr Meziani

        // Doctor 1 — on vacation next week
        Blocage::create([
            'medecin_id' => $doctor1->id,
            'debut'      => Carbon::now()->addDays(8)->setTime(8, 0),
            'fin'        => Carbon::now()->addDays(12)->setTime(18, 0),
            'raison'     => 'Congé annuel',
        ]);

        // Doctor 2 — formation one afternoon
        Blocage::create([
            'medecin_id' => $doctor2->id,
            'debut'      => Carbon::now()->addDays(3)->setTime(14, 0),
            'fin'        => Carbon::now()->addDays(3)->setTime(17, 30),
            'raison'     => 'Formation continue',
        ]);

        $this->command->info('  ✓ Blocked slots created');
    }

    // =========================================================================
    // RENDEZ-VOUS — Past and upcoming appointments
    // =========================================================================
    private function seedRendezVous(array $doctors, array $patients): void
    {
        $activeDoctors = array_values(array_filter($doctors, fn($d) => $d->statut === 'active'));
        $rdvCount = 0;

        // --- PAST appointments (already done) ---
        $pastAppointments = [
            // [doctor_index, patient_index, days_ago, hour, motif, statut]
            [0, 0, 30, '09:00', 'Consultation cardiaque de routine',    'termine'],
            [0, 1, 25, '10:00', 'Douleurs thoraciques',                 'termine'],
            [1, 2, 20, '08:30', 'Fièvre persistante (enfant)',          'termine'],
            [1, 3, 18, '09:30', 'Vaccination de rappel',                'termine'],
            [2, 4, 15, '14:00', 'Éruption cutanée',                     'termine'],
            [2, 5, 12, '15:00', 'Acné sévère',                          'termine'],
            [3, 6, 10, '09:00', 'Suivi grossesse',                      'termine'],
            [3, 7,  8, '10:30', 'Consultation gynécologique annuelle',   'termine'],
            [4, 8,  7, '14:30', 'Baisse de vision',                     'termine'],
            [4, 9,  5, '15:30', 'Contrôle lunettes',                    'termine'],
            [5, 0,  4, '09:00', 'Maux de tête récurrents',              'termine'],
            [5, 1,  3, '10:00', 'Vertiges et étourdissements',          'termine'],
            [0, 2,  2, '08:30', 'Palpitations cardiaques',              'annule'],  // cancelled
            [1, 3,  1, '14:00', 'Rhume persistant enfant',              'termine'],
        ];

        foreach ($pastAppointments as [$di, $pi, $daysAgo, $hour, $motif, $statut]) {
            $doctor  = $activeDoctors[$di % count($activeDoctors)];
            $patient = $patients[$pi % count($patients)];
            [$h, $m] = explode(':', $hour);

            RendezVous::create([
                'patient_id' => $patient->id,
                'medecin_id' => $doctor->id,
                'date_heure' => Carbon::now()->subDays($daysAgo)->setTime((int)$h, (int)$m, 0),
                'duree'      => 30,
                'statut'     => $statut,
                'motif'      => $motif,
            ]);
            $rdvCount++;
        }

        // --- UPCOMING appointments (confirmed) ---
        $upcomingAppointments = [
            // [doctor_index, patient_index, days_ahead, hour, motif]
            [0, 4,  2, '09:00', 'Suivi traitement hypertension'],
            [0, 5,  2, '09:30', 'ECG de contrôle'],
            [1, 6,  3, '08:30', 'Consultation pédiatrique'],
            [1, 7,  3, '09:00', 'Bilan de santé enfant'],
            [2, 8,  4, '14:00', 'Consultation dermatologique'],
            [3, 9,  5, '10:00', 'Suivi gynécologique'],
            [4, 0,  6, '14:30', 'Examen de la vue'],
            [5, 1,  7, '09:00', 'IRM résultats discussion'],
            [6, 2, 10, '08:30', 'Consultation suite entorse'],
            [6, 3, 14, '09:30', 'Radio genou'],
        ];

        foreach ($upcomingAppointments as [$di, $pi, $daysAhead, $hour, $motif]) {
            $doctor  = $activeDoctors[$di % count($activeDoctors)];
            $patient = $patients[$pi % count($patients)];
            [$h, $m] = explode(':', $hour);

            RendezVous::create([
                'patient_id' => $patient->id,
                'medecin_id' => $doctor->id,
                'date_heure' => Carbon::now()->addDays($daysAhead)->setTime((int)$h, (int)$m, 0),
                'duree'      => 30,
                'statut'     => 'confirme',
                'motif'      => $motif,
            ]);
            $rdvCount++;
        }

        $this->command->info("  ✓ $rdvCount appointments created (past + upcoming)");
    }

    // =========================================================================
    // DOCUMENTS MEDICAUX — Medical reports for past appointments
    // =========================================================================
    private function seedDocuments(array $doctors, array $patients): void
    {
        // Get some completed appointments to attach documents to
        $completedRdvs = RendezVous::where('statut', 'termine')
                                    ->with(['patient', 'medecin'])
                                    ->limit(8)
                                    ->get();

        $docTypes = [
            ['titre' => 'Compte-rendu de consultation',   'type' => 'compte_rendu'],
            ['titre' => 'Ordonnance médicale',             'type' => 'ordonnance'],
            ['titre' => 'Résultats analyse sanguine',      'type' => 'analyse'],
            ['titre' => 'Rapport ECG',                     'type' => 'compte_rendu'],
            ['titre' => 'Ordonnance renouvellement',       'type' => 'ordonnance'],
            ['titre' => 'Bilan biologique complet',        'type' => 'analyse'],
            ['titre' => 'Compte-rendu radiologie',         'type' => 'compte_rendu'],
            ['titre' => 'Certificat médical',              'type' => 'autre'],
        ];

        foreach ($completedRdvs as $index => $rdv) {
            $docInfo = $docTypes[$index % count($docTypes)];

            DocumentMedical::create([
                'patient_id'     => $rdv->patient_id,
                'medecin_id'     => $rdv->medecin_id,
                'rendez_vous_id' => $rdv->id,
                'titre'          => $docInfo['titre'],
                'type'           => $docInfo['type'],
                // Fake path — in real app files would be uploaded to storage
                'fichier_path'   => 'documents/patient_' . $rdv->patient_id . '/doc_' . ($index + 1) . '.pdf',
            ]);
        }

        $this->command->info('  ✓ ' . $completedRdvs->count() . ' medical documents created');
    }
}