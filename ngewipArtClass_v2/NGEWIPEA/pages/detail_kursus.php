<?php
session_start();
include '../database/db.php'; //

$id_kursus = null;
$kursus = null;
$pesan_pendaftaran = '';

if (isset($_GET['id_kursus'])) {
    $id_kursus = mysqli_real_escape_string($conn, $_GET['id_kursus']);
    $query_detail = "SELECT ks.*, m.username AS nama_mentor 
                     FROM kelas_seni ks
                     LEFT JOIN mentor m ON ks.id_mentor = m.id_mentor
                     WHERE ks.id_kelas = '$id_kursus'";
    $hasil_detail = mysqli_query($conn, $query_detail);
    if ($hasil_detail && mysqli_num_rows($hasil_detail) > 0) {
        $kursus = mysqli_fetch_assoc($hasil_detail);
    } else {
        // Kursus tidak ditemukan, bisa redirect atau tampilkan pesan
        header("Location: program.php"); // Kembali ke halaman program
        exit();
    }
} else {
    // ID Kursus tidak ada, redirect atau tampilkan pesan
    header("Location: program.php"); // Kembali ke halaman program
    exit();
}

// Proses pendaftaran jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar_kursus'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
        // Simpan id_kursus dan tipe_dipilih ke session agar bisa dilanjutkan setelah login
        $_SESSION['redirect_after_login'] = "pages/detail_kursus.php?id_kursus=" . urlencode($id_kursus);
        $_SESSION['kursus_ingin_didaftar_id'] = $id_kursus;
        $_SESSION['kursus_ingin_didaftar_tipe'] = $_POST['tipe_dipilih'];
        header("Location: ../guest/login.php?pesan=login_dulu");
        exit();
    }

    $id_member = $_SESSION['id_member']; // Pastikan Anda menyimpan id_member di session saat login
                                        // atau $_SESSION['user_id'] jika itu adalah id_member
    if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'member') {
         $id_member = $_SESSION['user_id']; // Asumsikan user_id untuk member adalah id_member
    }


    $tipe_dipilih = mysqli_real_escape_string($conn, $_POST['tipe_dipilih']);

    // Cek apakah sudah pernah mendaftar kursus yang sama dengan tipe yang sama (dan belum batal/selesai)
    $cek_pendaftaran_query = "SELECT id_pendaftaran FROM pendaftaran_kursus 
                              WHERE id_member = '$id_member' AND id_kelas = '$id_kursus' AND tipe_yang_dipilih = '$tipe_dipilih'
                              AND status_pendaftaran NOT IN ('Selesai', 'Dibatalkan')";
    $hasil_cek = mysqli_query($conn, $cek_pendaftaran_query);

    if ($hasil_cek && mysqli_num_rows($hasil_cek) > 0) {
        $pesan_pendaftaran = '<p class="pesan error">Anda sudah terdaftar atau sedang dalam proses untuk kursus ini dengan tipe yang dipilih.</p>';
    } else {
        $query_insert_pendaftaran = "INSERT INTO pendaftaran_kursus (id_member, id_kelas, tipe_yang_dipilih, tanggal_pendaftaran, status_pendaftaran) 
                                     VALUES ('$id_member', '$id_kursus', '$tipe_dipilih', NOW(), 'Menunggu Pembayaran')";
        
        if (mysqli_query($conn, $query_insert_pendaftaran)) {
            $id_pendaftaran_baru = mysqli_insert_id($conn);
            // Redirect ke halaman pembayaran atau tampilkan pesan sukses dengan instruksi
            // Untuk sekarang, tampilkan pesan sukses saja
            $pesan_pendaftaran = '<p class="pesan sukses">Pendaftaran berhasil! Silakan lanjutkan ke pembayaran. ID Pendaftaran Anda: ' . $id_pendaftaran_baru . '</p>';
            // Idealnya: header("Location: pembayaran.php?id_pendaftaran=" . $id_pendaftaran_baru); exit();
        } else {
            $pesan_pendaftaran = '<p class="pesan error">Gagal melakukan pendaftaran: ' . mysqli_error($conn) . '</p>';
        }
    }
}

