// =============================================================================
// DAWINI — API SERVICE LAYER
// =============================================================================
const API_BASE = 'http://127.0.0.1:8000/api'; // Change to your Laravel URL

const Api = {
  token: () => localStorage.getItem('dawini_token'),

  headers(extra = {}) {
    const h = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',   // ← FIXES the JSON.parse error: forces Laravel to return JSON instead of HTML
      ...extra
    };
    if (this.token()) h['Authorization'] = 'Bearer ' + this.token();
    return h;
  },

  async request(method, path, body = null) {
    const opts = { method, headers: this.headers() };
    if (body) opts.body = JSON.stringify(body);

    // Step 1: Network request
    let res;
    try {
      res = await fetch(API_BASE + path, opts);
    } catch {
      // Server is down or CORS preflight failed completely
      throw { message: 'Impossible de contacter le serveur. Vérifiez que Laravel est démarré.' };
    }

    // Step 2: Parse JSON response
    let data;
    try {
      data = await res.json();
    } catch {
      // Server returned non-JSON (shouldn't happen with Accept: application/json header)
      throw { message: `Erreur serveur (${res.status}). Réponse invalide reçue.` };
    }

    // Step 3: HTTP error check (4xx, 5xx)
    if (!res.ok) {
      throw { status: res.status, message: data.message || 'Erreur serveur', errors: data.errors };
    }

    // Step 4: Success — return parsed data
    return data;
  },

  get:    (p)    => Api.request('GET',    p),
  post:   (p, b) => Api.request('POST',   p, b),
  put:    (p, b) => Api.request('PUT',    p, b),
  patch:  (p, b) => Api.request('PATCH',  p, b),
  delete: (p)    => Api.request('DELETE', p),

  // Auth
  register:      (d) => Api.post('/register', d),
  login:         (d) => Api.post('/login', d),
  logout:        ()  => Api.post('/logout'),
  forgotPass:    (d) => Api.post('/forgot-password', d),
  verifyCode:    (d) => Api.post('/verify-reset-code', d),
  resetPass:     (d) => Api.post('/reset-password', d),

  // Profile
  getProfile:          ()  => Api.get('/profile'),
  updateProfile:       (d) => Api.put('/profile', d),
  updateDoctorProfile: (d) => Api.put('/profile/medecin', d),

  // Appointments - Patient
  searchDoctors:   (p) => Api.get('/medecins/recherche?' + new URLSearchParams(p)),
  getCreneaux:     (id, date) => Api.get(`/medecins/${id}/creneaux?date=${date}`),
  bookRdv:         (d) => Api.post('/rendez-vous', d),
  cancelRdv:       (id) => Api.patch(`/rendez-vous/${id}/annuler`),
  myAppointments:  ()  => Api.get('/mes-rendez-vous'),
  myDocuments:     ()  => Api.get('/mes-documents'),

  // ⚠️ Cannot use Api.get() here — server returns a binary file stream, NOT JSON.
  // Using Api.get() crashes at res.json() step with "Réponse invalide reçue".
  downloadDoc: async (id) => {
    const res = await fetch(API_BASE + `/mes-documents/${id}/download`, {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + Api.token(),
        // ❌ No Accept: application/json — we want the raw file stream
      }
    });
    if (!res.ok) {
      let msg = `Erreur serveur (${res.status}).`;
      try { const err = await res.json(); msg = err.message || msg; } catch(_) {}
      throw { message: msg };
    }
    return res; // Return raw Response so caller can .blob() it
  },

  // Doctor
  myDispos:        ()  => Api.get('/disponibilites'),
  addDispo:        (d) => Api.post('/disponibilites', d),
  deleteDispo:     (id) => Api.delete(`/disponibilites/${id}`),
  addBlocage:      (d) => Api.post('/blocages', d),
  getAgenda:       (p) => Api.get('/agenda?' + new URLSearchParams(p)),
  uploadDocument:  (fd) => {
    return fetch(API_BASE + '/documents', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',          // ← required for JSON error responses
        'Authorization': 'Bearer ' + Api.token()
        // NOTE: Do NOT set Content-Type here — browser sets it automatically for FormData (with boundary)
      },
      body: fd
    }).then(async r => {
      const data = await r.json();
      if (!r.ok) throw { status: r.status, message: `Erreur serveur (${r.status}). ${data.message || ''}`, errors: data.errors };
      return data;
    });
  },
  patientDocuments:(id) => Api.get(`/patients/${id}/documents`),

  // Admin
  adminStats:      ()  => Api.get('/admin/statistiques'),
  adminUsers:      (p) => Api.get('/admin/users?' + new URLSearchParams(p)),
  adminPatients:   (p) => Api.get('/admin/patients?' + new URLSearchParams(p)),
  adminMedecins:   (p) => Api.get('/admin/medecins?' + new URLSearchParams(p)),
  validateMedecin: (id) => Api.patch(`/admin/medecins/${id}/valider`),
  deactivateMedecin:(id) => Api.patch(`/admin/medecins/${id}/desactiver`),
  deactivatePatient:(id) => Api.patch(`/admin/patients/${id}/desactiver`),
  assignRole:      (id, role) => Api.patch(`/admin/users/${id}/role`, { role }),
  createPatient:   (d) => Api.post('/admin/patients', d),
  updatePatient:   (id, d) => Api.put(`/admin/patients/${id}`, d),
  updateMedecin:   (id, d) => Api.put(`/admin/medecins/${id}`, d),
};

