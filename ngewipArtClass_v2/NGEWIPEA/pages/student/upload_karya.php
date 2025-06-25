<?php
session_start();
include '../../database/db.php';

// Pastikan yang login adalah member
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'member') {
    header("Location: ../../guest/login.php?pesan=login_dulu_member");
    exit();
}

$id_member_login = $_SESSION['user_id'];
$pesan_upload = '';

// Ambil daftar kursus yang diikuti member (status Aktif) untuk dropdown opsional
$kursus_diikuti = [];
$query_kursus_member = "SELECT ks.id_kelas, ks.nama_kelas 
                        FROM pendaftaran_kursus pk
                        JOIN kelas_seni ks ON pk.id_kelas = ks.id_kelas
                        WHERE pk.id_member = ? AND pk.status_pendaftaran = 'Aktif'
                        ORDER BY ks.nama_kelas ASC";
$stmt_kursus = mysqli_prepare($conn, $query_kursus_member);
mysqli_stmt_bind_param($stmt_kursus, "i", $id_member_login);
mysqli_stmt_execute($stmt_kursus);
$hasil_kursus = mysqli_stmt_get_result($stmt_kursus);
if ($hasil_kursus) {
    while ($row = mysqli_fetch_assoc($hasil_kursus)) {
        $kursus_diikuti[] = $row;
    }
}
mysqli_stmt_close($stmt_kursus);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_karya'])) {
    $judul_karya = mysqli_real_escape_string($conn, trim($_POST['judul_karya']));
    $deskripsi_karya = mysqli_real_escape_string($conn, trim($_POST['deskripsi_karya']));
    $id_kelas_terkait = !empty($_POST['id_kelas_terkait']) ? mysqli_real_escape_string($conn, $_POST['id_kelas_terkait']) : NULL;

    if (empty($judul_karya)) {
        $pesan_upload = '<p class="pesan error">Judul karya tidak boleh kosong.</p>';
    } elseif (!isset($_FILES['file_karya']) || $_FILES['file_karya']['error'] != 0) {
        $pesan_upload = '<p class="pesan error">Silakan pilih file karya yang akan diupload. Error: ' . ($_FILES['file_karya']['error'] ?? 'Tidak ada file') . '</p>';
    } else {
        $target_dir_karya = "../../assets/images/karya_member/"; // Pastikan folder ini ada dan writable
        if (!is_dir($target_dir_karya)) {
            mkdir($target_dir_karya, 0777, true);
        }

        $file_name_karya = time() . '_' . basename($_FILES["file_karya"]["name"]);
        $target_file_karya = $target_dir_karya . $file_name_karya;
        $imageFileType = strtolower(pathinfo($target_file_karya, PATHINFO_EXTENSION));
        $allowed_types = array("jpg", "jpeg", "png", "gif"); // Izinkan tipe gambar umum

        // Validasi ukuran file (misalnya, maks 5MB)
        if ($_FILES["file_karya"]["size"] > 5000000) {
            $pesan_upload = '<p class="pesan error">Maaf, ukuran file terlalu besar (maks 5MB).</p>';
        } elseif (!in_array($imageFileType, $allowed_types)) {
            $pesan_upload = '<p class="pesan error">Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.</p>';
        } else {
            if (move_uploaded_file($_FILES["file_karya"]["tmp_name"], $target_file_karya)) {
                $path_file_simpan = "assets/images/karya_member/" . $file_name_karya; // Path relatif untuk DB

                $query_insert_karya = "INSERT INTO karya_seni (id_member, judul_karya, deskripsi_karya, path_file_karya, id_kelas_terkait) 
                                       VALUES (?, ?, ?, ?, ?)";
                $stmt_insert = mysqli_prepare($conn, $query_insert_karya);
                mysqli_stmt_bind_param($stmt_insert, "issss", $id_member_login, $judul_karya, $deskripsi_karya, $path_file_simpan, $id_kelas_terkait);
                
                if (mysqli_stmt_execute($stmt_insert)) {
                    $pesan_upload = '<p class="pesan sukses">Karya berhasil diupload!</p>';
                } else {
                    $pesan_upload = '<p class="pesan error">Gagal menyimpan informasi karya ke database: ' . mysqli_stmt_error($stmt_insert) . '</p>';
                    // Hapus file yang sudah terupload jika insert DB gagal
                    if (file_exists($target_file_karya)) {
                        unlink($target_file_karya);
                    }
                }
                mysqli_stmt_close($stmt_insert);
            } else {
                $pesan_upload = '<p class="pesan error">Maaf, terjadi kesalahan saat mengupload file karya Anda.</p>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Upload Karya Seni - ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../../assets/css/student.css" />
  <style>
    .form-container { background-color: #2b2b2b; padding: 20px; border-radius: 8px; margin-bottom: 30px; max-width: 600px; margin: 20px auto; }
    .form-container h3 { color: #00ffcc; margin-top: 0; }
    .form-container label { display: block; margin-bottom: 5px; color: #ccc; }
    .form-container input[type="text"],
    .form-container textarea,
    .form-container select,
    .form-container input[type="file"] {
        width: calc(100% - 22px); padding: 10px; margin-bottom: 15px;
        border-radius: 4px; border: 1px solid #444; background-color: #333; color: #fff;
    }
    .form-container textarea { min-height: 100px; }
    .form-container button {
        padding: 10px 20px; background-color: #00ffcc; color: #111;
        border: none; border-radius: 4px; cursor: pointer; font-weight: bold;
    }
    .form-container button:hover { background-color: #00e6b2; }
    .pesan { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align:center; }
    .pesan.sukses { background-color: #28a74533; color: #a3ffb8; border: 1px solid #28a74588; }
    .pesan.error { background-color: #dc354533; color: #ffacb3; border: 1px solid #dc354588; }
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
        <a href="upload_karya.php" class="active">posting Karya</a>
        <a href="karya_saya.php">Karya Saya</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header>
        <button id="modeToggle" style="position: fixed; top: 10px; right: 10px; z-index: 200;">☀️/🌙</button>
        <h1>Upload Karya Seni Anda</h1>
      </header>

      <?= $pesan_upload; ?>

      <div class="form-container">
        <form action="upload_karya.php" method="POST" enctype="multipart/form-data">
            <div>
                <label for="judul_karya">Judul Karya:</label>
                <input type="text" id="judul_karya" name="judul_karya" required>
            </div>
            <div>
                <label for="deskripsi_karya">Deskripsi Karya (Opsional):</label>
                <textarea id="deskripsi_karya" name="deskripsi_karya"></textarea>
            </div>
            <div>
                <label for="file_karya">Pilih File Karya (Gambar: JPG, PNG, GIF - Maks 5MB):</label>
                <input type="file" id="file_karya" name="file_karya" accept="image/jpeg,image/png,image/gif" required>
            </div>
            <div>
                <label for="id_kelas_terkait">Terkait dengan Kelas (Opsional):</label>
                <select name="id_kelas_terkait" id="id_kelas_terkait">
                    <option value="">-- Tidak terkait kelas tertentu --</option>
                    <?php foreach($kursus_diikuti as $kursus_item): ?>
                        <option value="<?= htmlspecialchars($kursus_item['id_kelas']); ?>">
                            <?= htmlspecialchars($kursus_item['nama_kelas']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="submit_karya">Upload Karya</button>
        </form>
      </div>
    </main>
  </div>
<script src="../assets/js/script.js"></script>
</body>
</html>