// Cek apakah ada data kursus yang ingin didaftar dari session (setelah login)
if (isset($_SESSION['kursus_ingin_didaftar_id']) && $_SESSION['kursus_ingin_didaftar_id'] == $id_kursus && isset($_SESSION['kursus_ingin_didaftar_tipe'])) {
    if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'member') {
        $id_member_session = $_SESSION['user_id'];
        $tipe_dipilih_session = $_SESSION['kursus_ingin_didaftar_tipe'];

        // Otomatis submit form pendaftaran
        // Ini adalah cara sederhana, bisa dibuat lebih canggih dengan JS atau konfirmasi ulang
        $_POST['daftar_kursus'] = true; // Tandai seolah-olah form disubmit
        $_POST['tipe_dipilih'] = $tipe_dipilih_session;

        // Hapus session agar tidak ter-trigger lagi
        unset($_SESSION['kursus_ingin_didaftar_id']);
        unset($_SESSION['kursus_ingin_didaftar_tipe']);
        unset($_SESSION['redirect_after_login']);

        // Jalankan ulang logika pendaftaran
        // (Untuk menghindari duplikasi kode, idealnya logika pendaftaran ada di fungsi terpisah)
        $cek_pendaftaran_query_session = "SELECT id_pendaftaran FROM pendaftaran_kursus 
                                  WHERE id_member = '$id_member_session' AND id_kelas = '$id_kursus' AND tipe_yang_dipilih = '$tipe_dipilih_session'
                                  AND status_pendaftaran NOT IN ('Selesai', 'Dibatalkan')";
        $hasil_cek_session = mysqli_query($conn, $cek_pendaftaran_query_session);

        if ($hasil_cek_session && mysqli_num_rows($hasil_cek_session) > 0) {
            $pesan_pendaftaran = '<p class="pesan error">Anda sudah terdaftar atau sedang dalam proses untuk kursus ini dengan tipe yang dipilih.</p>';
        } else {
            $query_insert_pendaftaran_session = "INSERT INTO pendaftaran_kursus (id_member, id_kelas, tipe_yang_dipilih, tanggal_pendaftaran, status_pendaftaran) 
                                         VALUES ('$id_member_session', '$id_kursus', '$tipe_dipilih_session', NOW(), 'Menunggu Pembayaran')";
            
            if (mysqli_query($conn, $query_insert_pendaftaran_session)) {
                $id_pendaftaran_baru_session = mysqli_insert_id($conn);
                $pesan_pendaftaran = '<p class="pesan sukses">Pendaftaran berhasil (setelah login)! Silakan lanjutkan ke pembayaran. ID Pendaftaran Anda: ' . $id_pendaftaran_baru_session . '</p>';
            } else {
                $pesan_pendaftaran = '<p class="pesan error">Gagal melakukan pendaftaran (setelah login): ' . mysqli_error($conn) . '</p>';
            }
        }
    } else {
        // Jika role bukan member atau user_id tidak ada, hapus saja sessionnya
        unset($_SESSION['kursus_ingin_didaftar_id']);
        unset($_SESSION['kursus_ingin_didaftar_tipe']);
        unset($_SESSION['redirect_after_login']);
    }
}


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kursus: <?= htmlspecialchars($kursus['nama_kelas'] ?? 'Tidak Ditemukan'); ?> - ngeWIP ArtClass</title>
    <script>
  if (localStorage.getItem("theme") === "light") {
    document.documentElement.classList.add("light-mode");
  }
