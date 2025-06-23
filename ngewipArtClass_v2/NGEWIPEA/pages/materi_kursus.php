<?php
session_start();
include '../database/db.php';

// 1. Pastikan user adalah member dan sudah login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'member') {
    $_SESSION['pesan_error'] = "Anda harus login sebagai member untuk mengakses materi kursus.";
    header("Location: ../guest/login.php");
    exit();
}

$id_member = $_SESSION['user_id'];
$id_kelas_url = '';
$nama_kelas_saat_ini = "Materi Kursus";
$daftar_materi = [];
$kursus_aktif = false;

if (isset($_GET['id_kelas'])) {
    $id_kelas_url = mysqli_real_escape_string($conn, $_GET['id_kelas']);

    // 2. Cek apakah member ini terdaftar di kursus tersebut dan statusnya Aktif
    $query_cek_pendaftaran = "SELECT ks.nama_kelas
                              FROM pendaftaran_kursus pk
                              JOIN kelas_seni ks ON pk.id_kelas = ks.id_kelas
                              WHERE pk.id_member = ? AND pk.id_kelas = ? AND pk.status_pendaftaran = 'Aktif'";
    $stmt_cek = mysqli_prepare($conn, $query_cek_pendaftaran);
    mysqli_stmt_bind_param($stmt_cek, "is", $id_member, $id_kelas_url);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);

    if ($result_cek && mysqli_num_rows($result_cek) > 0) {
        $kursus_aktif = true;
        $row_kelas_info = mysqli_fetch_assoc($result_cek);
        $nama_kelas_saat_ini = htmlspecialchars($row_kelas_info['nama_kelas']);

        // 3. Jika terdaftar dan aktif, ambil materi pelajaran untuk kelas tersebut
        $query_materi = "SELECT id_materi, judul_materi, deskripsi_materi, konten_materi, tipe_konten
                         FROM materi_pelajaran
                         WHERE id_kelas = ?
                         ORDER BY urutan ASC, id_materi ASC";
        $stmt_materi = mysqli_prepare($conn, $query_materi);
        mysqli_stmt_bind_param($stmt_materi, "s", $id_kelas_url);
        mysqli_stmt_execute($stmt_materi);
        $result_materi = mysqli_stmt_get_result($stmt_materi);

        if ($result_materi && mysqli_num_rows($result_materi) > 0) {
            while ($row = mysqli_fetch_assoc($result_materi)) {
                $daftar_materi[] = $row;
            }
        }
        mysqli_stmt_close($stmt_materi);
    } else {
        // Member tidak terdaftar atau kursus tidak aktif untuk member ini
        $_SESSION['pesan_error_kursus'] = "Anda tidak memiliki akses ke materi kursus ini atau pendaftaran Anda belum aktif.";
        // Bisa redirect ke halaman kursus_saya.php atau tampilkan pesan di halaman ini
    }
    mysqli_stmt_close($stmt_cek);

} else {
    // id_kelas tidak ada di URL
    $_SESSION['pesan_error_kursus'] = "ID Kursus tidak valid.";
    // header("Location: student/kursus_saya.php");
    // exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi Kursus: <?= $nama_kelas_saat_ini; ?> - ngeWIP ArtClass</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/student.css"> <style>
        .container-materi { max-width: 900px; margin: 30px auto; padding: 20px; background-color: #1e1e1e; border-radius: 8px; }
        .container-materi h1 { color: #00ffcc; margin-bottom: 20px; border-bottom:1px solid #333; padding-bottom:10px;}
        .breadcrumb a { color: #00ffcc; text-decoration: none;}
        .breadcrumb { margin-bottom:20px; font-size:0.9em; color:#aaa;}

        .daftar-modul { list-style-type: none; padding-left: 0; }
        .modul-item { background-color: #2b2b2b; margin-bottom: 15px; border-radius: 5px; }
        .modul-header { padding: 15px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #383838;}
        .modul-header h3 { margin: 0; font-size: 1.2em; color: #00e6b2; }
        .modul-header .toggle-icon { font-size: 1.5em; transition: transform 0.3s; }
        .modul-content { padding: 0 15px; max-height: 0; overflow: hidden; transition: max-height 0.5s ease-out, padding 0.5s ease-out; }
        .modul-content.open { max-height: 1000px; /* Cukup besar untuk konten */ padding: 15px; }
        .modul-content p { margin-top:0; color:#ddd; line-height:1.6;}
        .modul-content .video-container { position: relative; padding-bottom: 56.25%; /* 16:9 */ height: 0; overflow: hidden; max-width: 100%; background: #000; margin-bottom:15px;}
        .modul-content .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        .modul-content a.link-materi, .modul-content a.file-materi { color: #00ffcc; display: inline-block; margin-top: 10px; }
        .pesan-akses { background-color: #332020; color: #ffc0cb; padding: 15px; border-radius: 5px; text-align: center; border:1px solid #5c3032; }
    </style>
</head>
<body>
    <nav class="navbar">
        <img src="../assets/images/logo.png" alt="Logo" class="logo">
        <ul class="nav-links">
            <li><a href="../guest/index.php">Beranda</a></li>
            <li><a href="student/dashboard.php">Dashboard Siswa</a></li>
            <li><a href="student/kursus_saya.php">Kursus Saya</a></li>
            <li><a href="../guest/logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container-materi">
        <p class="breadcrumb"><a href="student/kursus_saya.php">Kursus Saya</a> &gt; <?= $nama_kelas_saat_ini; ?></p>
        <h1>Materi Kursus: <?= $nama_kelas_saat_ini; ?></h1>

        <?php if (isset($_SESSION['pesan_error_kursus'])): ?>
            <p class="pesan-akses"><?= htmlspecialchars($_SESSION['pesan_error_kursus']); ?></p>
            <?php unset($_SESSION['pesan_error_kursus']); ?>
        <?php elseif (!$kursus_aktif): ?>
            <p class="pesan-akses">Anda tidak memiliki akses ke materi kursus ini atau pendaftaran Anda belum aktif. Silakan periksa status pendaftaran Anda di halaman <a href="student/kursus_saya.php">Kursus Saya</a>.</p>
        <?php elseif (empty($daftar_materi)): ?>
            <p>Belum ada materi yang tersedia untuk kursus ini.</p>
        <?php else: ?>
            <ul class="daftar-modul">
                <?php foreach ($daftar_materi as $materi): ?>
                    <li class="modul-item">
                        <div class="modul-header">
                            <h3><?= htmlspecialchars($materi['judul_materi']); ?></h3>
                            <span class="toggle-icon">+</span>
                        </div>
                        <div class="modul-content">
                            <?php if (!empty($materi['deskripsi_materi'])): ?>
                                <p><em><?= nl2br(htmlspecialchars($materi['deskripsi_materi'])); ?></em></p>
                                <hr style="border-color: #444; margin:10px 0;">
                            <?php endif; ?>

                            <?php
                            switch ($materi['tipe_konten']) {
                                case 'Teks':
                                    echo "<div class='konten-teks'>" . nl2br(htmlspecialchars($materi['konten_materi'])) . "</div>";
                                    break;
                                case 'VideoEmbed':
                                    // Penting: Perlu sanitasi untuk kode embed, tapi untuk contoh ini kita tampilkan langsung
                                    // Di produksi, pertimbangkan library sanitasi HTML atau hanya izinkan domain video tertentu.
                                    echo "<div class='video-container'>" . $materi['konten_materi'] . "</div>";
                                    break;
                                case 'Link':
                                    echo "<p><a href='" . htmlspecialchars($materi['konten_materi']) . "' target='_blank' class='link-materi'>Buka Tautan Materi</a></p>";
                                    break;
                                case 'File':
                                    // Path file relatif terhadap root web Anda
                                    echo "<p><a href='../" . htmlspecialchars($materi['konten_materi']) . "' target='_blank' class='file-materi'>Unduh File Materi</a></p>";
                                    // Pastikan folder assets/materi/ dapat diakses dan berisi file yang sesuai
                                    break;
                                default:
                                    echo "<p>Tipe konten tidak dikenali.</p>";
                            }
                            ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?= date("Y") ?> ngeWIP ArtClass BANZAIII</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modulHeaders = document.querySelectorAll('.modul-header');
            modulHeaders.forEach(header => {
                header.addEventListener('click', function () {
                    const content = this.nextElementSibling;
                    const icon = this.querySelector('.toggle-icon');
                    content.classList.toggle('open');
                    icon.textContent = content.classList.contains('open') ? '-' : '+';
                });
            });
        });
    </script>
</body>
</html>