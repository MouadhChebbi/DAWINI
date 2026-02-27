document.addEventListener("DOMContentLoaded", () => {
  const doctorBtn = document.querySelector('.role-btn:first-child');
  const patientBtn = document.querySelector('.role-btn:last-child');
  const signUpBtn = document.querySelector('.btn-submit');
  const certInput = document.getElementById('cert-file');

  async function submitSignUp(formData) {
    try {
      const response = await fetch("http://127.0.0.1:8000/api/register", {
        method: "POST",
        body: formData
      });

      const data = await response.json();

      if (response.ok) {
        alert("Registration successful! Welcome " + data.user.name);
        window.location.href = "dawini-login.html";
      } else {
        console.error(data);
        alert(data.message || "Registration failed.");
      }
    } catch (err) {
      console.error("Network error:", err);
    }
  }

  // Upload file name display
  certInput.addEventListener('change', function () {
    const name = this.files[0]?.name;
    if (name) {
      this.closest('.upload-row').querySelector('.upload-lbl').textContent = name;
    }
  });

  // Role toggle buttons
  doctorBtn.addEventListener('click', () => {
    doctorBtn.classList.add('active');
    patientBtn.classList.remove('active');
    document.getElementById("speciality").disabled = false;
    document.getElementById("address").disabled = false;
    document.getElementById("certif").disabled = false;
  });

  patientBtn.addEventListener('click', () => {
    patientBtn.classList.add('active');
    doctorBtn.classList.remove('active');
    document.getElementById("speciality").disabled = true;
    document.getElementById("address").disabled = true;
    document.getElementById("certif").disabled = true;
  });

  signUpBtn.addEventListener('click', function () {
    if (patientBtn.classList.contains('active')) {
      patientSignUp();
    } else if (doctorBtn.classList.contains('active')) {
      doctorSignUp();
    }
  });

  function showError(inputId, message) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(inputId + "-error");
    if (!input || !error) { console.warn("Missing element:", inputId); return; }
    input.classList.add("input-error");
    error.textContent = message;
  }

  function clearAllErrors() {
    document.querySelectorAll(".error").forEach(e => e.textContent = "");
    document.querySelectorAll(".input-error").forEach(i => i.classList.remove("input-error"));
  }

  function clearError(inputId) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(inputId + "-error");
    if (!input || !error) return;
    input.classList.remove("input-error");
    error.textContent = "";
  }

  ["name", "lastname", "mail", "password", "confirmPassword", "phone", "address", "cert-file"]
    .forEach(id => {
      const el = document.getElementById(id);
      if (el) el.addEventListener("input", () => clearError(id));
    });

  document.getElementById("terms").addEventListener("change", function () {
    if (this.checked) clearError("terms");
    else showError("terms", "You must accept terms");
  });

  document.getElementById("speciality").addEventListener("change", function () {
    if (this.selectedIndex === 0) showError("speciality", "Please select a speciality");
    else clearError("speciality");
  });

  function patientSignUp() {
    clearAllErrors();
    let valid = true;

    const alphabetic = /^[A-Za-z\s]+$/;
    const mailform = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
    const phonePattern = /^\d{2} \d{3} \d{3}$/;
    const passwordForm = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

    const name = document.getElementById("name").value;
    if (!name) { showError("name", "Name is required"); valid = false; }
    else if (!alphabetic.test(name)) { showError("name", "Name should be alphabetic"); valid = false; }

    const lastname = document.getElementById("lastname").value;
    if (!lastname) { showError("lastname", "Last Name is required"); valid = false; }
    else if (!alphabetic.test(lastname)) { showError("lastname", "Last Name should be alphabetic"); valid = false; }

    const mail = document.getElementById("mail").value;
    if (!mailform.test(mail)) { showError("mail", "Please enter a valid email"); valid = false; }

    const password = document.getElementById("password").value;
    if (!passwordForm.test(password)) { showError("password", "Password must be 8+ chars, 1 uppercase & number"); valid = false; }

    const conf = document.getElementById("confirmPassword").value;
    if (conf === "" || conf !== password) { showError("confirmPassword", "Passwords do not match"); valid = false; }

    const phone = document.getElementById("phone").value;
    if (!phonePattern.test(phone)) { showError("phone", "Phone format: XX XXX XXX"); valid = false; }

    if (!document.getElementById("terms").checked) { showError("terms", "You must accept terms"); valid = false; }

    if (!valid) return;

    const formData = new FormData();
    formData.append("name", name);
    formData.append("last_name", lastname);
    formData.append("email", mail);
    formData.append("phone_number", phone);
    formData.append("password", password);
    formData.append("password_confirmation", conf);
    formData.append("role", "patient");

    submitSignUp(formData);
  }

  function doctorSignUp() {
    clearAllErrors();
    let valid = true;

    const alphabetic = /^[A-Za-z\s]+$/;
    const mailform = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
    const phonePattern = /^\d{2} \d{3} \d{3}$/;
    const passwordForm = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

    const name = document.getElementById("name").value;
    if (!name) { showError("name", "Name is required"); valid = false; }
    else if (!alphabetic.test(name)) { showError("name", "Name should be alphabetic"); valid = false; }

    const lastname = document.getElementById("lastname").value;
    if (!lastname) { showError("lastname", "Last Name is required"); valid = false; }
    else if (!alphabetic.test(lastname)) { showError("lastname", "Last Name should be alphabetic"); valid = false; }

    const mail = document.getElementById("mail").value;
    if (!mailform.test(mail)) { showError("mail", "Please enter a valid email"); valid = false; }

    const password = document.getElementById("password").value;
    if (!passwordForm.test(password)) { showError("password", "Password must be 8+ chars, 1 uppercase & number"); valid = false; }

    const conf = document.getElementById("confirmPassword").value;
    if (conf === "" || conf !== password) { showError("confirmPassword", "Passwords do not match"); valid = false; }

    const phone = document.getElementById("phone").value;
    if (!phonePattern.test(phone)) { showError("phone", "Phone format: XX XXX XXX"); valid = false; }

    if (!document.getElementById("terms").checked) { showError("terms", "You must accept terms"); valid = false; }

    const address = document.getElementById("address").value;
    if (!address) { showError("address", "Office Address is Required"); valid = false; }

    const speciality = document.getElementById("speciality");
    if (speciality.selectedIndex === 0) { showError("speciality", "Please select a speciality"); valid = false; }

    const cert = document.getElementById("cert-file");
    if (cert.files.length === 0) { showError("cert-file", "Please upload your certification"); valid = false; }

    if (!valid) return;

    const formData = new FormData();
    formData.append("name", name);
    formData.append("last_name", lastname);
    formData.append("email", mail);
    formData.append("phone_number", phone);
    formData.append("password", password);
    formData.append("password_confirmation", conf);
    formData.append("role", "doctor");
    formData.append("speciality", speciality.value);
    formData.append("address", address);
    formData.append("file", cert.files[0]);

    submitSignUp(formData);
  }

});