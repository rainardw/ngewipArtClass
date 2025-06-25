<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'mentor') {
    header("Location: ../../guest/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Live Class - Mentor ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../../assets/css/mentor.css" />
</head>
<body>
  <div class="mentor-container">
    <aside class="sidebar">
      <img src="../../assets/images/logo.png" class="logo" alt="Logo" />
      <h2>Mentor Panel</h2>
      <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="live-class.php" class="active">Live Class</a>
        <a href="lihat_murid.php">Lihat Murid</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header>
        <button id="modeToggle" style="position: fixed; top: 10px; right: 10px; z-index: 200;">☀️/🌙</button>
        <h1>Live Class Hari Ini</h1>
        <p>Berikut adalah jadwal kelas live kamu hari ini (data dari JavaScript).</p>
      </header>
      <section id="live-class-list" class="live-class-list">
        </section>
    </main>
  </div>
  <script src="../assets/js/script.js"></script>
  <script src="../../assets/js/mentor.js"></script> 
  </body>
</html>