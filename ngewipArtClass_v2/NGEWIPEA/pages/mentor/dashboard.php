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
  <title>Dashboard Mentor - ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../../assets/css/mentor.css" />
</head>
<body>
  <div class="mentor-container">
    <aside class="sidebar">
      <img src="../../assets/images/logo.png" class="logo" alt="Logo" />
      <h2>Mentor Panel</h2>
      <nav>
  <a href="dashboard.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
  <a href="live-class.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'live-class.php') ? 'active' : ''; ?>">Live Class</a>
  <a href="lihat_murid.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'lihat_murid.php') ? 'active' : ''; ?>">Lihat Murid</a>
  <a href="kelola_materi_saya.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'kelola_materi_saya.php') ? 'active' : ''; ?>">Kelola Materi Saya</a> <!-TAMBAHKAN INI -->
  <a href="../../pages/galery_karya.php">Galery Karya</a>
  <a href="../../guest/index.php">Kembali ke Beranda</a>
  <a href="../../guest/logout.php">Logout</a>
</nav>
    </aside>
    <main class="main-content">
      <header>
        <h1>Selamat Datang, Mentor <?= htmlspecialchars($_SESSION['username'] ?? ''); ?>!</h1>
        <p>Berikut ringkasan aktivitas dan kelas kamu.</p>
      </header>
      <section class="mentor-stats">
        <?php
        include '../../database/db.php';
        $id_mentor_login = $_SESSION['user_id'];

        // Jumlah kelas yang diampu
        $query_kelas_ampu = "SELECT COUNT(*) AS total_kelas_ampu FROM kelas_seni WHERE id_mentor = ?";
        $stmt_kelas_ampu = mysqli_prepare($conn, $query_kelas_ampu);
        mysqli_stmt_bind_param($stmt_kelas_ampu, "s", $id_mentor_login);
        mysqli_stmt_execute($stmt_kelas_ampu);
        $result_kelas_ampu = mysqli_stmt_get_result($stmt_kelas_ampu);
        $total_kelas_ampu = ($result_kelas_ampu) ? mysqli_fetch_assoc($result_kelas_ampu)['total_kelas_ampu'] : 0;
        mysqli_stmt_close($stmt_kelas_ampu);

        // Jumlah siswa aktif di kelas yang diampu mentor
        $query_siswa_aktif = "SELECT COUNT(DISTINCT pk.id_member) AS total_siswa_aktif 
                             FROM pendaftaran_kursus pk
                             JOIN kelas_seni ks ON pk.id_kelas = ks.id_kelas
                             WHERE ks.id_mentor = ? AND pk.status_pendaftaran = 'Aktif'";
        $stmt_siswa_aktif = mysqli_prepare($conn, $query_siswa_aktif);
        mysqli_stmt_bind_param($stmt_siswa_aktif, "s", $id_mentor_login);
        mysqli_stmt_execute($stmt_siswa_aktif);
        $result_siswa_aktif = mysqli_stmt_get_result($stmt_siswa_aktif);
        $total_siswa_aktif = ($result_siswa_aktif) ? mysqli_fetch_assoc($result_siswa_aktif)['total_siswa_aktif'] : 0;
        mysqli_stmt_close($stmt_siswa_aktif);
        ?>
        <div class="stat-card">
          <h3><?= $total_kelas_ampu ?></h3>
          <p>Kelas yang Diampu</p>
        </div>
        <div class="stat-card">
          <h3><?= $total_siswa_aktif ?></h3>
          <p>Siswa Aktif</p>
        </div>
        <div class="stat-card">
          <h3>0</h3> <p>Live Class Hari Ini</p>
        </div>
      </section>
    </main>
  </div>
</body>
</html>