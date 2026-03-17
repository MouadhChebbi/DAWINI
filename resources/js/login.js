/* ═══════════════════════════════════════════════
   DAWINI — login.js
   Login form validation, interactions & API
═══════════════════════════════════════════════ */

/* ── API CONFIG ──────────────────────────── */
const API_BASE = 'http://127.0.0.1:8000/api';

/* ── TOKEN HELPERS ───────────────────────── */
function saveToken(token) { 
  localStorage.setItem('dawini_token', token); 
}

function getToken() { 
  return localStorage.getItem('dawini_token'); 
}

function removeToken() {
  localStorage.removeItem('dawini_token');
}

/* ── LOGIN API CALL ──────────────────────── */
async function loginUser(email, password) {
  try {
    const response = await fetch(`${API_BASE}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ email, password }),
    });

    const data = await response.json();

    if (!response.ok) {
      return {
        success: false,
        status: response.status,
        message: data.message || 'Login failed.',
        errors: data.errors || null,
      };
    }

    // Save token if returned
    if (data.token) {
      saveToken(data.token);
    }
    
    return { success: true, data };

  } catch (error) {
    console.error('API Error:', error);
    return { 
      success: false, 
      message: 'Unable to reach the server. Please check your connection.' 
    };
  }
}

/* ── DOM REFS ────────────────────────────── */
const emailEl    = document.getElementById('email');
const passEl     = document.getElementById('password');
const errEmail   = document.getElementById('err-email');
const errPass    = document.getElementById('err-pass');
const lblEmail   = document.getElementById('lbl-email');
const lblPass    = document.getElementById('lbl-pass');
const loginBtn   = document.getElementById('login-btn');
const formBox    = document.getElementById('form-content');
const successBox = document.getElementById('success-box');
const forgotBtn  = document.querySelector('.forgot');

/* ── HELPERS ─────────────────────────────── */
function showError(inputEl, errEl, lblEl, message) {
  inputEl.classList.add('error');
  if (message) errEl.textContent = message;
  errEl.classList.add('show');
  if (lblEl) lblEl.classList.add('error');
}

function clearError(inputEl, errEl, lblEl) {
  inputEl.classList.remove('error');
  errEl.classList.remove('show');
  if (lblEl) lblEl.classList.remove('error');
}

function isValidEmail(val) {
  return /\S+@\S+\.\S+/.test(val);
}

/* ── LIVE CLEAR ON INPUT ─────────────────── */
emailEl.addEventListener('input', () => {
  clearError(emailEl, errEmail, lblEmail);
  // Reset to default message
  errEmail.textContent = 'Please enter a valid email.';
});

passEl.addEventListener('input', () => {
  clearError(passEl, errPass, lblPass);
  // Reset to default message
  errPass.textContent = 'Password must be at least 8 characters.';
});

/* ── VALIDATE ────────────────────────────── */
function validate() {
  let valid = true;
  const email = emailEl.value.trim();
  const password = passEl.value;

  // Email validation
  if (!email) {
    showError(emailEl, errEmail, lblEmail, 'Email is required.');
    valid = false;
  } else if (!isValidEmail(email)) {
    showError(emailEl, errEmail, lblEmail, 'Please enter a valid email address.');
    valid = false;
  }

  // Password validation
  if (!password) {
    showError(passEl, errPass, lblPass, 'Password is required.');
    valid = false;
  } else if (password.length < 8) {
    showError(passEl, errPass, lblPass, 'Password must be at least 8 characters.');
    valid = false;
  }

  return valid;
}

/* ── SET LOADING STATE ───────────────────── */
function setLoading(isLoading) {
  loginBtn.textContent = isLoading ? 'Logging in...' : 'Login';
  loginBtn.style.opacity = isLoading ? '0.7' : '1';
  loginBtn.disabled = isLoading;
}

/* ── SHOW API ERROR ──────────────────────── */
function showApiError(message) {
  // Clear any existing errors first
  clearError(emailEl, errEmail, lblEmail);
  clearError(passEl, errPass, lblPass);
  
  // Show error under password field — wrong credentials relate to the password
  errPass.textContent = message || 'Incorrect email or password. Please try again.';
  showError(passEl, errPass, lblPass);
}

/* ── HANDLE SUCCESSFUL LOGIN ─────────────── */
function handleLoginSuccess(userData) {
  console.log('Login successful:', userData);
  
  // Hide form and show success message
  formBox.style.display = 'none';
  successBox.classList.add('show');
  
  // Optional: Store additional user data
  if (userData.user) {
    localStorage.setItem('dawini_user', JSON.stringify(userData.user));
  }
  
  // Redirect after delay
  setTimeout(() => {
    window.location.href = 'dashboard.html'; // Change to your dashboard URL
  }, 2000);
}

/* ── SUBMIT ──────────────────────────────── */
async function handleLogin(e) {
  if (e) e.preventDefault();
  
  // Validate form
  if (!validate()) return;

  // Set loading state
  setLoading(true);

  // Get values
  const email = emailEl.value.trim();
  const password = passEl.value;

  // Call API
  const result = await loginUser(email, password);

  if (result.success) {
    handleLoginSuccess(result.data);
  } else {
    // Handle error
    setLoading(false);

    // Handle specific server-side field errors
    if (result.errors) {
      // Email errors
      if (result.errors.email) {
        showError(emailEl, errEmail, lblEmail, result.errors.email[0]);
      }
      // Password errors
      if (result.errors.password) {
        showError(passEl, errPass, lblPass, result.errors.password[0]);
      }
    } else {
      // Show general message
      showApiError(result.message);
    }
  }
}

/* ── FORGOT PASSWORD HANDLER ─────────────── */
function handleForgotPassword() {
  const email = emailEl.value.trim();
  
  if (email && isValidEmail(email)) {
    // Store email for password reset page
    sessionStorage.setItem('reset_email', email);
  }
  
  window.location.href = '../forgot/forgot-password.html';
}

/* ── CHECK AUTH STATUS ───────────────────── */
function checkAuthStatus() {
  const token = getToken();
  if (token) {
    console.log('User is already logged in');
    // Optional: Redirect to dashboard if already logged in
    // window.location.href = 'dashboard.html';
  }
}

/* ── EVENT LISTENERS ─────────────────────── */
loginBtn.addEventListener('click', handleLogin);

if (forgotBtn) {
  forgotBtn.addEventListener('click', handleForgotPassword);
}

/* Enter key triggers submit */
document.addEventListener('keydown', function (e) {
  if (e.key === 'Enter' && document.activeElement !== forgotBtn) {
    handleLogin(e);
  }
});

/* Initialize on page load */
document.addEventListener('DOMContentLoaded', () => {
  checkAuthStatus();
  
  // Clear any old success states
  formBox.style.display = 'block';
  successBox.classList.remove('show');
  
  // Focus email field
  emailEl.focus();
});

/* ── EXPORT FOR TESTING (optional) ───────── */
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { loginUser, validate, isValidEmail };
}