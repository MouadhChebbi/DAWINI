  /* ═══════════════════════════════════════════
     DAWINI — forgot-password.js (inline)
  ═══════════════════════════════════════════ */

  const API_BASE = 'http://127.0.0.1:8000/api';

  /* ── VIEWS ─────────────────────────────── */
  const views = {
    email:   document.getElementById('view-email'),
    otp:     document.getElementById('view-otp'),
    newpass: document.getElementById('view-newpass'),
  };

  function showView(name) {
    Object.values(views).forEach(v => v.classList.remove('active'));
    if (views[name]) views[name].classList.add('active');
  }

  /* ── DOM REFS ───────────────────────────── */
  const fpEmailEl    = document.getElementById('fp-email');
  const errFpEmail   = document.getElementById('err-fp-email');
  const lblFpEmail   = document.getElementById('lbl-fp-email');
  const sendCodeBtn  = document.getElementById('send-code-btn');

  const otpInputs    = Array.from(document.querySelectorAll('#otp-row input'));
  const errOtp       = document.getElementById('err-otp');
  const otpSubtitle  = document.getElementById('otp-subtitle');
  const verifyBtn    = document.getElementById('verify-btn');
  const resendBtn    = document.getElementById('resend-btn');
  const countdownEl  = document.getElementById('countdown');
  const countdownText = document.getElementById('countdown-text');

  const newPassEl    = document.getElementById('new-pass');
  const confirmPassEl= document.getElementById('confirm-pass');
  const errNewPass   = document.getElementById('err-newpass');
  const errConfirm   = document.getElementById('err-confirm');
  const lblNewPass   = document.getElementById('lbl-newpass');
  const lblConfirm   = document.getElementById('lbl-confirm');
  const resetBtn     = document.getElementById('reset-btn');
  const successBox   = document.getElementById('success-box');

  /* ── HELPERS ────────────────────────────── */
  function isValidEmail(val) { return /\S+@\S+\.\S+/.test(val); }

  function showError(inputEl, errEl, lblEl, msg) {
    inputEl.classList.add('error');
    if (msg) errEl.textContent = msg;
    errEl.classList.add('show');
    if (lblEl) lblEl.classList.add('error');
  }

  function clearError(inputEl, errEl, lblEl) {
    inputEl.classList.remove('error');
    errEl.classList.remove('show');
    if (lblEl) lblEl.classList.remove('error');
  }

  function setLoading(btn, isLoading, label = 'Loading...') {
    btn.textContent = isLoading ? label : btn.dataset.label;
    btn.style.opacity = isLoading ? '0.7' : '1';
    btn.disabled = isLoading;
  }

  /* Save default labels */
  [sendCodeBtn, verifyBtn, resetBtn].forEach(b => b.dataset.label = b.textContent);

  /* ── STEP 1: SEND CODE ──────────────────── */
  let userEmail = '';

  fpEmailEl.addEventListener('input', () => clearError(fpEmailEl, errFpEmail, lblFpEmail));

  sendCodeBtn.addEventListener('click', async () => {
    const email = fpEmailEl.value.trim();

    if (!email) {
      showError(fpEmailEl, errFpEmail, lblFpEmail, 'Email is required.'); return;
    }
    if (!isValidEmail(email)) {
      showError(fpEmailEl, errFpEmail, lblFpEmail, 'Please enter a valid email address.'); return;
    }

    setLoading(sendCodeBtn, true, 'Sending...');

    try {
      const res = await fetch(`${API_BASE}/forgotpassword`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email }),
      });

      const data = await res.json();

      if (!res.ok) {
        // 404 → user not found, but we show a generic message for security
        const msg = res.status === 404
          ? 'If this email is registered, a code has been sent.'
          : (data.message || 'Something went wrong. Please try again.');

        // For 404 we still proceed to OTP view (security: don't reveal if email exists)
        if (res.status !== 404) {
          showError(fpEmailEl, errFpEmail, lblFpEmail, msg);
          return;
        }
      }

      userEmail = email;
      otpSubtitle.innerHTML = `We sent a 6-digit code to <strong style="color:var(--white)">${email}</strong>. It expires in 10 minutes.`;
      showView('otp');
      otpInputs[0].focus();
      startCountdown();

    } catch {
      showError(fpEmailEl, errFpEmail, lblFpEmail, 'Something went wrong. Please try again.');
    } finally {
      setLoading(sendCodeBtn, false);
    }
  });

  /* ── STEP 2: OTP ────────────────────────── */
  otpInputs.forEach((input, i) => {
    input.addEventListener('input', (e) => {
      const val = e.target.value.replace(/\D/g, '');
      input.value = val ? val[0] : '';
      input.classList.toggle('filled', !!input.value);

      // Clear error on type
      otpInputs.forEach(el => el.classList.remove('error'));
      errOtp.classList.remove('show');

      // Auto-advance
      if (val && i < otpInputs.length - 1) {
        otpInputs[i + 1].focus();
      }
    });

    input.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && !input.value && i > 0) {
        otpInputs[i - 1].focus();
      }
    });

    input.addEventListener('paste', (e) => {
      e.preventDefault();
      const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
      pasted.split('').forEach((ch, j) => {
        if (otpInputs[i + j]) {
          otpInputs[i + j].value = ch;
          otpInputs[i + j].classList.add('filled');
        }
      });
      const next = Math.min(i + pasted.length, otpInputs.length - 1);
      otpInputs[next].focus();
    });
  });

  verifyBtn.addEventListener('click', async () => {
    const code = otpInputs.map(el => el.value).join('');
    if (code.length < 6) {
      otpInputs.forEach(el => el.classList.add('error'));
      errOtp.textContent = 'Please enter the full 6-digit code.';
      errOtp.classList.add('show');
      return;
    }

    setLoading(verifyBtn, true, 'Verifying...');

    try {
      const res = await fetch(`${API_BASE}/verifycode`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: userEmail, code }),
      });

      const data = await res.json();

      if (!res.ok) {
        otpInputs.forEach(el => el.classList.add('error'));
        errOtp.textContent = data.message || 'Invalid or expired code. Please try again.';
        errOtp.classList.add('show');
        return;
      }

      showView('newpass');
      newPassEl.focus();

    } catch {
      otpInputs.forEach(el => el.classList.add('error'));
      errOtp.textContent = 'Something went wrong. Please try again.';
      errOtp.classList.add('show');
    } finally {
      setLoading(verifyBtn, false);
    }
  });

  /* Countdown timer */
  let countdownInterval = null;

  function startCountdown(seconds = 60) {
    clearInterval(countdownInterval);
    resendBtn.disabled = true;
    countdownText.style.display = 'inline';
    let remaining = seconds;
    countdownEl.textContent = remaining + 's';

    countdownInterval = setInterval(() => {
      remaining--;
      countdownEl.textContent = remaining + 's';
      if (remaining <= 0) {
        clearInterval(countdownInterval);
        countdownText.style.display = 'none';
        resendBtn.disabled = false;
      }
    }, 1000);
  }

  resendBtn.addEventListener('click', async () => {
    resendBtn.disabled = true;
    otpInputs.forEach(el => { el.value = ''; el.classList.remove('filled', 'error'); });
    errOtp.classList.remove('show');

    try {
      await fetch(`${API_BASE}/forgotpassword`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: userEmail }),
      });
      // Silently restart — don't surface errors on resend
    } finally {
      startCountdown();
    }
  });

  /* ── STEP 3: NEW PASSWORD ───────────────── */
  const segs = [1,2,3,4].map(n => document.getElementById(`seg-${n}`));
  const strengthLabelEl = document.getElementById('strength-label');

  function scorePassword(pw) {
    let score = 0;
    if (pw.length >= 8)  score++;
    if (pw.length >= 12) score++;
    if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
    if (/\d/.test(pw) && /[^A-Za-z0-9]/.test(pw)) score++;
    return score;
  }

  const strengthMap = ['', 'weak', 'fair', 'good', 'strong'];
  const strengthNames = ['', 'Weak', 'Fair', 'Good', 'Strong'];

  newPassEl.addEventListener('input', () => {
    clearError(newPassEl, errNewPass, lblNewPass);
    const score = scorePassword(newPassEl.value);
    segs.forEach((seg, i) => {
      seg.className = 'strength-seg';
      if (newPassEl.value && i < score) seg.classList.add(strengthMap[score]);
    });
    strengthLabelEl.textContent = newPassEl.value ? strengthNames[score] : '';
  });

  confirmPassEl.addEventListener('input', () => clearError(confirmPassEl, errConfirm, lblConfirm));

  resetBtn.addEventListener('click', async () => {
    let valid = true;
    const pw  = newPassEl.value;
    const cpw = confirmPassEl.value;

    if (!pw || pw.length < 8) {
      showError(newPassEl, errNewPass, lblNewPass, 'Password must be at least 8 characters.');
      valid = false;
    }
    if (!cpw) {
      showError(confirmPassEl, errConfirm, lblConfirm, 'Please confirm your password.');
      valid = false;
    } else if (pw !== cpw) {
      showError(confirmPassEl, errConfirm, lblConfirm, 'Passwords do not match.');
      valid = false;
    }
    if (!valid) return;

    setLoading(resetBtn, true, 'Resetting...');

    try {
      const res = await fetch(`${API_BASE}/resetpassword`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: userEmail, new_password: pw, password_confirmation: cpw }),
      });

      const data = await res.json();

      if (!res.ok) {
        showError(newPassEl, errNewPass, lblNewPass, data.message || 'Reset failed. Please try again.');
        return;
      }

      views.newpass.classList.remove('active');
      successBox.classList.add('show');

    } catch {
      showError(newPassEl, errNewPass, lblNewPass, 'Reset failed. Please try again.');
    } finally {
      setLoading(resetBtn, false);
    }
  });

  /* ── PASSWORD TOGGLES ───────────────────── */
  document.querySelectorAll('.pass-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.target);
      const isText = target.type === 'text';
      target.type = isText ? 'password' : 'text';
      btn.querySelector('.eye-icon').style.opacity = isText ? '1' : '0.45';
    });
  });

  /* ── BACK BUTTONS ───────────────────────── */
  document.getElementById('back-to-email').addEventListener('click', (e) => {
    e.preventDefault();
    clearInterval(countdownInterval);
    showView('email');
  });

  document.getElementById('back-to-otp').addEventListener('click', (e) => {
    e.preventDefault();
    showView('otp');
    startCountdown(60);
  });

  /* ── ENTER KEY ──────────────────────────── */
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter') return;
    const active = document.querySelector('.view.active');
    if (active?.id === 'view-email')   sendCodeBtn.click();
    if (active?.id === 'view-otp')     verifyBtn.click();
    if (active?.id === 'view-newpass') resetBtn.click();
  });

  /* ── PRE-FILL FROM sessionStorage (set by login.js) ── */
  document.addEventListener('DOMContentLoaded', () => {
    const storedEmail = sessionStorage.getItem('reset_email');
    if (storedEmail) {
      fpEmailEl.value = storedEmail;
      sessionStorage.removeItem('reset_email');
    }
    fpEmailEl.focus();
  });