// Wait for the DOM to be fully loaded before executing
document.addEventListener("DOMContentLoaded", () => {
  // DOM element selections for role buttons, submit button, and file input
  const doctorBtn = document.querySelector('.role-btn:first-child');
  const patientBtn = document.querySelector('.role-btn:last-child');
  const signUpBtn = document.querySelector('.btn-submit');
  const certInput = document.getElementById('cert-file');

  /**
   * Async function to handle the signup API request
   * @param {FormData} formData - The form data to be sent to the server
   */
  async function submitSignUp(formData) {
    try {
      // Send POST request to registration endpoint
      const response = await fetch("http://127.0.0.1:8000/api/register", {
        method: "POST",
        body: formData
      });

      const data = await response.json();

      // Handle successful registration
      if (response.ok) {
        alert("Registration successful! Welcome " + data.user.name);
        window.location.href = "/loginpage"; // Redirect to success page
      } else {
        // Handle server-side errors
        console.error(data);
        alert(data.message || "Registration failed.");
      }
    } catch (err) {
      // Handle network errors
      console.error("Network error:", err);
    }
  }

  // On page load: Patient is active by default → disable doctor-only fields
  document.getElementById("speciality").disabled = true;
  document.getElementById("address").disabled = true;
  document.getElementById("certif").disabled = true;

  // Event listener for file input - displays the selected filename
  certInput.addEventListener('change', function () {
    const name = this.files[0]?.name; // Optional chaining in case no file selected
    if (name) {
      // Update the label text with the filename
      this.closest('.upload-row').querySelector('.upload-lbl').textContent = name;
    }
  });

  // Event listener for doctor role button - enables doctor-specific fields
  doctorBtn.addEventListener('click', () => {
    doctorBtn.classList.add('active');
    patientBtn.classList.remove('active');
    // Enable doctor-only form fields
    document.getElementById("speciality").disabled = false;
    document.getElementById("address").disabled = false;
    document.getElementById("certif").disabled = false;
  });

  // Event listener for patient role button - disables doctor-specific fields
  patientBtn.addEventListener('click', () => {
    patientBtn.classList.add('active');
    doctorBtn.classList.remove('active');
    // Disable doctor-only form fields
    document.getElementById("speciality").disabled = true;
    document.getElementById("address").disabled = true;
    document.getElementById("certif").disabled = true;
  });

  // Main signup button click handler - determines which role is active
  signUpBtn.addEventListener('click', function () {
    if (patientBtn.classList.contains('active')) {
      patientSignUp(); // Handle patient registration
    } else if (doctorBtn.classList.contains('active')) {
      doctorSignUp(); // Handle doctor registration
    }
  });

  /**
   * Displays error message for a specific input field
   * @param {string} inputId - The ID of the input element
   * @param {string} message - The error message to display
   */
  function showError(inputId, message) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(inputId + "-error");
    if (!input || !error) { console.warn("Missing element:", inputId); return; }
    input.classList.add("input-error"); // Add error styling
    error.textContent = message; // Display error message
  }

  // Clears all error messages and error styling from the form
  function clearAllErrors() {
    document.querySelectorAll(".error").forEach(e => e.textContent = "");
    document.querySelectorAll(".input-error").forEach(i => i.classList.remove("input-error"));
  }

  /**
   * Clears error for a specific input field
   * @param {string} inputId - The ID of the input element
   */
  function clearError(inputId) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(inputId + "-error");
    if (!input || !error) return;
    input.classList.remove("input-error");
    error.textContent = "";
  }

  // Add input event listeners to clear errors when user starts typing
  ["name", "lastname", "mail", "password", "confirmPassword", "phone", "address", "cert-file"]
    .forEach(id => {
      const el = document.getElementById(id);
      if (el) el.addEventListener("input", () => clearError(id));
    });

  // Terms checkbox validation
  document.getElementById("terms").addEventListener("change", function () {
    if (this.checked) clearError("terms");
    else showError("terms", "You must accept terms");
  });

  // Speciality dropdown validation
  document.getElementById("speciality").addEventListener("change", function () {
    if (this.selectedIndex === 0) showError("speciality", "Please select a speciality");
    else clearError("speciality");
  });

  /**
   * Validates and processes patient signup form
   * Collects form data and submits to server if validation passes
   */
  function patientSignUp() {
    clearAllErrors();
    let valid = true;

    // Validation patterns
    const alphabetic = /^[A-Za-z\s]+$/; // Only letters and spaces
    const mailform = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/; // Basic email format
    const phonePattern = /^\d{2} \d{3} \d{3}$/; // Phone format: XX XXX XXX
    const passwordForm = /^(?=.*[A-Z])(?=.*\d).{8,}$/; // Min 8 chars, 1 uppercase, 1 number

    // Validate name field
    const name = document.getElementById("name").value;
    if (!name) { showError("name", "Name is required"); valid = false; }
    else if (!alphabetic.test(name)) { showError("name", "Name should be alphabetic"); valid = false; }

    // Validate lastname field
    const lastname = document.getElementById("lastname").value;
    if (!lastname) { showError("lastname", "Last Name is required"); valid = false; }
    else if (!alphabetic.test(lastname)) { showError("lastname", "Last Name should be alphabetic"); valid = false; }

    // Validate email field
    const mail = document.getElementById("mail").value;
    if (!mailform.test(mail)) { showError("mail", "Please enter a valid email"); valid = false; }

    // Validate password field
    const password = document.getElementById("password").value;
    if (!passwordForm.test(password)) { showError("password", "Password must be 8+ chars, 1 uppercase & number"); valid = false; }

    // Validate password confirmation
    const conf = document.getElementById("confirmPassword").value;
    if (conf === "" || conf !== password) { showError("confirmPassword", "Passwords do not match"); valid = false; }

    // Validate phone number
    const phone = document.getElementById("phone").value;
    if (!phonePattern.test(phone)) { showError("phone", "Phone format: XX XXX XXX"); valid = false; }

    // Validate terms acceptance
    if (!document.getElementById("terms").checked) { showError("terms", "You must accept terms"); valid = false; }

    // If validation fails, stop execution
    if (!valid) return;

    // Prepare form data for submission
    const formData = new FormData();
    formData.append("name", name);
    formData.append("last_name", lastname);
    formData.append("email", mail);
    formData.append("phone_number", phone);
    formData.append("password", password);
    formData.append("password_confirmation", conf);
    formData.append("role", "patient");

    // Submit the form data
    submitSignUp(formData);
  }

  /**
   * Validates and processes doctor signup form
   * Includes additional doctor-specific fields
   * Collects form data and submits to server if validation passes
   */
  function doctorSignUp() {
    clearAllErrors();
    let valid = true;

    // Validation patterns (same as patient)
    const alphabetic = /^[A-Za-z\s]+$/;
    const mailform = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
    const phonePattern = /^\d{2} \d{3} \d{3}$/;
    const passwordForm = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

    // Validate name field
    const name = document.getElementById("name").value;
    if (!name) { showError("name", "Name is required"); valid = false; }
    else if (!alphabetic.test(name)) { showError("name", "Name should be alphabetic"); valid = false; }

    // Validate lastname field
    const lastname = document.getElementById("lastname").value;
    if (!lastname) { showError("lastname", "Last Name is required"); valid = false; }
    else if (!alphabetic.test(lastname)) { showError("lastname", "Last Name should be alphabetic"); valid = false; }

    // Validate email field
    const mail = document.getElementById("mail").value;
    if (!mailform.test(mail)) { showError("mail", "Please enter a valid email"); valid = false; }

    // Validate password field
    const password = document.getElementById("password").value;
    if (!passwordForm.test(password)) { showError("password", "Password must be 8+ chars, 1 uppercase & number"); valid = false; }

    // Validate password confirmation
    const conf = document.getElementById("confirmPassword").value;
    if (conf === "" || conf !== password) { showError("confirmPassword", "Passwords do not match"); valid = false; }

    // Validate phone number
    const phone = document.getElementById("phone").value;
    if (!phonePattern.test(phone)) { showError("phone", "Phone format: XX XXX XXX"); valid = false; }

    // Validate terms acceptance
    if (!document.getElementById("terms").checked) { showError("terms", "You must accept terms"); valid = false; }

    // Doctor-specific validations
    const address = document.getElementById("address").value;
    if (!address) { showError("address", "Office Address is Required"); valid = false; }

    const speciality = document.getElementById("speciality");
    if (speciality.selectedIndex === 0) { showError("speciality", "Please select a speciality"); valid = false; }

    const cert = document.getElementById("cert-file");
    if (cert.files.length === 0) { showError("cert-file", "Please upload your certification"); valid = false; }

    // If validation fails, stop execution
    if (!valid) return;

    // Prepare form data for submission (including doctor-specific fields)
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

    // Submit the form data
    submitSignUp(formData);
  }

});