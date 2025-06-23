<?php
error_reporting(0); 
ini_set('display_errors', 0);
session_start();
include '../../database/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'member') {
    header("Location: ../../guest/login.php");
    exit();
}

$id_member = $_SESSION['user_id'];
$kursus_terdaftar = [];

$query = "SELECT ks.id_kelas, ks.nama_kelas, ks.deskripsi_singkat, ks.path_gambar, 
                 ks.harga_online, ks.harga_offline, 
                 pk.id_pendaftaran, pk.tipe_yang_dipilih, pk.status_pendaftaran, pk.tanggal_pendaftaran,
                 p.id_pembayaran, p.status AS status_pembayaran
          FROM pendaftaran_kursus pk
          JOIN kelas_seni ks ON pk.id_kelas = ks.id_kelas
          LEFT JOIN pembayaran p ON pk.id_pendaftaran = p.id_pendaftaran_kursus
          WHERE pk.id_member = ?
          ORDER BY pk.tanggal_pendaftaran DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_member);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $kursus_terdaftar[] = $row;
    }
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kursus Saya - ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../../assets/css/student.css" /> 
  <style>
    .course-list { display: grid; gap: 20px; margin-top: 30px; }
    .course-card { display: flex; background: #202020; border-radius: 10px; overflow: hidden; border-left: 6px solid #00ffcc; box-shadow: 0 0 10px rgba(0,255,204,0.08); transition: transform 0.3s ease; }
    .course-card:hover { transform: translateY(-5px); }
    .course-card img { width: 150px; height: 100%; object-fit: cover; }
    .course-info { padding: 20px; flex-grow: 1; }
    .course-info h3 { margin-top: 0; color: #00ffcc; }
    .course-info p { color: #ccc; margin: 5px 0; font-size:0.9em; }
    .course-info .status { font-weight:bold; }
    .course-info .status.aktif { color: #28a745; }
    .course-info .status.pending { color: #ffc107; }
    .course-info .status.selesai { color: #007bff; }
    .course-info .status.dibatalkan { color: #dc3545; }
    .start-btn, .payment-btn, .upload-btn { background: #00ffcc; color: #000; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-weight: bold; transition: background 0.3s; display:inline-block; margin-top:10px; font-size:0.9em; }
    .start-btn:hover, .payment-btn:hover, .upload-btn:hover { background: #00e6b2; }
    .no-kursus {text-align:center; color: #aaa; margin-top: 30px; font-size:1.1em;}
    form.upload-form { margin-top: 10px; }
    form.upload-form input[type="file"] { margin: 5px 0; }
  </style>
</head>
<body>
  <div class="student-container">
    <aside class="sidebar">
      <img src="../../assets/images/logo.png" class="logo" alt="Logo" />
      <h2>Panel Siswa</h2>
      <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="kursus_saya.php" class="active">Kursus Saya</a>
        <a href="upload_karya.php">Posting Karya</a>
        <a href="karya_saya.php">Karya Saya</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header>
        <h1>Kursus Saya</h1>
        <p>Berikut daftar kursus yang telah atau sedang Anda ikuti.</p>
      </header>
      <section class="course-list">
        <?php if (!empty($kursus_terdaftar)): ?>
            <?php foreach ($kursus_terdaftar as $kursus): ?>
                <div class="course-card">
                    <img src="../../<?= htmlspecialchars(!empty($kursus['path_gambar']) ? $kursus['path_gambar'] : 'assets/images/kursus/default.png'); ?>" alt="<?= htmlspecialchars($kursus['nama_kelas']); ?>" />
                    <div class="course-info">
                        <h3><?= htmlspecialchars($kursus['nama_kelas']); ?> (<?= htmlspecialchars($kursus['tipe_yang_dipilih']); ?>)</h3>
                        <p><?= htmlspecialchars($kursus['deskripsi_singkat'] ?? 'Tidak ada deskripsi singkat.'); ?></p>
                        <p>Tanggal Daftar: <?= htmlspecialchars(date('d M Y, H:i', strtotime($kursus['tanggal_pendaftaran']))); ?></p>
                        <p>Status: <span class="status <?= strtolower(str_replace(' ', '-', $kursus['status_pendaftaran'])); ?>"><?= htmlspecialchars($kursus['status_pendaftaran']); ?></span></p>

                        <?php
                          $harga_kelas = ($kursus['tipe_yang_dipilih'] === 'Online') ? $kursus['harga_online'] : $kursus['harga_offline'];
                          $sudah_upload = !empty($kursus['id_pembayaran']) && $kursus['status_pembayaran'] !== 'Ditolak';
                        ?>

                        <?php if ($kursus['status_pendaftaran'] === 'Menunggu Pembayaran'): ?>
                            <?php if (!$sudah_upload): ?>
                                <form class="upload-form" action="../upload_bukti.php" method="POST" enctype="multipart/form-data">
                                  <input type="hidden" name="id_pendaftaran" value="<?= $kursus['id_pendaftaran']; ?>">
                                  <input type="hidden" name="jumlah_bayar" value="<?= $harga_kelas; ?>">
                                  <input type="file" name="bukti" required>
                                  <button type="submit" class="upload-btn">Upload Bukti</button>
                                </form>
                            <?php else: ?>
                                <p><em>Menunggu Verifikasi Admin...</em></p>
                            <?php endif; ?>
                        <?php elseif ($kursus['status_pendaftaran'] === 'Aktif'): ?>
                            <a href="../materi_kursus.php?id_kelas=<?= htmlspecialchars($kursus['id_kelas']) ?>" class="start-btn">Mulai Belajar</a>
                        <?php elseif ($kursus['status_pendaftaran'] === 'Selesai'): ?>
                            <p class="done-status">Kursus selesai. Terima kasih telah belajar di ngeWIP!</p>
                        <?php elseif ($kursus['status_pendaftaran'] === 'Dibatalkan'): ?>
                            <p style="color: #ff6666;"><em>Pendaftaran dibatalkan karena pembayaran tidak valid.</em></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-kursus">Anda belum terdaftar di kursus manapun. <a href="../program.php">Lihat program kursus</a>.</p>
        <?php endif; ?>
      </section>
    </main>
  </div>
</body>
</html>
