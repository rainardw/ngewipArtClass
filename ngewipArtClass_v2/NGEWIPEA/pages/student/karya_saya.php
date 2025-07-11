<?php
session_start();
include '../../database/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'member') {
    header("Location: ../../guest/login.php?pesan=login_dulu_member");
    exit();
}

$id_member_login = $_SESSION['user_id'];
$karya_member = [];
$pesan_karya = '';

// Logika Hapus Karya
if (isset($_POST['hapus_karya'])) {
    $id_karya_hapus = (int)$_POST['id_karya_hapus'];

    // Ambil path file untuk dihapus dari server
    $q_file = mysqli_prepare($conn, "SELECT path_file_karya FROM karya_seni WHERE id_karya = ? AND id_member = ?");
    mysqli_stmt_bind_param($q_file, "ii", $id_karya_hapus, $id_member_login);
    mysqli_stmt_execute($q_file);
    $res_file = mysqli_stmt_get_result($q_file);
    if($r_file = mysqli_fetch_assoc($res_file)){
        $file_untuk_dihapus = $r_file['path_file_karya'];
        
        $query_delete_karya = "DELETE FROM karya_seni WHERE id_karya = ? AND id_member = ?";
        $stmt_delete = mysqli_prepare($conn, $query_delete_karya);
        mysqli_stmt_bind_param($stmt_delete, "ii", $id_karya_hapus, $id_member_login);
        if (mysqli_stmt_execute($stmt_delete)) {
            if (file_exists("../../".$file_untuk_dihapus)) {
                unlink("../../".$file_untuk_dihapus);
            }
            $pesan_karya = '<p class="pesan sukses">Karya berhasil dihapus.</p>';
        } else {
            $pesan_karya = '<p class="pesan error">Gagal menghapus karya: ' . mysqli_stmt_error($stmt_delete) . '</p>';
        }
        mysqli_stmt_close($stmt_delete);
    } else {
         $pesan_karya = '<p class="pesan error">Karya tidak ditemukan atau Anda tidak berhak menghapusnya.</p>';
    }
    mysqli_stmt_close($q_file);
}


// Ambil daftar karya milik member yang login
$query_karya = "SELECT ky.id_karya, ky.judul_karya, ky.deskripsi_karya, ky.path_file_karya, ky.tanggal_upload, ks.nama_kelas AS nama_kelas_terkait
                FROM karya_seni ky
                LEFT JOIN kelas_seni ks ON ky.id_kelas_terkait = ks.id_kelas
                WHERE ky.id_member = ?
                ORDER BY ky.tanggal_upload DESC";
$stmt_karya = mysqli_prepare($conn, $query_karya);
mysqli_stmt_bind_param($stmt_karya, "i", $id_member_login);
mysqli_stmt_execute($stmt_karya);
$hasil_karya = mysqli_stmt_get_result($stmt_karya);
if ($hasil_karya) {
    while ($row = mysqli_fetch_assoc($hasil_karya)) {
        $karya_member[] = $row;
    }
}
mysqli_stmt_close($stmt_karya);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Karya Seni Saya - ngeWIP ArtClass</title>
  <script>
  if (localStorage.getItem("theme") === "light") {
    document.documentElement.classList.add("light-mode");
  }
</script>
  <link rel="stylesheet" href="../../assets/css/student.css" />
  <style><body class="karya_saya">
  </style>
</head>
<body>
  <div class="student-container">
    <aside class="sidebar">
      <img src="../../assets/images/logo.png" class="logo" alt="Logo" />
      <h2>Panel Siswa</h2>
      <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="kursus_saya.php">Kursus Saya</a>
        <a href="upload_karya.php">Upload Karya</a>
        <a href="karya_saya.php" class="active">Karya Saya</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header>
        <button id="modeToggle" style="position: fixed; top: 10px; right: 10px; z-index: 200;">☀️/🌙</button>
        <h1>Karya Seni Saya</h1>
      </header>

      <?= $pesan_karya; ?>

      <?php if (empty($karya_member)): ?>
        <p class="no-karya">Anda belum mengupload karya seni apapun. <a href="upload_karya.php">Upload sekarang!</a></p>
      <?php else: ?>
        <div class="galeri-container">
          <?php foreach($karya_member as $karya): ?>
            <div class="karya-card">
              <img src="../../<?= htmlspecialchars($karya['path_file_karya']); ?>" alt="<?= htmlspecialchars($karya['judul_karya']); ?>">
              <div class="karya-info">
                <h4><?= htmlspecialchars($karya['judul_karya']); ?></h4>
                <p><?= nl2br(htmlspecialchars($karya['deskripsi_karya'] ?? 'Tidak ada deskripsi.')); ?></p>
                <div class="meta-info">
                  Diupload: <?= htmlspecialchars(date('d M Y, H:i', strtotime($karya['tanggal_upload']))); ?><br>
                  <?php if (!empty($karya['nama_kelas_terkait'])): ?>
                    Terkait Kelas: <strong><?= htmlspecialchars($karya['nama_kelas_terkait']); ?></strong>
                  <?php endif; ?>
                </div>
                <form action="karya_saya.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus karya ini?');">
                    <input type="hidden" name="id_karya_hapus" value="<?= $karya['id_karya']; ?>">
                    <button type="submit" name="hapus_karya">Hapus</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>
<script src="../assets/js/script.js"></script>
</body>
</html>