// =============================================================================
// STATE
// =============================================================================
const State = {
  user: null,
  currentPage: 'home',
  currentDashTab: null,

  setUser(u) { this.user = u; localStorage.setItem('dawini_user', JSON.stringify(u)); },
  getUser() {
    if (!this.user) {
      const s = localStorage.getItem('dawini_user');
      if (s) this.user = JSON.parse(s);
    }
    return this.user;
  },
  clearUser() { this.user = null; localStorage.removeItem('dawini_user'); localStorage.removeItem('dawini_token'); }
};

// =============================================================================
// UI HELPERS
// =============================================================================
function showToast(msg, type = 'default') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `show toast-${type}`;
  setTimeout(() => { t.className = t.className.replace(' show', ''); }, 3200);
}

function showPage(id) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  const p = document.getElementById(id);
  if (p) { p.classList.add('active'); State.currentPage = id; }
  updateNav();
  window.scrollTo(0, 0);
}

function showModal(id) { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

function setLoading(btn, loading) {
  if (loading) {
    btn._txt = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span>';
    btn.disabled = true;
  } else {
    btn.innerHTML = btn._txt || btn.innerHTML;
    btn.disabled = false;
  }
}

function getErrMsg(e) {
  if (e.errors) return Object.values(e.errors).flat().join(' ');
  return e.message || 'Une erreur est survenue.';
}

function fmt(dateStr) {
  if (!dateStr) return '–';
  return new Date(dateStr).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
}
function fmtTime(dateStr) {
  if (!dateStr) return '–';
  return new Date(dateStr).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}
function fmtDateTime(dateStr) {
  if (!dateStr) return '–';
  return new Date(dateStr).toLocaleString('fr-FR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
}

function initials(nom, prenom) {
  return ((prenom || '?')[0] + (nom || '?')[0]).toUpperCase();
}

function statusBadge(s) {
  const map = {
    active: 'badge-success',   Active: 'badge-success',
    pending: 'badge-warning',  confirme: 'badge-success',
    annule: 'badge-danger',    inactive: 'badge-danger',
  };
  const labels = { active:'Actif', pending:'En attente', inactive:'Inactif', confirme:'Confirmé', annule:'Annulé' };
  return `<span class="badge ${map[s]||'badge-muted'}">${labels[s]||s}</span>`;
}

// =============================================================================
// NAV
// =============================================================================
function updateNav() {
  const user = State.getUser();
  const token = localStorage.getItem('dawini_token');
  const loggedIn = !!(token && user);

  document.getElementById('nav-public').style.display   = loggedIn ? 'none'  : 'flex';
  document.getElementById('nav-private').style.display  = loggedIn ? 'flex'  : 'none';
  document.getElementById('nav-username').textContent   = loggedIn ? (user.prenom || user.nom || 'Compte') : '';

  // Role-specific nav items
  document.querySelectorAll('[data-role]').forEach(el => {
    el.style.display = (user && el.dataset.role.split(',').includes(user.role)) ? '' : 'none';
  });
}

// =============================================================================
// AUTH
// =============================================================================
async function handleLogin(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  const err = document.getElementById('login-err');
  err.style.display = 'none';
  setLoading(btn, true);
  try {
    const res = await Api.login({
      email:    document.getElementById('login-email').value,
      password: document.getElementById('login-pass').value,
    });
    localStorage.setItem('dawini_token', res.access_token);
    State.setUser(res.user);
    showToast('Bienvenue, ' + (res.user.prenom || res.user.nom) + ' !', 'success');
    redirectByRole(res.user.role);
  } catch(ex) {
    err.textContent = getErrMsg(ex);
    err.style.display = 'flex';
  } finally { setLoading(btn, false); }
}

function redirectByRole(role) {
  if (role === 'admin')   { showPage('page-admin');   loadAdminDash(); }
  else if (role === 'medecin') { showPage('page-medecin'); loadMedecinDash(); }
  else { showPage('page-patient'); loadPatientDash(); }
}

async function handleRegister(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  const err = document.getElementById('register-err');
  err.style.display = 'none';
  setLoading(btn, true);

  const role = document.getElementById('reg-role').value;
  const body = {
    nom:      document.getElementById('reg-nom').value,
    prenom:   document.getElementById('reg-prenom').value,
    email:    document.getElementById('reg-email').value,
    password: document.getElementById('reg-pass').value,
    password_confirmation: document.getElementById('reg-pass-confirm').value,
    telephone: document.getElementById('reg-tel').value || undefined,
    role,
  };
  if (role === 'medecin') {
    body.specialite   = document.getElementById('reg-spec').value;
    body.numero_rpps  = document.getElementById('reg-rpps').value;
    body.cabinet      = document.getElementById('reg-cabinet').value || undefined;
    body.ville        = document.getElementById('reg-ville').value || undefined;
  }

  try {
    await Api.register(body);
    showToast('Inscription réussie !', 'success');
    showPage('page-login');
    if (role === 'medecin') showToast('Compte en attente de validation admin.', 'default');
  } catch(ex) {
    err.textContent = getErrMsg(ex);
    err.style.display = 'flex';
  } finally { setLoading(btn, false); }
}

async function handleLogout() {
  try { await Api.logout(); } catch {}
  State.clearUser();
  showToast('Déconnexion réussie.');
  showPage('page-home');
}

// handleForgotPass moved to bottom of file (3-step code flow)

// =============================================================================
// PATIENT DASHBOARD
// =============================================================================
async function loadPatientDash(tab = 'search') {
  State.currentDashTab = tab;
  showDashTab(tab);
  if (tab === 'mes-rdv') await loadMyRdv();
  if (tab === 'mes-docs') await loadMyDocs();
}

function showDashTab(tab) {
  document.querySelectorAll('#page-patient .dash-tab').forEach(el => el.style.display = 'none');
  const el = document.getElementById('ptab-' + tab);
  if (el) el.style.display = '';
  document.querySelectorAll('#page-patient .sidebar-link').forEach(l => {
    l.classList.toggle('active', l.dataset.tab === tab);
  });
}

// --- SEARCH DOCTORS ---
async function searchDoctors() {
  const spec  = document.getElementById('s-specialite').value;
  const ville = document.getElementById('s-ville').value;
  const grid  = document.getElementById('doctors-grid');
  grid.innerHTML = '<p class="text-muted">Recherche en cours…</p>';
  try {
    const params = {};
    if (spec)  params.specialite = spec;
    if (ville) params.ville = ville;
    const res = await Api.searchDoctors(params);
    const doctors = res.data || res;
    if (!doctors.length) {
      grid.innerHTML = '<div class="empty-state"><div class="empty-icon">🔍</div><p>Aucun médecin trouvé pour ces critères.</p></div>';
      return;
    }
    grid.innerHTML = doctors.map(d => `
      <div class="doctor-card">
        <div class="flex items-center gap-3 mb-2">
          <div class="doctor-avatar">${initials(d.nom, d.prenom)}</div>
          <div>
            <div class="doctor-name">Dr ${d.prenom} ${d.nom}</div>
            <div class="doctor-spec">${d.doctor_profile?.specialite || '–'}</div>
          </div>
        </div>
        <div class="doctor-meta">📍 ${d.doctor_profile?.ville || 'Non renseigné'}</div>
        <div class="doctor-meta">🏥 ${d.doctor_profile?.cabinet || 'Cabinet non renseigné'}</div>
        <button class="btn btn-primary btn-sm mt-3" onclick="openBooking(${d.id}, '${d.prenom} ${d.nom}', '${d.doctor_profile?.specialite||''}')">
          Prendre RDV
        </button>
      </div>
    `).join('');
  } catch(ex) { grid.innerHTML = `<p class="text-muted">${getErrMsg(ex)}</p>`; }
}

// --- BOOKING MODAL ---
async function openBooking(medecinId, name, spec) {
  document.getElementById('book-doctor-name').textContent = `Dr ${name} — ${spec}`;
  document.getElementById('book-medecin-id').value = medecinId;
  document.getElementById('book-slots').innerHTML = '';
  document.getElementById('book-selected-slot').value = '';
  document.getElementById('book-date').value = '';
  showModal('modal-booking');
}

async function loadSlots() {
  const date = document.getElementById('book-date').value;
  const mId  = document.getElementById('book-medecin-id').value;
  if (!date || !mId) return;
  const wrap = document.getElementById('book-slots');
  wrap.innerHTML = '<p class="text-muted">Chargement…</p>';
  try {
    const res = await Api.getCreneaux(mId, date);
    const slots = res.creneaux || [];
    if (!slots.length) { wrap.innerHTML = '<p class="text-muted">Aucun créneau disponible ce jour.</p>'; return; }
    wrap.innerHTML = `<div class="slots-grid">${slots.map(s => `
      <button class="slot-btn" onclick="selectSlot(this,'${date} ${s}')">${s}</button>
    `).join('')}</div>`;
  } catch(ex) { wrap.innerHTML = `<p class="text-muted">${getErrMsg(ex)}</p>`; }
}

function selectSlot(btn, dt) {
  document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  document.getElementById('book-selected-slot').value = dt;
}

async function handleBooking(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  const dt  = document.getElementById('book-selected-slot').value;
  if (!dt) { showToast('Veuillez sélectionner un créneau.', 'error'); return; }
  setLoading(btn, true);
  try {
    await Api.bookRdv({
      medecin_id: +document.getElementById('book-medecin-id').value,
      date_heure: dt,
      motif: document.getElementById('book-motif').value || undefined,
    });
    closeModal('modal-booking');
    showToast('Rendez-vous confirmé !', 'success');
    showDashTab('mes-rdv');
    await loadMyRdv();
  } catch(ex) { showToast(getErrMsg(ex), 'error'); }
  finally { setLoading(btn, false); }
}

// --- MY APPOINTMENTS ---
async function loadMyRdv() {
  const wrap = document.getElementById('my-rdv-list');
  wrap.innerHTML = '<p class="text-muted">Chargement…</p>';
  try {
    const res = await Api.myAppointments();
    const rdvs = res.data || res;
    if (!rdvs.length) { wrap.innerHTML = '<div class="empty-state"><div class="empty-icon">📅</div><p>Aucun rendez-vous pour l\'instant.</p></div>'; return; }
    wrap.innerHTML = rdvs.map(r => `
      <div class="rdv-item">
        <div>
          <div class="rdv-time">${fmtTime(r.date_heure)}</div>
          <div class="text-muted" style="font-size:0.78rem">${fmt(r.date_heure)}</div>
        </div>
        <div class="rdv-info">
          <div class="rdv-patient">Dr ${r.medecin?.prenom||''} ${r.medecin?.nom||''}</div>
          <div class="rdv-motif">${r.motif || 'Aucun motif renseigné'}</div>
          <div class="rdv-motif">${r.medecin?.doctor_profile?.specialite||''}</div>
        </div>
        <div>${statusBadge(r.statut)}</div>
        ${r.statut === 'confirme' ? `<button class="btn btn-outline btn-sm" onclick="cancelRdv(${r.id}, this)">Annuler</button>` : ''}
      </div>
    `).join('');
  } catch(ex) { wrap.innerHTML = `<p class="text-muted">${getErrMsg(ex)}</p>`; }
}

async function cancelRdv(id, btn) {
  if (!confirm('Confirmer l\'annulation de ce rendez-vous ?')) return;
  setLoading(btn, true);
  try {
    await Api.cancelRdv(id);
    showToast('Rendez-vous annulé.', 'success');
    await loadMyRdv();
  } catch(ex) { showToast(getErrMsg(ex), 'error'); setLoading(btn, false); }
}

// --- MY DOCS ---
async function loadMyDocs() {
  const wrap = document.getElementById('my-docs-list');
  wrap.innerHTML = '<p class="text-muted">Chargement…</p>';
  try {
    const docs = await Api.myDocuments();
    if (!docs.length) { wrap.innerHTML = '<div class="empty-state"><div class="empty-icon">📄</div><p>Aucun document médical.</p></div>'; return; }
    wrap.innerHTML = `<div class="table-wrap"><table>
      <thead><tr><th>Titre</th><th>Type</th><th>Médecin</th><th>Date</th><th></th></tr></thead>
      <tbody>${docs.map(d => `<tr>
        <td class="td-name">${d.titre}</td>
        <td>${d.type}</td>
        <td>Dr ${d.medecin?.prenom||''} ${d.medecin?.nom||''}</td>
        <td>${fmt(d.created_at)}</td>
        <td><button class="btn btn-outline btn-sm" data-doc-id="${d.id}" onclick="downloadDoc(${d.id}, '${(d.titre||'document').replace(/'/g,"\\'")}')">⬇ Télécharger</button></td>
      </tr>`).join('')}</tbody>
    </table></div>`;
  } catch(ex) { wrap.innerHTML = `<p class="text-muted">${getErrMsg(ex)}</p>`; }
}

// ✅ FIXED: Downloads file as blob instead of expecting JSON
// The old version used Api.get() which calls res.json() — crashing on binary streams.
async function downloadDoc(id, filename) {
  const btn = document.querySelector(`button[data-doc-id="${id}"]`);
  if (btn) { btn.disabled = true; btn.textContent = '⏳…'; }
  try {
    const res = await Api.downloadDoc(id); // Returns raw Response, not JSON

    const blob = await res.blob(); // ✅ Read as binary

    // Try to extract filename from server header
    const disposition = res.headers.get('Content-Disposition');
    if (disposition && disposition.includes('filename=')) {
      filename = disposition.split('filename=')[1].replace(/['"]/g, '').trim();
    }

    // Trigger browser download
    const url = window.URL.createObjectURL(blob);
    const a   = document.createElement('a');
    a.href     = url;
    a.download = filename || `document_${id}`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(url);

  } catch(ex) {
    showToast(getErrMsg(ex), 'error');
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = '⬇ Télécharger'; }
  }
}


async function loadPatientProfile() {
  try {
    const res = await Api.getProfile();
    const u = res.user;
    document.getElementById('p-nom').value       = u.nom || '';
    document.getElementById('p-prenom').value    = u.prenom || '';
    document.getElementById('p-email').value     = u.email || '';
    document.getElementById('p-tel').value       = u.telephone || '';
    document.getElementById('p-adresse').value   = u.adresse || '';
  } catch {}
}

async function handleUpdateProfile(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  setLoading(btn, true);
  try {
    await Api.updateProfile({
      nom:       document.getElementById('p-nom').value,
      prenom:    document.getElementById('p-prenom').value,
      telephone: document.getElementById('p-tel').value,
      adresse:   document.getElementById('p-adresse').value,
    });
    showToast('Profil mis à jour !', 'success');
  } catch(ex) { showToast(getErrMsg(ex), 'error'); }
  finally { setLoading(btn, false); }
}

// =============================================================================
// MEDECIN DASHBOARD
// =============================================================================
async function loadMedecinDash(tab = 'agenda') {
  State.currentDashTab = tab;
  showMedecinTab(tab);
  if (tab === 'agenda')     await loadAgenda();
  if (tab === 'dispos')     await loadDispos();
  if (tab === 'docs-med')   initDocsMed();
  if (tab === 'profil-med') await loadMedecinProfile();
}

function showMedecinTab(tab) {
  document.querySelectorAll('#page-medecin .dash-tab').forEach(el => el.style.display = 'none');
  const el = document.getElementById('mtab-' + tab);
  if (el) el.style.display = '';
  document.querySelectorAll('#page-medecin .sidebar-link').forEach(l => {
    l.classList.toggle('active', l.dataset.tab === tab);
  });
}

async function loadAgenda() {
  const today = new Date();
  const debut = today.toISOString().split('T')[0];
  const fin   = new Date(today.getTime() + 7 * 86400000).toISOString().split('T')[0];
  const wrap  = document.getElementById('agenda-list');
  wrap.innerHTML = '<p class="text-muted">Chargement…</p>';
  try {
    const res  = await Api.getAgenda({ date_debut: debut, date_fin: fin });
    const rdvs = res.rendez_vous || [];
    if (!rdvs.length) { wrap.innerHTML = '<div class="empty-state"><div class="empty-icon">📅</div><p>Aucun rendez-vous cette semaine.</p></div>'; return; }
    wrap.innerHTML = rdvs.map(r => `
      <div class="rdv-item">
        <div>
          <div class="rdv-time">${fmtTime(r.date_heure)}</div>
          <div class="text-muted" style="font-size:0.78rem">${fmt(r.date_heure)}</div>
        </div>
        <div class="rdv-info">
          <div class="rdv-patient">${r.patient?.prenom||''} ${r.patient?.nom||''}</div>
          <div class="rdv-motif">${r.motif || 'Aucun motif'}</div>
          ${r.patient?.id ? `<div class="text-muted" style="font-size:.78rem">Patient ID: ${r.patient.id} · RDV ID: ${r.id}</div>` : ''}
        </div>
        <div>${statusBadge(r.statut)}</div>
        ${r.patient?.id ? `<button class="btn btn-outline btn-sm" onclick="quickSendDoc(${r.patient.id},${r.id})">📄 Document</button>` : ''}
      </div>
    `).join('');
  } catch(ex) { wrap.innerHTML = `<p class="text-muted">${getErrMsg(ex)}</p>`; }
}

// =============================================================================
// DOCTOR DOCUMENTS
// =============================================================================

// Called from agenda button — navigates to docs tab and pre-fills both IDs
function quickSendDoc(patientId, rdvId) {
  loadMedecinDash('docs-med');
  setTimeout(() => {
    const elPat = document.getElementById('doc-patient-id');
    const elRdv = document.getElementById('doc-rdv-id');
    const elView = document.getElementById('doc-view-patient-id');
    if (elPat)  elPat.value  = patientId;
    if (elRdv)  elRdv.value  = rdvId || '';
    if (elView) elView.value = patientId;
  }, 60);
}

function initDocsMed() {
  const err = document.getElementById('doc-err');
  if (err) err.style.display = 'none';
  const list = document.getElementById('patient-docs-list');
  if (list) list.innerHTML = '<div class="empty-state"><div class="empty-icon">🔍</div><p>Entrez un ID patient pour voir ses documents.</p></div>';
}

async function handleUploadDocument(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  const err = document.getElementById('doc-err');
  err.style.display = 'none';

  const file = document.getElementById('doc-file').files[0];
  if (!file) { err.textContent = 'Veuillez sélectionner un fichier.'; err.style.display = 'flex'; return; }

  // Validate file type client-side to match Laravel: pdf, jpg, jpeg, png
  const allowed = ['application/pdf', 'image/jpeg', 'image/png'];
  if (!allowed.includes(file.type)) {
    err.textContent = 'Format non supporté. Utilisez PDF, JPG ou PNG.';
    err.style.display = 'flex';
    return;
  }

  setLoading(btn, true);
  try {
    const fd = new FormData();
    fd.append('patient_id', document.getElementById('doc-patient-id').value);
    // rendez_vous_id is nullable on the server — only send if filled
    const rdvId = document.getElementById('doc-rdv-id').value;
    if (rdvId) fd.append('rendez_vous_id', rdvId);
    fd.append('titre',   document.getElementById('doc-titre').value);
    fd.append('type',    document.getElementById('doc-type').value);
    fd.append('fichier', file);
    await Api.uploadDocument(fd);
    showToast('Document envoyé avec succès !', 'success');
    document.getElementById('form-upload-doc').reset();
  } catch(ex) {
    err.textContent = getErrMsg(ex);
    err.style.display = 'flex';
  } finally { setLoading(btn, false); }
}

async function loadPatientDocs() {
  const patientId = document.getElementById('doc-view-patient-id').value;
  if (!patientId) { showToast('Entrez un ID patient.', 'error'); return; }
  const wrap = document.getElementById('patient-docs-list');
  wrap.innerHTML = '<p class="text-muted">Chargement…</p>';
  try {
    const docs = await Api.patientDocuments(patientId);
    const list = docs.data || docs;
    if (!list.length) {
      wrap.innerHTML = '<div class="empty-state"><div class="empty-icon">📄</div><p>Aucun document pour ce patient.</p></div>';
      return;
    }
    const typeLabels = { ordonnance:'Ordonnance', analyse:'Analyse', compte_rendu:'Compte-rendu', autre:'Autre' };
    wrap.innerHTML = `<div class="table-wrap"><table>
      <thead><tr><th>Titre</th><th>Type</th><th>Médecin</th><th>Date</th><th></th></tr></thead>
      <tbody>${list.map(d => `<tr>
        <td class="td-name">${d.titre}</td>
        <td><span class="badge badge-info">${typeLabels[d.type] || d.type}</span></td>
        <td>Dr ${d.medecin?.prenom||''} ${d.medecin?.nom||''}</td>
        <td>${fmt(d.created_at)}</td>
        <td>${d.url ? `<a class="btn btn-outline btn-sm" href="${d.url}" target="_blank">⬇ Ouvrir</a>` : '–'}</td>
      </tr>`).join('')}</tbody>
    </table></div>`;
  } catch(ex) { wrap.innerHTML = `<p class="text-muted">${getErrMsg(ex)}</p>`; }
}

async function loadDispos() {
  const wrap = document.getElementById('dispos-list');
  wrap.innerHTML = '<p class="text-muted">Chargement…</p>';
  const jours = ['–', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
  try {
    const res = await Api.myDispos();
    if (!res.length) { wrap.innerHTML = '<div class="empty-state"><div class="empty-icon">🗓</div><p>Aucune disponibilité configurée.</p></div>'; return; }
    wrap.innerHTML = `<div class="table-wrap"><table>
      <thead><tr><th>Jour</th><th>Début</th><th>Fin</th><th>Durée créneau</th><th></th></tr></thead>
      <tbody>${res.map(d => `<tr>
        <td class="td-name">${jours[d.jour_semaine]||d.jour_semaine}</td>
        <td>${d.heure_debut}</td>
        <td>${d.heure_fin}</td>
        <td>${d.duree_creneau} min</td>
        <td><button class="btn btn-danger btn-sm" onclick="deleteDispo(${d.id}, this)">Supprimer</button></td>
      </tr>`).join('')}</tbody>
    </table></div>`;
  } catch(ex) { wrap.innerHTML = `<p class="text-muted">${getErrMsg(ex)}</p>`; }
}

async function deleteDispo(id, btn) {
  if (!confirm('Supprimer cette disponibilité ?')) return;
  setLoading(btn, true);
  try {
    await Api.deleteDispo(id);
    showToast('Disponibilité supprimée.', 'success');
    await loadDispos();
  } catch(ex) { showToast(getErrMsg(ex), 'error'); setLoading(btn, false); }
}

async function handleAddDispo(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  setLoading(btn, true);
  try {
    await Api.addDispo({
      jour_semaine:  +document.getElementById('dispo-jour').value,
      heure_debut:   document.getElementById('dispo-debut').value,
      heure_fin:     document.getElementById('dispo-fin').value,
      duree_creneau: +document.getElementById('dispo-duree').value,
    });
    e.target.reset();
    showToast('Disponibilité ajoutée !', 'success');
    await loadDispos();
  } catch(ex) { showToast(getErrMsg(ex), 'error'); }
  finally { setLoading(btn, false); }
}

async function handleAddBlocage(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  setLoading(btn, true);
  try {
    await Api.addBlocage({
      debut:  document.getElementById('bloc-debut').value,
      fin:    document.getElementById('bloc-fin').value,
      raison: document.getElementById('bloc-raison').value || undefined,
    });
    closeModal('modal-blocage');
    e.target.reset();
    showToast('Créneau bloqué.', 'success');
  } catch(ex) { showToast(getErrMsg(ex), 'error'); }
  finally { setLoading(btn, false); }
}

async function loadMedecinProfile() {
  try {
    const res = await Api.getProfile();
    const u = res.user;
    document.getElementById('mp-nom').value        = u.nom || '';
    document.getElementById('mp-prenom').value     = u.prenom || '';
    document.getElementById('mp-email').value      = u.email || '';
    document.getElementById('mp-tel').value        = u.telephone || '';
    document.getElementById('mp-spec').value       = u.doctor_profile?.specialite || '';
    document.getElementById('mp-cabinet').value    = u.doctor_profile?.cabinet || '';
    document.getElementById('mp-ville').value      = u.doctor_profile?.ville || '';
    document.getElementById('mp-rpps').value       = u.doctor_profile?.numero_rpps || '';
  } catch {}
}

async function handleUpdateMedecinProfile(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  setLoading(btn, true);
  try {
    await Api.updateProfile({ nom: document.getElementById('mp-nom').value, prenom: document.getElementById('mp-prenom').value, telephone: document.getElementById('mp-tel').value });
    await Api.updateDoctorProfile({ specialite: document.getElementById('mp-spec').value, cabinet: document.getElementById('mp-cabinet').value, ville: document.getElementById('mp-ville').value });
    showToast('Profil mis à jour !', 'success');
  } catch(ex) { showToast(getErrMsg(ex), 'error'); }
  finally { setLoading(btn, false); }
}

// =============================================================================
// ADMIN DASHBOARD
// =============================================================================
async function loadAdminDash(tab = 'stats') {
  State.currentDashTab = tab;
  showAdminTab(tab);
  if (tab === 'stats')    await loadAdminStats();
  if (tab === 'patients') await loadAdminPatients();
  if (tab === 'medecins') await loadAdminMedecins();
  if (tab === 'users')    await loadAdminUsers();
}

function showAdminTab(tab) {
  document.querySelectorAll('#page-admin .dash-tab').forEach(el => el.style.display = 'none');
  const el = document.getElementById('atab-' + tab);
  if (el) el.style.display = '';
  document.querySelectorAll('#page-admin .sidebar-link').forEach(l => {
    l.classList.toggle('active', l.dataset.tab === tab);
  });
}

async function loadAdminStats() {
  try {
    const s = await Api.adminStats();
    document.getElementById('stat-patients').textContent  = s.total_patients;
    document.getElementById('stat-medecins').textContent  = s.total_medecins;
    document.getElementById('stat-pending').textContent   = s.medecins_en_attente;
    document.getElementById('stat-rdv-total').textContent = s.total_rendez_vous;
    document.getElementById('stat-rdv-conf').textContent  = s.rendez_vous_confirmes;
    document.getElementById('stat-rdv-ann').textContent   = s.rendez_vous_annules;
    document.getElementById('stat-rdv-mois').textContent  = s.rendez_vous_ce_mois;
  } catch(ex) { showToast(getErrMsg(ex), 'error'); }
}

async function loadAdminPatients(search = '') {
  const wrap = document.getElementById('admin-patients-table');
  wrap.innerHTML = '<p class="text-muted">Chargement…</p>';
  try {
    const res = await Api.adminPatients(search ? { search } : {});
    const list = res.data || res;
    if (!list.length) { wrap.innerHTML = '<div class="empty-state"><div class="empty-icon">👤</div><p>Aucun patient.</p></div>'; return; }
    wrap.innerHTML = `<div class="table-wrap"><table>
      <thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Statut</th><th>Actions</th></tr></thead>
      <tbody>${list.map(p => `<tr>
        <td class="td-name">${p.prenom} ${p.nom}</td>
        <td>${p.email}</td>
        <td>${p.telephone||'–'}</td>
        <td>${statusBadge(p.statut)}</td>
        <td class="flex gap-2">
          ${p.statut !== 'inactive' ? `<button class="btn btn-danger btn-sm" onclick="adminDeactivatePatient(${p.id}, this)">Désactiver</button>` : '<span class="badge badge-muted">Inactif</span>'}
        </td>
      </tr>`).join('')}</tbody>
    </table></div>`;
  } catch(ex) { wrap.innerHTML = `<p class="text-muted">${getErrMsg(ex)}</p>`; }
}

async function adminDeactivatePatient(id, btn) {
  if (!confirm('Désactiver ce compte patient ?')) return;
  setLoading(btn, true);
  try {
    await Api.deactivatePatient(id);
    showToast('Compte désactivé.', 'success');
    await loadAdminPatients();
  } catch(ex) { showToast(getErrMsg(ex), 'error'); setLoading(btn, false); }
}

async function loadAdminMedecins() {
  const wrap = document.getElementById('admin-medecins-table');
  wrap.innerHTML = '<p class="text-muted">Chargement…</p>';
  try {
    const res  = await Api.adminMedecins({});
    const list = res.data || res;
    if (!list.length) { wrap.innerHTML = '<div class="empty-state"><div class="empty-icon">👨‍⚕️</div><p>Aucun médecin.</p></div>'; return; }
    wrap.innerHTML = `<div class="table-wrap"><table>
      <thead><tr><th>Nom</th><th>Spécialité</th><th>Ville</th><th>Statut</th><th>Validé</th><th>Actions</th></tr></thead>
      <tbody>${list.map(m => `<tr>
        <td class="td-name">Dr ${m.prenom} ${m.nom}</td>
        <td>${m.doctor_profile?.specialite||'–'}</td>
        <td>${m.doctor_profile?.ville||'–'}</td>
        <td>${statusBadge(m.statut)}</td>
        <td>${m.doctor_profile?.valide ? '<span class="badge badge-success">✓ Validé</span>' : '<span class="badge badge-warning">En attente</span>'}</td>
        <td class="flex gap-2" style="flex-wrap:wrap">
          ${!m.doctor_profile?.valide ? `<button class="btn btn-primary btn-sm" onclick="adminValidateMedecin(${m.id}, this)">Valider</button>` : ''}
          ${m.statut !== 'inactive' ? `<button class="btn btn-danger btn-sm" onclick="adminDeactivateMedecin(${m.id}, this)">Désactiver</button>` : ''}
        </td>
      </tr>`).join('')}</tbody>
    </table></div>`;
  } catch(ex) { wrap.innerHTML = `<p class="text-muted">${getErrMsg(ex)}</p>`; }
}

async function adminValidateMedecin(id, btn) {
  setLoading(btn, true);
  try {
    await Api.validateMedecin(id);
    showToast('Médecin validé !', 'success');
    await loadAdminMedecins();
  } catch(ex) { showToast(getErrMsg(ex), 'error'); setLoading(btn, false); }
}

async function adminDeactivateMedecin(id, btn) {
  if (!confirm('Désactiver ce compte médecin ?')) return;
  setLoading(btn, true);
  try {
    await Api.deactivateMedecin(id);
    showToast('Compte désactivé.', 'success');
    await loadAdminMedecins();
  } catch(ex) { showToast(getErrMsg(ex), 'error'); setLoading(btn, false); }
}

async function loadAdminUsers() {
  const wrap = document.getElementById('admin-users-table');
  wrap.innerHTML = '<p class="text-muted">Chargement…</p>';
  try {
    const res  = await Api.adminUsers({});
    const list = res.data || res;
    wrap.innerHTML = `<div class="table-wrap"><table>
      <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Actions</th></tr></thead>
      <tbody>${list.map(u => `<tr>
        <td class="td-name">${u.prenom} ${u.nom}</td>
        <td>${u.email}</td>
        <td><span class="badge badge-info">${u.role}</span></td>
        <td>${statusBadge(u.statut)}</td>
        <td>
          <select class="form-control" style="width:120px;padding:0.3rem 0.6rem;font-size:0.82rem" onchange="adminAssignRole(${u.id}, this.value, this)">
            <option value="">Rôle…</option>
            <option value="patient" ${u.role==='patient'?'selected':''}>Patient</option>
            <option value="medecin" ${u.role==='medecin'?'selected':''}>Médecin</option>
            <option value="admin"   ${u.role==='admin'?'selected':''}>Admin</option>
          </select>
        </td>
      </tr>`).join('')}</tbody>
    </table></div>`;
  } catch(ex) { wrap.innerHTML = `<p class="text-muted">${getErrMsg(ex)}</p>`; }
}

async function adminAssignRole(id, role, sel) {
  if (!role) return;
  try {
    await Api.assignRole(id, role);
    showToast('Rôle mis à jour.', 'success');
  } catch(ex) { showToast(getErrMsg(ex), 'error'); }
}

// Admin create patient
async function handleAdminCreatePatient(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  setLoading(btn, true);
  try {
    await Api.createPatient({
      nom:      document.getElementById('ap-nom').value,
      prenom:   document.getElementById('ap-prenom').value,
      email:    document.getElementById('ap-email').value,
      password: document.getElementById('ap-pass').value,
      telephone:document.getElementById('ap-tel').value || undefined,
    });
    closeModal('modal-create-patient');
    e.target.reset();
    showToast('Patient créé !', 'success');
    await loadAdminPatients();
  } catch(ex) { showToast(getErrMsg(ex), 'error'); }
  finally { setLoading(btn, false); }
}

// =============================================================================
// ROLE TOGGLE on Register
// =============================================================================
function setRole(role) {
  document.getElementById('reg-role').value = role;
  document.querySelectorAll('.role-btn').forEach(b => b.classList.toggle('selected', b.dataset.r === role));
  document.getElementById('doctor-fields').style.display = role === 'medecin' ? '' : 'none';
}

// =============================================================================
// BOOT
// =============================================================================
document.addEventListener('DOMContentLoaded', () => {
  // Check existing session
  const token = localStorage.getItem('dawini_token');
  const user  = State.getUser();

  if (token && user) {
    redirectByRole(user.role);
  } else {
    showPage('page-home');
  }

  // Close modals on overlay click
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) overlay.classList.remove('open');
    });
  });
});
// =============================================================================
// PASSWORD RESET — 3-step flow: email → code → new password
// =============================================================================

