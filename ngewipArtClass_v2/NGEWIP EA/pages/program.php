<?php
session_start();
include '../database/db.php'; //

// Query untuk mengambil semua data dari tabel kelas_seni
$query_kursus = "SELECT ks.id_kelas, ks.nama_kelas, ks.deskripsi_singkat, ks.path_gambar, ks.harga_online, ks.harga_offline, ks.tipe_kelas_tersedia, m.username AS nama_mentor
                 FROM kelas_seni ks
                 LEFT JOIN mentor m ON ks.id_mentor = m.id_mentor
                 ORDER BY ks.nama_kelas ASC";
$hasil_kursus = mysqli_query($conn, $query_kursus);

$daftar_kursus = [];
if ($hasil_kursus && mysqli_num_rows($hasil_kursus) > 0) {
    while ($row = mysqli_fetch_assoc($hasil_kursus)) {
        $daftar_kursus[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Program Kursus - ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../assets/css/style.css"> <style>
    .program-card {
        border: 1px solid #333;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        background-color: #222; /* Warna latar card sedikit beda */
        display: flex;
        flex-direction: column;
        min-height: 350px; /* Sesuaikan tinggi */
    }
    .program-card img {
        width: 100%;
        height: 180px; /* Tinggi gambar konsisten */
        object-fit: cover;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    .program-card h3 {
        color: #00ffcc;
        margin-top: 0;
        margin-bottom: 8px;
    }
    .program-card .deskripsi {
        font-size: 0.9em;
        color: #ccc;
        flex-grow: 1; /* Membuat deskripsi mengisi ruang kosong */
        margin-bottom: 10px;
    }
    .program-card .harga-info {
        font-size: 0.95em;
        margin-bottom: 10px;
    }
    .program-card .harga-info strong {
        color: #00ffcc;
    }
    .program-card .mentor-info {
        font-size: 0.85em;
        color: #aaa;
        margin-bottom: 15px;
    }
    .btn-pilih-kursus {
        display: inline-block;
        padding: 10px 18px;
        background-color: #00ffcc;
        color: #111;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        text-align: center;
        transition: background-color 0.3s;
        margin-top: auto; /* Mendorong tombol ke bawah */
    }
    .btn-pilih-kursus:hover {
        background-color: #00e6b2;
    }
    #program-kursus h2 {
        text-align: center;
        color: #00ffcc; /* Konsisten dengan style lain */
        margin-bottom: 30px;
    }
    .program-container { /* Dari style.css, pastikan ada */
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Grid responsif */
        gap: 20px;
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <img src="../assets/images/logo.png" alt="Logo" class="logo">
    <ul class="nav-links">
      <li><a href="../guest/index.php">Beranda</a></li> <li><a href="tentang.html">Tentang</a></li> <li><a href="program.php" class="active">Program Kursus</a></li>
      <li><a href="artikel.html">Artikel</a></li> <li><a href="biaya.php">Biaya & Pendaftaran</a></li> <?php if (isset($_SESSION['user_id'])): ?>
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

 <section id="program-kursus">
  <h2>Program Kursus Seni Kami</h2>

  <div class="program-container">
    <?php if (!empty($daftar_kursus)): ?>
        <?php foreach ($daftar_kursus as $kursus): ?>
            <div class="program-card">
              <img src="../<?= htmlspecialchars(!empty($kursus['path_gambar']) ? $kursus['path_gambar'] : 'assets/images/kursus/default.png'); ?>" alt="<?= htmlspecialchars($kursus['nama_kelas']); ?>">
              <h3><?= htmlspecialchars($kursus['nama_kelas']); ?></h3>
              <p class="deskripsi"><?= htmlspecialchars($kursus['deskripsi_singkat'] ?? 'Deskripsi belum tersedia.'); ?></p>
              <?php if(!empty($kursus['nama_mentor'])): ?>
                <p class="mentor-info">Mentor: <?= htmlspecialchars($kursus['nama_mentor']); ?></p>
              <?php endif; ?>
              <div class="harga-info">
                <?php
                $tipe_tersedia = explode(',', $kursus['tipe_kelas_tersedia'] ?? '');
                if (in_array('Online', $tipe_tersedia) && !is_null($kursus['harga_online'])) {
                    echo "Online: <strong>Rp " . number_format($kursus['harga_online'], 0, ',', '.') . "</strong><br>";
                }
                if (in_array('Offline', $tipe_tersedia) && !is_null($kursus['harga_offline'])) {
                    echo "Offline: <strong>Rp " . number_format($kursus['harga_offline'], 0, ',', '.') . "</strong>";
                }
                if (empty(array_filter($tipe_tersedia)) || (is_null($kursus['harga_online']) && is_null($kursus['harga_offline']))) {
                    echo "Harga belum tersedia.";
                }
                ?>
              </div>
              <a href="detail_kursus.php?id_kursus=<?= htmlspecialchars($kursus['id_kelas']); ?>" class="btn-pilih-kursus">Lihat Detail & Daftar</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center; width:100%;">Belum ada program kursus yang tersedia saat ini.</p>
    <?php endif; ?>
  </div>
</section>

<footer>
    <p>&copy; <?= date("Y") ?> ngeWIP ArtClass BANZAIII</p>
</footer>

</body>
</html>