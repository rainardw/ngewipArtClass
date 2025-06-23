// ADMIN DASHBOARD CHART SIMULATION
window.addEventListener('DOMContentLoaded', () => {
  const ctx = document.getElementById('signupChart');

  if (ctx) {
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
        datasets: [{
          label: 'Pendaftaran Siswa',
          data: [30, 50, 65, 40, 80, 90],
          fill: false,
          borderColor: '#00ffcc',
          tension: 0.3,
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            labels: {
              color: '#eee'
            }
          }
        },
        scales: {
          x: {
            ticks: { color: '#ccc' },
            grid: { color: '#333' }
          },
          y: {
            ticks: { color: '#ccc' },
            grid: { color: '#333' }
          }
        }
      }
    });
  }
});
