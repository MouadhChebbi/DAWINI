


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>DAWINI — Sign Up</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  @vite('resources/css/signup.css')
  @vite('resources/js/signup.js')
</head>
<body>
  <div class="bg"></div>
  <div class="blob blob1"></div>
  <div class="blob blob2"></div>
  <!-- NAV -->
  <nav>
    <a href="dawini-concept.html" class="nav-logo">DAWINI</a>
    <div class="nav-search">
      <svg width="14" height="14" fill="none" stroke="#7fb3c8" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input type="text" placeholder="Search your Doctor"/>
    </div>
    <ul class="nav-links">
      <li class="hide-sm"><a href="dawini-concept.html">Home</a></li>
      <li class="hide-sm"><a href="dawini-usecase.html">Filter Doctors</a></li>
      <li><a href="/" class="active">Sign Up</a></li>
      <li><a href="/loginpage">Login</a></li>
    </ul>
  </nav>

  <main>
    <div class="card">
      <!-- LEFT -->
      <div class="panel-left">
        <div class="brand">DAWINI</div>
        <p class="tagline">Your trusted digital bridge connecting doctors and patients</p>
        
        <div class="role-label">Sign up as?</div>
        <div class="role-btns">
          <button class="role-btn" onclick="this.classList.add('active');this.parentElement.querySelector('.role-btn:last-child').classList.remove('active')">Doctor</button>
          <button class="role-btn active" onclick="this.classList.add('active');this.parentElement.querySelector('.role-btn:first-child').classList.remove('active')">Patient</button>
        </div>
        <div class="illus">
          <!-- Medical eye / doctor illustration as SVG -->
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

      <!-- RIGHT / FORM -->
      <div class="panel-right">
        <div class="form-grid">
          <div class="form-group">
            <label>FIRST NAME</label>
            <input type="text" placeholder="Jhonson" id="name" >
            <small class="error" id="name-error"></small>
          </div>
          <div class="form-group">
            <label>LAST NAME</label>
            <input type="text" placeholder="Smith" id="lastname" >
            <small class="error" id="lastname-error"></small>
          </div>
          <div class="form-group form-full">
            <label>Email</label>
            <input type="email" placeholder="example@gmail.com" id="mail" >
            <small class="error" id="mail-error"></small>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" placeholder="••••••••••" id="password" >
            <small class="error" id="password-error"></small>
          </div>
          <div class="form-group">
            <label>Confirm password</label>
            <input type="password" placeholder="••••••••••" id="confirmPassword">
            <small id="confirmPassword-error" class="error"></small>
          </div>
                    <div class="form-group">
            <label>Phone number</label>
            <input type="tel" placeholder="xx xxx xxx" id="phone"/>
            <small class="error" id="phone-error"></small>
          </div>
          <!-- SPECIALTY + GENDER -->
          

          <div class="form-group" >
            <label>Speciality</label>
            <select id="speciality">
              <option value="" disabled selected>Select ur speciality</option>
              <option>Cardiology</option>
              <option>Neurology</option>
              <option>Pediatrics</option>
              <option>Orthopedics</option>
              <option>Ophthalmology</option>
              <option>Dermatology</option>s
              <option>Psychiatry</option>
              <option>General Practice</option>
            </select>
            <small class="error" id="speciality-error"></small>
          </div>


          <div class="form-group">
            <label>Office Address</label>
            <input type="text" placeholder="ex.st, city, ZIP" id="address">
            <small class="error" id="address-error"></small>
          </div>

          <!-- UPLOAD -->
          <div class="form-group form-full">
            <label>Upload your certification</label>
            <div class="upload-row">
              <span class="upload-lbl">Choose a file to upload</span>
              <button class="upload-btn" onclick="document.getElementById('cert-file').click()" id="certif">Upload a file</button>
              <input id="cert-file" type="file" style="display:none" accept=".pdf,.jpg,.png" >
            </div>
              <small class="error" id="cert-file-error"></small>
          </div>

          <!-- TERMS -->
          <div class="form-group form-full">
            <div class="terms-row">
              <input type="checkbox" id="terms"/>
              <label for="terms">Terms &amp; condition <a href="#">READ MORE</a></label>
            </div>
           <small id="terms-error" class="error"></small>
          </div>
        </div>

        <button class="btn-submit" type="button">SIGN UP</button>
        <p class="login-link">Already have an account? <a href="/loginpage">LOGIN</a></p>
      </div>
    </div>
  </main>


</body>
</html>