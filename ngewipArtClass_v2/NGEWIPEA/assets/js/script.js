document.addEventListener("DOMContentLoaded", () => {
  // Toggle menu untuk mobile
  const navToggle = document.getElementById("navToggle");
  const navLinks = document.getElementById("navLinks");

  if (navToggle && navLinks) {
    navToggle.addEventListener("click", () => {
      navLinks.classList.toggle("show");
    });
  }

  // Fungsi update harga dari program.php bisa diletakkan di sini jika ingin global
  // atau biarkan di program.php jika hanya spesifik untuk halaman itu.
  // Contoh jika dipindah ke sini:
  const kelasFilterSelect = document.getElementById("kelas-filter"); // ID dari select di program.php
  if (kelasFilterSelect) {
    const updateTampilanHargaProgram = () => {
      const jenisKelasFilter = kelasFilterSelect.value;
      const semuaKartuKursus = document.querySelectorAll(".program-card"); // Selector dari program.php

      semuaKartuKursus.forEach(kartu => {
        const hargaElement = kartu.querySelector(".harga-info"); // Disesuaikan dengan class di program.php
        if (!hargaElement) return;
        
        // Ambil harga dari atribut data atau langsung dari teks jika strukturnya pasti
        // Untuk contoh ini, kita asumsikan harga sudah ditulis di HTML oleh PHP
        // dan JS hanya mengatur tampilan berdasar filter

        // Logika untuk show/hide atau mengubah teks harga berdasarkan jenisKelasFilter
        // ... (Implementasi detailnya akan bergantung pada bagaimana Anda ingin filter bekerja)
        // Contoh sederhana: menyembunyikan card jika tidak sesuai filter (butuh data-tipe di card)
        // const tipeKartu = kartu.getAttribute('data-tipe-tersedia'); // e.g., "Online,Offline"
        // if (jenisKelasFilter !== "semua" && tipeKartu && !tipeKartu.includes(jenisKelasFilter)) {
        //    kartu.style.display = 'none';
        // } else {
        //    kartu.style.display = '';
        // }
      });
    };
    kelasFilterSelect.addEventListener("change", updateTampilanHargaProgram);
    // updateTampilanHargaProgram(); // Panggil saat load jika perlu
  }

});