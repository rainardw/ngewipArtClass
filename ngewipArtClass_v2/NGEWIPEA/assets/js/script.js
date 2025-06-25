document.addEventListener("DOMContentLoaded", () => {
  // Toggle menu untuk mobile
  const navToggle = document.getElementById("navToggle");
  const navLinks = document.getElementById("navLinks");

  if (navToggle && navLinks) {
    navToggle.addEventListener("click", () => {
      navLinks.classList.toggle("show");
    });
  }

  // 🌗 Toggle Siang/Malam Mode
  const modeToggle = document.getElementById("modeToggle");

  if (modeToggle) {
    modeToggle.addEventListener("click", () => {
      document.body.classList.toggle("light-mode");
      const isLight = document.body.classList.contains("light-mode");
      localStorage.setItem("theme", isLight ? "light" : "dark");
    });

    // Cek preferensi tema saat halaman dimuat
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "light") {
      document.body.classList.add("light-mode");
    }
  }

  // Filter harga program (khusus halaman program.php)
  const kelasFilterSelect = document.getElementById("kelas-filter"); // ID dari select di program.php
  if (kelasFilterSelect) {
    const updateTampilanHargaProgram = () => {
      const jenisKelasFilter = kelasFilterSelect.value;
      const semuaKartuKursus = document.querySelectorAll(".program-card");

      semuaKartuKursus.forEach(kartu => {
        const tipeKartu = kartu.getAttribute("data-tipe"); // asumsikan ada data-tipe misal "online" / "offline"
        if (jenisKelasFilter === "semua" || !tipeKartu) {
          kartu.style.display = "";
        } else if (tipeKartu.includes(jenisKelasFilter)) {
          kartu.style.display = "";
        } else {
          kartu.style.display = "none";
        }
      });
    };

    kelasFilterSelect.addEventListener("change", updateTampilanHargaProgram);
    updateTampilanHargaProgram(); // Panggil sekali saat awal load
  }
});