// STEP 1: User enters email → API sends 6-digit code
async function handleForgotPass(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  setLoading(btn, true);
  try {
    const email = document.getElementById('forgot-email').value;
    await Api.forgotPass({ email });
    // Save email in sessionStorage to use in steps 2 and 3
    sessionStorage.setItem('reset_email', email);
    showToast('Code envoyé par email !', 'success');
    showPage('page-verify-code');
  } catch(ex) {
    showToast(getErrMsg(ex), 'error');
  } finally { setLoading(btn, false); }
}

// STEP 2: User enters the 6-digit code → verified by API
async function handleVerifyCode(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  const err = document.getElementById('verify-err');
  err.style.display = 'none';
  setLoading(btn, true);
  try {
    const email = sessionStorage.getItem('reset_email');
    const code  = document.getElementById('verify-code').value.trim();
    await Api.verifyCode({ email, code });
    // Save code to use in step 3
    sessionStorage.setItem('reset_code', code);
    showToast('Code validé !', 'success');
    showPage('page-new-password');
  } catch(ex) {
    err.textContent = getErrMsg(ex);
    err.style.display = 'flex';
  } finally { setLoading(btn, false); }
}

// STEP 3: User sets a new password → API saves it
async function handleResetPassword(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  const err = document.getElementById('newpass-err');
  err.style.display = 'none';
  setLoading(btn, true);
  try {
    await Api.resetPass({
      email:                 sessionStorage.getItem('reset_email'),
      code:                  sessionStorage.getItem('reset_code'),
      password:              document.getElementById('new-pass').value,
      password_confirmation: document.getElementById('new-pass-confirm').value,
    });
    // Clean up session storage
    sessionStorage.removeItem('reset_email');
    sessionStorage.removeItem('reset_code');
    showToast('Mot de passe changé ! Connectez-vous.', 'success');
    showPage('page-login');
  } catch(ex) {
    err.textContent = getErrMsg(ex);
    err.style.display = 'flex';
  } finally { setLoading(btn, false); }
}

// Resend code — go back to forgot page and clear stored email
function resendCode() {
  sessionStorage.removeItem('reset_email');
  sessionStorage.removeItem('reset_code');
  showPage('page-forgot');
}
