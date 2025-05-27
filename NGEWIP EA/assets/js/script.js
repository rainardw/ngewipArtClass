document.addEventListener("DOMContentLoaded", () => {
  const cards = document.querySelectorAll('.card');
  cards.forEach((card, i) => {
    card.style.opacity = 0;
    setTimeout(() => {
      card.style.transition = 'opacity 0.8s ease';
      card.style.opacity = 1;
    }, 300 * i);
  });
    // Toggle menu untuk mobile
  const navToggle = document.getElementById("navToggle");
  const navLinks = document.getElementById("navLinks");

  navToggle.addEventListener("click", () => {
  navLinks.classList.toggle("show");
    
  function updateHarga() {
  const jenisKelas = document.getElementById("kelas").value;
  const hargaList = document.querySelectorAll(".harga");

  hargaList.forEach(hargaEl => {
    const online = hargaEl.getAttribute("data-online");
    const offline = hargaEl.getAttribute("data-offline");
    const targetHarga = jenisKelas === "online" ? online : offline;

    hargaEl.querySelector("span").textContent = targetHarga;
  });
}

});

});
