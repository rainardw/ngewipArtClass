document.addEventListener("DOMContentLoaded", () => {
  // 🔄 Toggle menu mobile
  const navToggle = document.getElementById("navToggle");
  const navLinks = document.getElementById("navLinks");

  if (navToggle && navLinks) {
    navToggle.addEventListener("click", () => {
      navLinks.classList.toggle("show");
    });
  }

  // 🌞🌚 Toggle Mode — PAKAI <html> bukan <body>
  const modeToggle = document.getElementById("modeToggle");
  const html = document.documentElement;

  const applyTheme = () => {
    const saved = localStorage.getItem("theme");
    if (saved === "light") {
      html.classList.add("light-mode");
    } else {
      html.classList.remove("light-mode");
    }
  };

  applyTheme(); // apply saat load

  if (modeToggle) {
    modeToggle.addEventListener("click", () => {
      const isLight = html.classList.toggle("light-mode");
      localStorage.setItem("theme", isLight ? "light" : "dark");
    });
  }

  // 🎯 Filter program (khusus program.php)
  const kelasFilterSelect = document.getElementById("kelas-filter");
  if (kelasFilterSelect) {
    const updateTampilanHargaProgram = () => {
      const jenisKelasFilter = kelasFilterSelect.value;
      const semuaKartu = document.querySelectorAll(".program-card");

      semuaKartu.forEach(kartu => {
        const tipe = kartu.getAttribute("data-tipe");
        if (jenisKelasFilter === "semua" || !tipe) {
          kartu.style.display = "";
        } else if (tipe.includes(jenisKelasFilter)) {
          kartu.style.display = "";
        } else {
          kartu.style.display = "none";
        }
      });
    };

    kelasFilterSelect.addEventListener("change", updateTampilanHargaProgram);
    updateTampilanHargaProgram();
  }
});
