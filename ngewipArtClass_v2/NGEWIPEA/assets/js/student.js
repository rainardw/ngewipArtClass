document.addEventListener("DOMContentLoaded", () => {
  // Greeting popup
  const user = "Siswa Hebat ✨";
  alert(`Selamat datang kembali, ${user}! Semangat belajarnya ya 💪`);

  // Auto remind jika progress di atas 60%
  const progresses = document.querySelectorAll("progress");
  progresses.forEach((bar) => {
    const value = parseInt(bar.value);
    if (value >= 60 && value < 100) {
      const parent = bar.closest(".progress-card");
      const reminder = document.createElement("p");
      reminder.textContent = "🔥 Kamu hampir selesai! Yuk lanjutkan!";
      reminder.style.color = "#00ffcc";
      reminder.style.fontWeight = "bold";
      parent.appendChild(reminder);
    }
  });

  // Live class reminder (jika ada tombol Gabung)
  const joinBtn = document.querySelector(".join-btn");
  if (joinBtn) {
    const timer = setTimeout(() => {
      alert("⏰ Jangan lupa! Live class kamu akan segera dimulai.");
    }, 3000); // 3 detik setelah load
  }
});
