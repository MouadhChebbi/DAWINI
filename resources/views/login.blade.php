<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>DAWINI — Login</title>
  @vite(['resources/js/login.js'])
  @vite(['resources/css/login.css'])
</head>
<body>

  <!-- BACKGROUND -->
  <div class="bg-mesh"></div>
  <div class="bg-noise"></div>
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
      <a href="/loginpage" class="active">Login</a>
    </div>
  </nav>

  <!-- MAIN -->
  <main>
    <div class="card">

      <!-- LEFT PANEL -->
      <div class="panel-left">
        <div class="brand">DAWINI</div>
        <p class="tagline">Your trusted digital bridge connecting doctors and patients</p>
        <div class="illus-box">
          <svg viewBox="0 0 300 230" fill="none" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="150" cy="105" rx="95" ry="57" fill="rgba(46,207,191,.09)" stroke="rgba(46,207,191,.45)" stroke-width="2"/>
            <circle cx="150" cy="105" r="34" fill="rgba(9,24,41,.9)" stroke="rgba(46,207,191,.55)" stroke-width="2"/>
            <circle cx="150" cy="105" r="19" fill="rgba(46,207,191,.18)"/>
            <circle cx="150" cy="105" r="9"  fill="rgba(5,14,28,.95)"/>
            <circle cx="156" cy="99"  r="4"  fill="rgba(255,255,255,.35)"/>
            <circle cx="150" cy="105" r="44" fill="none" stroke="rgba(46,207,191,.07)" stroke-width="10"/>
            <line x1="118" y1="66" x2="117" y2="52" stroke="rgba(46,207,191,.28)" stroke-width="1.5"/>
            <line x1="133" y1="62" x2="132" y2="48" stroke="rgba(46,207,191,.28)" stroke-width="1.5"/>
            <line x1="150" y1="60" x2="150" y2="46" stroke="rgba(46,207,191,.28)" stroke-width="1.5"/>
            <line x1="167" y1="62" x2="168" y2="48" stroke="rgba(46,207,191,.28)" stroke-width="1.5"/>
            <line x1="182" y1="66" x2="183" y2="52" stroke="rgba(46,207,191,.28)" stroke-width="1.5"/>
            <circle cx="50"  cy="128" r="11" fill="rgba(255,255,255,.08)" stroke="rgba(46,207,191,.3)"  stroke-width="1.2"/>
            <rect   x="42"  y="140" width="16" height="38" rx="5" fill="rgba(255,255,255,.06)" stroke="rgba(46,207,191,.18)" stroke-width="1"/>
            <rect   x="38"  y="150" width="8"  height="22" rx="3" fill="rgba(46,207,191,.1)"/>
            <rect   x="54"  y="150" width="8"  height="22" rx="3" fill="rgba(46,207,191,.1)"/>
            <circle cx="250" cy="128" r="11" fill="rgba(240,100,73,.1)"  stroke="rgba(240,100,73,.28)" stroke-width="1.2"/>
            <rect   x="242" y="140" width="16" height="38" rx="5" fill="rgba(240,100,73,.06)" stroke="rgba(240,100,73,.18)" stroke-width="1"/>
            <rect   x="238" y="150" width="8"  height="22" rx="3" fill="rgba(240,100,73,.1)"/>
            <rect   x="254" y="150" width="8"  height="22" rx="3" fill="rgba(240,100,73,.1)"/>
            <rect   x="100" y="172" width="100" height="56" rx="8" fill="rgba(46,207,191,.06)" stroke="rgba(46,207,191,.22)" stroke-width="1.5"/>
            <rect   x="109" y="180" width="82"  height="40" rx="4" fill="rgba(46,207,191,.09)"/>
            <polyline points="113,200 123,200 129,188 136,214 143,200 167,200 173,191 179,210 185,200 191,200"
              fill="none" stroke="#2ecfbf" stroke-width="1.8" opacity=".75"/>
            <circle cx="30"  cy="90"  r="4" fill="rgba(46,207,191,.35)"/>
            <circle cx="20"  cy="108" r="3" fill="rgba(240,100,73,.35)"/>
            <circle cx="270" cy="88"  r="4" fill="rgba(46,207,191,.35)"/>
            <circle cx="280" cy="108" r="3" fill="rgba(240,100,73,.3)"/>
          </svg>
        </div>
      </div>

      <!-- RIGHT PANEL -->
      <div class="panel-right">

        <!-- FORM -->
        <div id="form-content">
          <h2>Welcome back <span>!</span></h2>

          <div class="form-group">
            <label id="lbl-email">Email</label>
            <input type="email" id="email" placeholder="example@gmail.com" autocomplete="email"/>
            <span class="err-msg" id="err-email">Please enter a valid email.</span>
          </div>

          <div class="form-group">
            <label id="lbl-pass">Password</label>
            <input type="password" id="password" placeholder="••••••••••" autocomplete="current-password"/>
            <span class="err-msg" id="err-pass">Password must be at least 8 characters.</span>
          </div>
          <a href="/forgotpasswordpage" style="text-decoration: none;">
            <button class="forgot">Forgot your password?</button>
          </a>
          

          <button class="btn-primary" id="login-btn">Login</button>

          <div class="divider"><span>or continue with</span></div>

          <p class="alt-link">
            You don't have an account? <a href="/">SIGN UP</a>
          </p>
        </div>
        <!-- SUCCESS STATE -->
        <div class="success-box" id="success-box">
          <div class="success-icon">✅</div>
          <h3>Welcome back!</h3>
          <p>You're now logged in to DAWINI.</p>
          <a href="#">Go to Dashboard</a>
        </div>

      </div>
    </div>
  </main>


</body>
</html>