<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Signup Form</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      max-width: 500px;
      margin: 40px auto;
      padding: 20px;
      background: #f4f4f4;
    }
    h2 { text-align: center; color: #333; }
    label { display: block; margin-top: 12px; font-weight: bold; }
    input, select {
      width: 100%;
      padding: 8px;
      margin-top: 4px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }
    button {
      margin-top: 20px;
      width: 100%;
      padding: 10px;
      background-color: #ff6c37;
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
    }
    button:hover { background-color: #e55a28; }
    #response {
      margin-top: 20px;
      padding: 12px;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 4px;
      white-space: pre-wrap;
      word-break: break-all;
    }
  </style>
</head>
<body>
  <h2>Signup</h2>
  <form id="signupForm">
    <label>First Name</label>
    <input type="text" id="name" value="Chebbi" required>

    <label>Last Name</label>
    <input type="text" id="last_name" value="Mouadh" required>

    <label>Email</label>
    <input type="email" id="email" value="" placeholder="auto-generated on submit" required>

    <label>Password</label>
    <input type="password" id="password" value="password123" required>

    <label>Phone Number</label>
    <input type="text" id="phone_number" value="+21612345678" required>

    <label>Speciality</label>
    <input type="text" id="speciality" value="Cardiologie" required>

    <label>Gender</label>
    <select id="gender">
      <option value="male" selected>Male</option>
      <option value="female">Female</option>
    </select>

    <label>Role</label>
    <select id="role">
      <option value="doctor" selected>Doctor</option>
      <option value="patient">Patient</option>
    </select>

    <button type="submit">Register</button>
  </form>

  <div id="response" style="display:none;"></div>

  <script>
    // Auto-generate a unique email using timestamp
    document.getElementById('email').value = `mouadh_new_${Date.now()}@example.com`;

    document.getElementById('signupForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const payload = {
        name: document.getElementById('name').value,
        last_name: document.getElementById('last_name').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        phone_number: document.getElementById('phone_number').value,
        speciality: document.getElementById('speciality').value,
        gender: document.getElementById('gender').value,
        role: document.getElementById('role').value
      };

      const responseBox = document.getElementById('response');
      responseBox.style.display = 'block';
      responseBox.textContent = 'Sending...';

      try {
        const res = await fetch('http://127.0.0.1:8000/api/register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const data = await res.json();
        responseBox.textContent = `Status: ${res.status}\n\n${JSON.stringify(data, null, 2)}`;
      } catch (err) {
        responseBox.textContent = `Error: ${err.message}`;
      }
    });
  </script>
</body>
</html>