import { Api } from "./api.js";

function isValidEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

function showError(errorEl, message) {
  errorEl.textContent = message;
  errorEl.hidden = false;
}

export function initLoginForm(formEl, errorEl) {
  formEl.addEventListener("submit", async (event) => {
    event.preventDefault();

    const email = formEl.email.value.trim();
    const password = formEl.password.value;

    if (!isValidEmail(email)) {
      return showError(errorEl, "Enter a valid email address.");
    }

    if (password.length < 6) {
      return showError(errorEl, "Password must be at least 6 characters.");
    }

    errorEl.hidden = true;

    try {
      const response = await Api.login(email, password);

      if (!response.ok) {
        return showError(errorEl, response.error);
      }

      window.location.href = "/seai-01-group-project/translation_client/pages/play.html";
    } catch (error) {
      showError(errorEl, error.message);
    }
  });
}

export function initSignupForm(formEl, errorEl) {
  formEl.addEventListener("submit", async (event) => {
    event.preventDefault();

    const name = formEl.name.value.trim();
    const email = formEl.email.value.trim();
    const password = formEl.password.value;
    const confirm = formEl.confirm.value;

    if (!name) {
      return showError(errorEl, "Enter your name.");
    }

    if (!isValidEmail(email)) {
      return showError(errorEl, "Enter a valid email address.");
    }

    if (password.length < 6) {
      return showError(errorEl, "Password must be at least 6 characters.");
    }

    if (password !== confirm) {
      return showError(errorEl, "Passwords don't match.");
    }

    errorEl.hidden = true;

    try {
      const response = await Api.register(name, email, password);

      if (!response.ok) {
        return showError(errorEl, response.error);
      }

      window.location.href = "/seai-01-group-project/translation_client/pages/play.html";
    } catch (error) {
      showError(errorEl, error.message);
    }
  });
}
