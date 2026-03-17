<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>DAWINI — Forgot Password</title>
  @vite('resources/css/login.css')
  @vite('resources/css/forgot-password.css')
  @vite('resources/js/forgot-password.js')
</head>
<body>

  <!-- BACKGROUND -->
  <div class="bg-mesh"></div>
  <!-- <div class="bg-noise"></div> -->
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <!-- NAV -->
  <nav>
    <a href="login.html" class="nav-logo">DAWINI</a>
    <div class="nav-search">
      <svg width="13" height="13" fill="none" stroke="#6fa8bf" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"/>
        <path d="M21 21l-4.35-4.35"/>
      </svg>
      <input type="text" placeholder="Search your Doctor"/>
    </div>
    <div class="nav-links">
      <a href="#">Home</a>
      <a href="#">Filter Doctors</a>
      <a href="/" class="btn-pill">Sign Up</a>
      <a href="/loginpage">Login</a>
    </div>
  </nav>

  <!-- MAIN -->
  <main>
    <div class="card">
      <div class="panel-single">

        <!-- ── STEP 1: EMAIL ── -->
        <div class="view active" id="view-email">
          <a href="../login/login.html" class="back-link">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Back to Login
          </a>

          <div class="steps">
            <div class="step-dot active" id="dot-1"></div>
            <div class="step-dot" id="dot-2"></div>
            <div class="step-dot" id="dot-3"></div>
          </div>

          <h2>Forgot your <span>password?</span></h2>
          <p class="subtitle">Enter your email and we'll send you a code to reset it.</p>

          <div class="form-group">
            <label id="lbl-fp-email">Email</label>
            <input type="email" id="fp-email" placeholder="example@gmail.com" autocomplete="email"/>
            <span class="err-msg" id="err-fp-email">Please enter a valid email.</span>
          </div>

          <button class="btn-primary" id="send-code-btn" style="margin-top:0.6rem;">Send Reset Code</button>
        </div>

        <!-- ── STEP 2: OTP ── -->
        <div class="view" id="view-otp">
          <a href="#" class="back-link" id="back-to-email">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Back
          </a>

          <div class="steps">
            <div class="step-dot done"></div>
            <div class="step-dot active"></div>
            <div class="step-dot"></div>
          </div>

          <h2>Check your <span>email</span></h2>
          <p class="subtitle" id="otp-subtitle">We sent a 6-digit code to <strong style="color:var(--white)">you</strong>. It expires in 10 minutes.</p>

          <div class="form-group" style="margin-bottom:0.5rem;">
            <label>Verification Code</label>
            <div class="otp-row" id="otp-row">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code"/>
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
            </div>
            <span class="err-msg" id="err-otp">Please enter the 6-digit code.</span>
          </div>

          <div class="resend-row">
            <span id="countdown-text">Resend code in <strong id="countdown">60s</strong></span>
            <button id="resend-btn" disabled>Resend</button>
          </div>

          <button class="btn-primary" id="verify-btn">Verify Code</button>
        </div>

        <!-- ── STEP 3: NEW PASSWORD ── -->
        <div class="view" id="view-newpass">
          <a href="#" class="back-link" id="back-to-otp">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Back
          </a>

          <div class="steps">
            <div class="step-dot done"></div>
            <div class="step-dot done"></div>
            <div class="step-dot active"></div>
          </div>

          <h2>New <span>password</span></h2>
          <p class="subtitle">Choose a strong password you haven't used before.</p>

          <div class="form-group">
            <label id="lbl-newpass">New Password</label>
            <div class="pass-wrap">
              <input type="password" id="new-pass" placeholder="••••••••••" autocomplete="new-password"/>
              <button class="pass-toggle" data-target="new-pass" type="button" aria-label="Toggle password">
                <svg class="eye-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
            <span class="err-msg" id="err-newpass">Password must be at least 8 characters.</span>
          </div>

          <div class="strength-bar" id="strength-bar">
            <div class="strength-seg" id="seg-1"></div>
            <div class="strength-seg" id="seg-2"></div>
            <div class="strength-seg" id="seg-3"></div>
            <div class="strength-seg" id="seg-4"></div>
          </div>
          <p class="strength-label" id="strength-label"></p>

          <div class="form-group" style="margin-bottom:1.2rem;">
            <label id="lbl-confirm">Confirm Password</label>
            <div class="pass-wrap">
              <input type="password" id="confirm-pass" placeholder="••••••••••" autocomplete="new-password"/>
              <button class="pass-toggle" data-target="confirm-pass" type="button" aria-label="Toggle confirm password">
                <svg class="eye-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
            <span class="err-msg" id="err-confirm">Passwords do not match.</span>
          </div>

          <button class="btn-primary" id="reset-btn">Reset Password</button>
        </div>

        <!-- ── SUCCESS STATE ── -->
        <div class="success-box" id="success-box">
          <div class="success-icon">🔓</div>
          <h3>Password Reset!</h3>
          <p>Your password has been updated successfully. You can now log in.</p>
          <a href="/loginpage">Back to Login</a>
        </div>

      </div>
    </div>
  </main>

</body>
</html>
