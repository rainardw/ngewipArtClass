document.addEventListener("DOMContentLoaded", () => {
  const classList = [
    {
      title: "Sketching Dasar Minggu Ini",
      time: "Setiap Rabu, 10:00 - 11:00 WIB",
      platform: "Zoom",
      link: "https://zoom.us/j/contohlink1"
    },
    {
      title: "Sesi Tanya Jawab Mewarnai Digital",
      time: "Setiap Jumat, 13:00 - 14:00 WIB",
      platform: "Google Meet",
      link: "https://meet.google.com/contohlink2"
    }
    // Tambahkan jadwal live class lainnya jika perlu
  ];

  const listContainer = document.getElementById("live-class-list");

  if (listContainer) { // Pastikan elemen ada sebelum mencoba mengisinya
    if (classList.length > 0) {
        classList.forEach((cls) => {
            const div = document.createElement("div");
            div.className = "live-class-card"; // Pastikan class ini ada di mentor.css

            div.innerHTML = `
            <h3>${cls.title}</h3>
            <p><strong>Waktu:</strong> ${cls.time}</p>
            <p><strong>Platform:</strong> ${cls.platform}</p>
            <a href="${cls.link}" target="_blank" class="join-btn">Join Class</a>
            `;
            listContainer.appendChild(div);
        });
    } else {
        listContainer.innerHTML = "<p>Tidak ada jadwal live class hari ini.</p>";
    }
  }
});