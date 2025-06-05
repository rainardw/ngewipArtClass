<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../guest/login.php");
    exit();
}
include '../../database/db.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../../assets/css/admin.css" />
  <style>
    .chart-container {
      max-width: 600px;
      margin: 20px auto;
      background: #1f1f1f;
      padding: 20px;
      border-radius: 10px;
    }
  </style>
</head>
<body>
  <div class="admin-container">
    <aside class="sidebar">
      <img src="../../assets/images/logo.png" class="logo" alt="Logo" />
      <h2>Admin Panel</h2>
      <nav>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="analytics.php">Analytics</a>
        <a href="kelola_kursus.php">Kelola Kursus</a>
        <a href="verifikasi_pembayaran.php">Verifikasi Pembayaran</a>
        <a href="kelola_member.php">Kelola Member</a>
        <a href="kelola_mentor.php">Kelola mentor</a>
        <a href="kelola_karya_seni.php">Kelola Karya Seni Member</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header>
        <h1>Dashboard Admin</h1>
        <p>Selamat datang, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>! Ini adalah ringkasan sistem ngeWIP ArtClass.</p>
      </header>

      <section class="stats">
        <a href="kelola_member.php" class="stat-card-link">
          <div class="stat-card">
            <?php
              $result_murid = mysqli_query($conn, "SELECT COUNT(*) AS total_murid FROM member");
              $total_murid = ($result_murid) ? mysqli_fetch_assoc($result_murid)['total_murid'] : 0;
            ?>
            <h3><?= $total_murid ?></h3>
            <p>Jumlah Murid</p>
          </div>
        </a>

        <a href="kelola_mentor.php" class="stat-card-link">
          <div class="stat-card">
            <?php
              $result_mentor = mysqli_query($conn, "SELECT COUNT(*) AS total_mentor FROM mentor");
              $total_mentor = ($result_mentor) ? mysqli_fetch_assoc($result_mentor)['total_mentor'] : 0;
            ?>
            <h3><?= $total_mentor ?></h3>
            <p>Mentor Aktif</p>
          </div>
        </a>

        <a href="kelola_kursus.php" class="stat-card-link">
          <div class="stat-card">
            <?php
              $result_kelas = mysqli_query($conn, "SELECT COUNT(*) AS total_kelas FROM kelas_seni");
              $total_kelas = ($result_kelas) ? mysqli_fetch_assoc($result_kelas)['total_kelas'] : 0;
            ?>
            <h3><?= $total_kelas ?></h3>
            <p>Kelas Tersedia</p>
          </div>
        </a>
      </section>

      <div class="chart-container">
        <canvas id="signupChart" width="600" height="300"></canvas>
      </div>
    </main>
  </div>
</body>
</html>