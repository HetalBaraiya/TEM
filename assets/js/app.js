document.addEventListener("click", (e) => {
  if (e.target.matches("[data-confirm]")) {
    if (!confirm(e.target.getAttribute("data-confirm"))) e.preventDefault();
  }
});

// Form validation
document.addEventListener("submit", (e) => {
  const form = e.target;
  if (form.matches("#employee-form") || form.matches("#task-form")) {
    let hasErrors = false;
    const inputs = form.querySelectorAll(
      "input[required], select[required], textarea[required]"
    );

    inputs.forEach((input) => {
      if (!input.value.trim()) {
        hasErrors = true;
        input.style.borderColor = "red";
        // Create error message if not exists
        if (
          !input.nextElementSibling ||
          !input.nextElementSibling.classList.contains("error-message")
        ) {
          const error = document.createElement("div");
          error.className = "error-message";
          error.style.color = "red";
          error.style.fontSize = "0.8em";
          error.textContent = "This field is required";
          input.parentNode.insertBefore(error, input.nextSibling);
        }
      } else {
        input.style.borderColor = "";
        // Remove error message if exists
        if (
          input.nextElementSibling &&
          input.nextElementSibling.classList.contains("error-message")
        ) {
          input.nextElementSibling.remove();
        }
      }
    });

    // Email validation
    const emailInput = form.querySelector('input[type="email"]');
    if (emailInput && emailInput.value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(emailInput.value)) {
        hasErrors = true;
        emailInput.style.borderColor = "red";
        if (
          !emailInput.nextElementSibling ||
          !emailInput.nextElementSibling.classList.contains("error-message")
        ) {
          const error = document.createElement("div");
          error.className = "error-message";
          error.style.color = "red";
          error.style.fontSize = "0.8em";
          error.textContent = "Please enter a valid email address";
          emailInput.parentNode.insertBefore(error, emailInput.nextSibling);
        }
      } else {
        emailInput.style.borderColor = "";
        if (
          emailInput.nextElementSibling &&
          emailInput.nextElementSibling.classList.contains("error-message")
        ) {
          emailInput.nextElementSibling.remove();
        }
      }
    }

    if (hasErrors) {
      e.preventDefault();
      return false;
    }
  }
});
