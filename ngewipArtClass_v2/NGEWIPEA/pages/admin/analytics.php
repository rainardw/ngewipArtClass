<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../guest/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Analytics - Admin Panel</title>
  <link rel="stylesheet" href="../../assets/css/admin.css" />
</head>
<body>
  <div class="admin-container">
    <aside class="sidebar">
      <img src="../../assets/images/logo.png" class="logo" alt="Logo" />
      <h2>Admin Panel</h2>
      <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="analytics.php" class="active">Analytics</a>
        <a href="kelola_kursus.php" >Kelola Kursus</a>
        <a href="verifikasi_pembayaran.php">verifikasi_pembayaran</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header>
        <h1>Analytics</h1>
        <p>Statistik performa dan pertumbuhan pengguna platform.</p>
      </header>
      <section class="chart-section">
        <div class="fake-chart">
          <h3>Grafik Pendaftaran Siswa (Contoh)</h3>
          <canvas id="signupChart" width="400" height="200"></canvas>
        </div>
      </section>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../../assets/js/admin.js"></script>
</body>
</html>
