<?php
session_start();
include '../database/db.php'; //

$query_biaya_kursus = "SELECT id_kelas, nama_kelas, harga_online, harga_offline, tipe_kelas_tersedia 
                       FROM kelas_seni 
                       WHERE harga_online IS NOT NULL OR harga_offline IS NOT NULL
                       ORDER BY nama_kelas ASC";
$hasil_biaya_kursus = mysqli_query($conn, $query_biaya_kursus);

$daftar_biaya = [];
if ($hasil_biaya_kursus && mysqli_num_rows($hasil_biaya_kursus) > 0) {
    while ($row = mysqli_fetch_assoc($hasil_biaya_kursus)) {
        $daftar_biaya[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biaya Kursus - ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../assets/css/style.css"> <style>
    /* Gaya dari biaya.html bisa dipertahankan atau disesuaikan */
    body { background-color: #111; color: #fff; font-family: Arial, sans-serif; margin: 0; padding: 0; }
    .container { padding: 4rem 2rem; max-width: 1000px; margin: 0 auto; }
    .container h1 { font-size: 2.5rem; margin-bottom: 2rem; color: #00ffd5; text-align: center; }
    .pricing-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
      margin-top: 2rem;
    }
    .pricing-card {
      background-color: #1c1c1c;
      border: 2px solid #00ffd5;
      border-radius: 12px;
      padding: 2rem;
      text-align: center;
      transition: transform 0.3s ease;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .pricing-card:hover { transform: translateY(-10px); }
    .pricing-card h2 { color: #00ffd5; margin-bottom: 1rem; font-size: 1.5em; }
    .pricing-card p.harga-item { font-size: 1.1rem; margin-bottom: 0.5rem; }
    .pricing-card p.harga-item strong { font-size: 1.2rem; }
    .btn-daftar-kursus {
      display: inline-block;
      padding: 0.75rem 1.5rem;
      background-color: #00ffd5;
      color: #111;
      font-weight: bold;
      border-radius: 8px;
      text-decoration: none;
      transition: background-color 0.3s ease;
      margin-top: 1.5rem;
    }
    .btn-daftar-kursus:hover { background-color: #00e6c2; }
    .no-kursus { text-align: center; font-size: 1.2em; color: #aaa; }
  </style>
</head>
<body>
  <nav class="navbar">
    <img src="../assets/images/logo.png" alt="Logo" class="logo">
    <ul class="nav-links">
      <li><a href="../guest/index.php">Beranda</a></li> <li><a href="tentang.html">Tentang</a></li> <li><a href="program.php">Program Kursus</a></li>
      <li><a href="artikel.html">Artikel</a></li> <li><a href="biaya.php" class="active">Biaya & Pendaftaran</a></li>
      <?php if (isset($_SESSION['user_id'])): ?>
            <?php
                $dashboard_link = '#';
                $role = $_SESSION['role'] ?? '';
                if ($role === 'admin') $dashboard_link = 'admin/dashboard.php'; //
                elseif ($role === 'mentor') $dashboard_link = 'mentor/dashboard.php'; //
                elseif ($role === 'member') $dashboard_link = 'student/dashboard.php'; //
            ?>
            <li><a href="<?= $dashboard_link ?>">Dashboard</a></li>
            <li><a href="../guest/logout.php">Logout</a></li> <?php else: ?>
            <li><a href="../guest/login.php">Login</a></li> <?php endif; ?>
    </ul>
  </nav>

  <main class="container">
    <h1>Biaya Kursus</h1>
    <?php if (!empty($daftar_biaya)): ?>
        <div class="pricing-grid">
            <?php foreach ($daftar_biaya as $item): ?>
                <div class="pricing-card">
                    <h2><?= htmlspecialchars($item['nama_kelas']); ?></h2>
                    <?php
                    $tipe_tersedia_biaya = explode(',', $item['tipe_kelas_tersedia'] ?? '');
                    $ada_harga_biaya = false;
                    if (in_array('Online', $tipe_tersedia_biaya) && !is_null($item['harga_online'])) {
                        echo "<p class='harga-item'>Online: <strong>Rp " . number_format($item['harga_online'], 0, ',', '.') . "</strong></p>";
                        $ada_harga_biaya = true;
                    }
                    if (in_array('Offline', $tipe_tersedia_biaya) && !is_null($item['harga_offline'])) {
                        echo "<p class='harga-item'>Offline: <strong>Rp " . number_format($item['harga_offline'], 0, ',', '.') . "</strong></p>";
                        $ada_harga_biaya = true;
                    }
                    if (!$ada_harga_biaya) {
                        echo "<p class='harga-item'>Harga belum ditentukan.</p>";
                    }
                    ?>
                    <a href="detail_kursus.php?id_kursus=<?= htmlspecialchars($item['id_kelas']); ?>" class="btn-daftar-kursus">Lihat Detail & Daftar</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-kursus">Informasi biaya kursus belum tersedia.</p>
    <?php endif; ?>
  </main>

  <footer>
    <p>&copy; <?= date("Y") ?> ngeWIP ArtClass. All rights reserved.</p>
  </footer>
</body>
</html>