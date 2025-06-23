document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");

  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const email = document.getElementById("loginEmail").value.trim();
      const password = document.getElementById("loginPassword").value.trim();

      if (!email || !password) {
        alert("Email dan password wajib diisi!");
        return;
      }

      // Simulasi: akan diganti AJAX/backend nanti
      if (email === "admin@example.com" && password === "admin123") {
        alert("Selamat datang Admin!");
        window.location.href = "admin/dashboard.html";
      } else if (email.includes("mentor")) {
        window.location.href = "mentor/dashboard.html";
      } else {
        window.location.href = "student/dashboard.html";
      }
    });
  }
});
