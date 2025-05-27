document.addEventListener("DOMContentLoaded", () => {
  const classList = [
    {
      title: "Sketching Dasar",
      time: "10:00 - 11:00 WIB",
      platform: "Zoom",
      link: "https://zoom.us/sketching"
    },
    {
      title: "Mewarnai Digital",
      time: "13:00 - 14:00 WIB",
      platform: "Google Meet",
      link: "https://meet.google.com/mewarnai"
    },
    {
      title: "Anatomi Karakter",
      time: "16:00 - 17:00 WIB",
      platform: "Zoom",
      link: "https://zoom.us/karakter"
    }
  ];

  const listContainer = document.getElementById("live-class-list");

  classList.forEach((cls) => {
    const div = document.createElement("div");
    div.className = "live-class-card";

    div.innerHTML = `
      <h3>${cls.title}</h3>
      <p><strong>Waktu:</strong> ${cls.time}</p>
      <p><strong>Platform:</strong> ${cls.platform}</p>
      <a href="${cls.link}" target="_blank" class="join-btn">Join Class</a>
    `;

    listContainer.appendChild(div);
  });
});
