<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>DAWINI — Login</title>
    @vite('resources/css/login.css')
    @vite('resources/js/login.js')
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
      <a href='/' class="btn-pill">Sign Up</a>
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
          <img src="image.png" alt="">
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

          <button class="forgot">Forgot your password?</button>

          <button class="btn-primary" id="login-btn">Login</button>

          <div class="divider"><span>or continue with</span></div>

          <p class="alt-link">
            You don't have an account? <a href="signup.html">SIGN UP</a>
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