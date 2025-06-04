<?php
session_start();
include '../../database/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../guest/login.php");
    exit();
}
$pesan = '';

// Logika Hapus Karya Seni
if (isset($_POST['hapus_karya_admin'])) {
    $id_karya_hapus_admin = (int)$_POST['id_karya_hapus_admin'];

    // Ambil path file untuk dihapus dari server
    $q_file_admin = mysqli_prepare($conn, "SELECT path_file_karya FROM karya_seni WHERE id_karya = ?");
    mysqli_stmt_bind_param($q_file_admin, "i", $id_karya_hapus_admin);
    mysqli_stmt_execute($q_file_admin);
    $res_file_admin = mysqli_stmt_get_result($q_file_admin);

    if($r_file_admin = mysqli_fetch_assoc($res_file_admin)){
        $file_untuk_dihapus_admin = $r_file_admin['path_file_karya'];
        
        $query_delete_karya_admin = "DELETE FROM karya_seni WHERE id_karya = ?";
        $stmt_delete_admin = mysqli_prepare($conn, $query_delete_karya_admin);
        mysqli_stmt_bind_param($stmt_delete_admin, "i", $id_karya_hapus_admin);
        if (mysqli_stmt_execute($stmt_delete_admin)) {
            if (file_exists("../../".$file_untuk_dihapus_admin)) {
                unlink("../../".$file_untuk_dihapus_admin);
            }
            $pesan = '<p class="pesan sukses">Karya seni berhasil dihapus.</p>';
        } else {
            $pesan = '<p class="pesan error">Gagal menghapus karya seni: ' . mysqli_stmt_error($stmt_delete_admin) . '</p>';
        }
        mysqli_stmt_close($stmt_delete_admin);
    } else {
         $pesan = '<p class="pesan error">Karya seni tidak ditemukan.</p>';
    }
    mysqli_stmt_close($q_file_admin);
}


// Ambil semua karya seni
$daftar_semua_karya = [];
$query_karya_all = "SELECT ky.id_karya, ky.judul_karya, ky.path_file_karya, ky.tanggal_upload, 
                           m.username AS nama_member, ks.nama_kelas AS nama_kelas_terkait
                    FROM karya_seni ky
                    JOIN member m ON ky.id_member = m.id_member
                    LEFT JOIN kelas_seni ks ON ky.id_kelas_terkait = ks.id_kelas
                    ORDER BY ky.tanggal_upload DESC";
$hasil_karya_all = mysqli_query($conn, $query_karya_all);
if ($hasil_karya_all) {
    while($row = mysqli_fetch_assoc($hasil_karya_all)){
        $daftar_semua_karya[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kelola Karya Seni - Admin ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../../assets/css/admin.css" />
  <style>
    /* Style mirip galeri */
    .galeri-admin-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; padding: 20px 0; }
    .karya-admin-card { background-color: #2b2b2b; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.2); display: flex; flex-direction: column;}
    .karya-admin-card img { width: 100%; height: 200px; object-fit: cover; cursor:pointer;}
    .karya-admin-info { padding: 15px; flex-grow:1; }
    .karya-admin-info h4 { margin-top: 0; margin-bottom: 5px; color: #00ffcc; font-size: 1.1em; }
    .karya-admin-info p { font-size: 0.9em; color: #ccc; margin-bottom: 3px; }
    .karya-admin-info .meta-info { font-size: 0.8em; color: #888; margin-top:auto; padding-top:10px;}
    .karya-admin-info .meta-info strong { color: #aaa;}
    .karya-admin-card form button { background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 0.8em; margin-top: 10px;}
    .karya-admin-card form button:hover { background-color: #c82333;}
    .pesan { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align:center; }
    .pesan.sukses { background-color: #28a74533; color: #a3ffb8; border: 1px solid #28a74588; }
    .pesan.error { background-color: #dc354533; color: #ffacb3; border: 1px solid #dc354588; }
    .no-karya {text-align: center; color:#aaa; margin-top:20px;}
    /* Modal */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.9); }
    .modal-content { margin: auto; display: block; width: auto; max-width: 85%; max-height: 85%; position:absolute; top:50%; left:50%; transform: translate(-50%, -50%);}
    .close-modal { position: absolute; top: 20px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; }
    .close-modal:hover, .close-modal:focus { color: #bbb; text-decoration: none; cursor: pointer; }
  </style>
</head>
<body>
  <div class="admin-container">
    <aside class="sidebar">
      <img src="../../assets/images/logo.png" class="logo" alt="Logo" />
      <h2>Admin Panel</h2>
      <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="analytics.php">Analytics</a>
        <a href="kelola_kursus.php">Kelola Kursus</a>
        <a href="verifikasi_pembayaran.php">Verifikasi Pembayaran</a>
        <a href="kelola_member.php">Kelola Member</a>
        <a href="kelola_mentor.php">Kelola Mentor</a>
        <a href="kelola_karya_seni.php" class="active">Kelola Karya Seni</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header><h1>Kelola Semua Karya Seni</h1></header>
      <?= $pesan; ?>
      <?php if(empty($daftar_semua_karya)): ?>
        <p class="no-karya">Belum ada karya seni yang diupload oleh member.</p>
      <?php else: ?>
        <div class="galeri-admin-container">
          <?php foreach($daftar_semua_karya as $karya_item): ?>
          <div class="karya-admin-card">
            <img src="../../<?= htmlspecialchars($karya_item['path_file_karya']); ?>" alt="<?= htmlspecialchars($karya_item['judul_karya']); ?>" onclick="bukaModalGambarAdmin(this.src)">
            <div class="karya-admin-info">
              <h4><?= htmlspecialchars($karya_item['judul_karya']); ?></h4>
              <p class="meta-info">Oleh: <strong><?= htmlspecialchars($karya_item['nama_member']); ?></strong></p>
              <p class="meta-info">Upload: <?= htmlspecialchars(date('d M Y', strtotime($karya_item['tanggal_upload']))); ?></p>
              <?php if(!empty($karya_item['nama_kelas_terkait'])): ?>
                <p class="meta-info">Kelas: <strong><?= htmlspecialchars($karya_item['nama_kelas_terkait']); ?></strong></p>
              <?php endif; ?>
              <form action="kelola_karya_seni.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus karya seni ini? Ini tidak bisa dibatalkan.');">
                <input type="hidden" name="id_karya_hapus_admin" value="<?= $karya_item['id_karya']; ?>">
                <button type="submit" name="hapus_karya_admin">Hapus Karya Ini</button>
              </form>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>

  <div id="gambarModalAdmin" class="modal">
    <span class="close-modal" onclick="tutupModalGambarAdmin()">&times;</span>
    <img class="modal-content" id="gambarModalKontenAdmin">
  </div>
<script>
    var modalAdmin = document.getElementById("gambarModalAdmin");
    var modalImgAdmin = document.getElementById("gambarModalKontenAdmin");
    function bukaModalGambarAdmin(src) {
        modalAdmin.style.display = "block";
        modalImgAdmin.src = src;
    }
    function tutupModalGambarAdmin() {
        modalAdmin.style.display = "none";
    }
    window.onclick = function(event) {
        if (event.target == modalAdmin) {
            tutupModalGambarAdmin();
        }
    }
</script>
</body>
</html>