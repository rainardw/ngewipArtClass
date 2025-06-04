<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'member') {
    header("Location: ../../guest/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Siswa - ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../../assets/css/student.css" />
</head>
<body>
  <div class="student-container">
    <aside class="sidebar">
      <img src="../../assets/images/logo.png" class="logo" alt="Logo" />
      <h2>Panel Siswa</h2>
      <nav>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="kursus_saya.php">Kursus Saya</a>
        <a href="upload_karya.php">Posting Karya</a>
        <a href="karya_saya.php">Karya Saya</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header>
        <h1>Hai <?= htmlspecialchars($_SESSION['username'] ?? 'Siswa'); ?>, Selamat Belajar!</h1>
        <p>Lihat progres dan kelas kamu di sini ya.</p>
      </header>
      <section class="student-progress">
        <div class="progress-card"> <h3>Sketching Dasar</h3> <progress value="0" max="100"></progress> <p>0% Selesai</p> </div>
        <div class="progress-card"> <h3>Mewarnai Digital</h3> <progress value="0" max="100"></progress> <p>0% Selesai</p> </div>
        <div class="progress-card"> <h3>Live Class: Karakter</h3> <p><strong>Jadwal Menyusul</strong></p> </div>
      </section>
      </main>
  </div>
  <script src="../assets/js/student.js"></script>
</body>
</html>