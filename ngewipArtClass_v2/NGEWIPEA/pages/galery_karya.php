<?php
session_start();
include '../database/db.php';

// Hanya user yang login bisa lihat galeri
if (!isset($_SESSION['user_id'])) {
    header("Location: ../guest/login.php?pesan=login_dulu_galeri");
    exit();
}

$semua_karya = [];
$query_semua_karya = "SELECT ky.id_karya, ky.judul_karya, ky.path_file_karya, ky.tanggal_upload, 
                             m.username AS nama_uploader, ks.nama_kelas AS nama_kelas_terkait
                      FROM karya_seni ky
                      JOIN member m ON ky.id_member = m.id_member
                      LEFT JOIN kelas_seni ks ON ky.id_kelas_terkait = ks.id_kelas
                      ORDER BY ky.tanggal_upload DESC";
$hasil_semua_karya = mysqli_query($conn, $query_semua_karya);
if ($hasil_semua_karya) {
    while ($row = mysqli_fetch_assoc($hasil_semua_karya)) {
        $semua_karya[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Galeri Karya Seni - ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <style>
    /* Style mirip dengan karya_saya.php, bisa disatukan di style.css nanti */
    .container { padding: 2rem; max-width: 1200px; margin: 0 auto; }
    .container h1 { font-size: 2.5rem; margin-bottom: 2rem; color: #00ffd5; text-align: center; }
    .galeri-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
    .karya-item { background-color: #1c1c1c; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.3); transition: transform 0.3s ease; display:flex; flex-direction:column; }
    .karya-item:hover { transform: translateY(-5px); }
    .karya-item img { width: 100%; height: 220px; object-fit: cover; cursor: pointer; }
    .karya-detail { padding: 15px; flex-grow:1; }
    .karya-detail h3 { color: #00ffd5; margin-top: 0; margin-bottom: 8px; font-size: 1.2em; }
    .karya-detail p { font-size: 0.9em; color: #bbb; margin-bottom: 5px; }
    .karya-detail .uploader-info { font-size: 0.85em; color: #888; margin-top: 10px; }
    .karya-detail .uploader-info strong { color: #aaa; }
    .no-karya {text-align: center; color:#aaa; margin-top:30px; font-size:1.2em;}

    /* Modal untuk gambar */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.9); }
    .modal-content { margin: auto; display: block; width: auto; max-width: 85%; max-height: 85%; position:absolute; top:50%; left:50%; transform: translate(-50%, -50%);}
    .close-modal { position: absolute; top: 20px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; transition: 0.3s; }
    .close-modal:hover, .close-modal:focus { color: #bbb; text-decoration: none; cursor: pointer; }
  </style>
</head>
<body>
  <nav class="navbar">
    <img src="../assets/images/logo.png" alt="Logo" class="logo" />
    <ul class="nav-links">
      <li><a href="../guest/index.php">Beranda</a></li>
      <li><a href="tentang.html">Tentang</a></li>
      <li><a href="program.php">Program Kursus</a></li>
      <li><a href="artikel.html">Artikel</a></li>
      <li><a href="biaya.php">Biaya & Pendaftaran</a></li>
      <li><a href="galery_karya.php">Galery Karya</a></li>
      <?php if (isset($_SESSION['user_id'])): ?>
            <?php
                $role = $_SESSION['role'] ?? '';
                $dashboard_page = '#';
                if ($role === 'admin') $dashboard_page = 'admin/dashboard.php';
                elseif ($role === 'mentor') $dashboard_page = 'mentor/dashboard.php';
                elseif ($role === 'member') $dashboard_page = 'student/dashboard.php';
            ?>
            <li><a href="<?= $dashboard_page ?>">Dashboard</a></li>
            <li><a href="../guest/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="../guest/login.php">Login</a></li>
        <?php endif; ?>
    </ul>
  </nav>

  <main class="container">
    <h1>Galeri Karya Seni</h1>
    <?php if (empty($semua_karya)): ?>
        <p class="no-karya">Belum ada karya seni yang diupload.</p>
    <?php else: ?>
        <div class="galeri-grid">
            <?php foreach($semua_karya as $karya): ?>
                <div class="karya-item">
                    <img src="../<?= htmlspecialchars($karya['path_file_karya']); ?>" alt="<?= htmlspecialchars($karya['judul_karya']); ?>" onclick="bukaModalGambar(this.src)">
                    <div class="karya-detail">
                        <h3><?= htmlspecialchars($karya['judul_karya']); ?></h3>
                        <p class="uploader-info">Diupload oleh: <strong><?= htmlspecialchars($karya['nama_uploader']); ?></strong></p>
                        <p class="uploader-info">Tanggal: <?= htmlspecialchars(date('d M Y', strtotime($karya['tanggal_upload']))); ?></p>
                        <?php if (!empty($karya['nama_kelas_terkait'])): ?>
                            <p class="uploader-info">Kelas: <strong><?= htmlspecialchars($karya['nama_kelas_terkait']); ?></strong></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </main>

  <div id="gambarModal" class="modal">
    <span class="close-modal" onclick="tutupModalGambar()">&times;</span>
    <img class="modal-content" id="gambarModalKonten">
  </div>

  <footer>
    <p>&copy; <?= date("Y") ?> ngeWIP ArtClass BANZAIII</p>
  </footer>
  <script>
    var modal = document.getElementById("gambarModal");
    var modalImg = document.getElementById("gambarModalKonten");
    function bukaModalGambar(src) {
        modal.style.display = "block";
        modalImg.src = src;
    }
    function tutupModalGambar() {
        modal.style.display = "none";
    }
    window.onclick = function(event) {
        if (event.target == modal) {
            tutupModalGambar();
        }
    }
  </script>
</body>
</html>