</script>
    <link rel="stylesheet" href="../assets/css/style.css"> <style>
        .container-detail { max-width: 800px; margin: 30px auto; padding: 20px; background-color: #1e1e1e; border-radius: 8px; }
        .container-detail img { width: 100%; max-height: 400px; object-fit: cover; border-radius: 4px; margin-bottom: 20px; }
        .container-detail h1 { color: #00ffcc; margin-bottom: 10px; }
        .container-detail .mentor { color: #aaa; font-style: italic; margin-bottom: 20px; }
        .container-detail .deskripsi-lengkap { color: #ddd; line-height: 1.6; margin-bottom: 20px; }
        .harga-detail strong { color: #00ffcc; }
        .form-pendaftaran { margin-top: 30px; padding-top: 20px; border-top: 1px solid #333; }
        .form-pendaftaran label { display: block; margin-bottom: 8px; color: #00ffcc; }
        .form-pendaftaran select, .form-pendaftaran button {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #444;
            background-color: #2b2b2b;
            color: #eee;
            font-size: 1em;
        }
        .form-pendaftaran button { background-color: #00ffcc; color: #111; font-weight: bold; cursor: pointer; }
        .form-pendaftaran button:hover { background-color: #00e6b2; }
        .pesan { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align:center; }
        .pesan.sukses { background-color: #28a74533; color: #a3ffb8; border: 1px solid #28a74588; }
        .pesan.error { background-color: #dc354533; color: #ffacb3; border: 1px solid #dc354588; }
    </style>
</head>
<body>
    <button id="modeToggle" style="position: fixed; top: 10px; right: 10px; z-index: 200;">☀️/🌙</button>
    <nav class="navbar">
        <img src="../assets/images/logo.png" alt="Logo" class="logo">
        <ul class="nav-links">
            <li><a href="../guest/index.php">Beranda</a></li> <li><a href="tentang.html">Tentang</a></li> <li><a href="program.php">Program Kursus</a></li>
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

    <div class="container-detail">
        <?php if ($kursus): ?>
            <img src="../<?= htmlspecialchars(!empty($kursus['path_gambar']) ? $kursus['path_gambar'] : 'assets/images/kursus/default.png'); ?>" alt="<?= htmlspecialchars($kursus['nama_kelas']); ?>">
            <h1><?= htmlspecialchars($kursus['nama_kelas']); ?></h1>
            <?php if(!empty($kursus['nama_mentor'])): ?>
                <p class="mentor">Mentor: <?= htmlspecialchars($kursus['nama_mentor']); ?></p>
            <?php endif; ?>
            <div class="deskripsi-lengkap">
                <p><?= nl2br(htmlspecialchars($kursus['deskripsi_lengkap'] ?? $kursus['deskripsi_singkat'] ?? 'Informasi detail mengenai kursus ini belum tersedia.')); ?></p>
            </div>
            <div class="harga-detail">
                <h4>Harga:</h4>
                <?php
                $tipe_tersedia = explode(',', $kursus['tipe_kelas_tersedia'] ?? '');
                $ada_harga = false;
                if (in_array('Online', $tipe_tersedia) && !is_null($kursus['harga_online'])) {
                    echo "<p>Online: <strong>Rp " . number_format($kursus['harga_online'], 0, ',', '.') . "</strong></p>";
                    $ada_harga = true;
                }
                if (in_array('Offline', $tipe_tersedia) && !is_null($kursus['harga_offline'])) {
                    echo "<p>Offline: <strong>Rp " . number_format($kursus['harga_offline'], 0, ',', '.') . "</strong></p>";
                    $ada_harga = true;
                }
                if (!$ada_harga) {
                    echo "<p>Harga belum ditentukan.</p>";
                }
                ?>
            </div>

            <div class="form-pendaftaran">
                <h3>Daftar Kursus Ini</h3>
                <?= $pesan_pendaftaran; // Tampilkan pesan sukses/error dari proses pendaftaran ?>
                
                <form action="detail_kursus.php?id_kursus=<?= htmlspecialchars($id_kursus); ?>" method="POST">
                    <label for="tipe_dipilih">Pilih Tipe Kelas:</label>
                    <select name="tipe_dipilih" id="tipe_dipilih" required>
                        <?php
                        $tipe_dipilih_sebelumnya = $_POST['tipe_dipilih'] ?? ($_SESSION['kursus_ingin_didaftar_tipe'] ?? '');
                        if (in_array('Online', $tipe_tersedia) && !is_null($kursus['harga_online'])): ?>
                            <option value="Online" <?= ($tipe_dipilih_sebelumnya == 'Online') ? 'selected' : '' ?>>Online - Rp <?= number_format($kursus['harga_online'], 0, ',', '.'); ?></option>
                        <?php endif; ?>
                        <?php if (in_array('Offline', $tipe_tersedia) && !is_null($kursus['harga_offline'])): ?>
                            <option value="Offline" <?= ($tipe_dipilih_sebelumnya == 'Offline') ? 'selected' : '' ?>>Offline - Rp <?= number_format($kursus['harga_offline'], 0, ',', '.'); ?></option>
                        <?php endif; ?>
                        <?php if (empty(array_filter($tipe_tersedia)) || (!$kursus['harga_online'] && !$kursus['harga_offline'])): ?>
                             <option value="" disabled>Tipe kelas belum tersedia</option>
                        <?php endif; ?>
                    </select>
                    <button type="submit" name="daftar_kursus" <?= (empty(array_filter($tipe_tersedia)) || (!$kursus['harga_online'] && !$kursus['harga_offline'])) ? 'disabled' : '' ?>>
                        Daftar Sekarang
                    </button>
                </form>
                <?php if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member'): ?>
                    <p style="font-size:0.9em; color:#ffc107; margin-top:10px;">Anda harus <a href="../guest/login.php?redirect=pages/detail_kursus.php?id_kursus=<?= urlencode($id_kursus); ?>">login sebagai member</a> untuk mendaftar.</p>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <h1>Kursus Tidak Ditemukan</h1>
            <p>Maaf, kursus yang Anda cari tidak dapat ditemukan. Silakan kembali ke <a href="program.php">halaman program kursus</a>.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?= date("Y") ?> ngeWIP ArtClass BANZAIII</p>
    </footer>
    <script src="../assets/js/script.js"></script>
</body>
</html>