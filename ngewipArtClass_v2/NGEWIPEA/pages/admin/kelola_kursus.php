<?php
session_start();
include '../../database/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../guest/login.php");
    exit();
}

$pesan = '';
$edit_mode = false;
$kursus_to_edit = null;

// Logika untuk menangani form (Tambah/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kelas = mysqli_real_escape_string($conn, $_POST['id_kelas']);
    $nama_kelas = mysqli_real_escape_string($conn, $_POST['nama_kelas']);
    $deskripsi_singkat = mysqli_real_escape_string($conn, $_POST['deskripsi_singkat']);
    $deskripsi_lengkap = mysqli_real_escape_string($conn, $_POST['deskripsi_lengkap']);
    $path_gambar_lama = $_POST['path_gambar_lama'] ?? 'assets/images/kursus/default.png';
    $harga_online = !empty($_POST['harga_online']) ? mysqli_real_escape_string($conn, $_POST['harga_online']) : NULL;
    $harga_offline = !empty($_POST['harga_offline']) ? mysqli_real_escape_string($conn, $_POST['harga_offline']) : NULL;
    $id_mentor = !empty($_POST['id_mentor']) ? mysqli_real_escape_string($conn, $_POST['id_mentor']) : NULL;
    
    $tipe_kelas_tersedia_array = $_POST['tipe_kelas_tersedia'] ?? [];
    $tipe_kelas_tersedia = !empty($tipe_kelas_tersedia_array) ? implode(',', $tipe_kelas_tersedia_array) : NULL;

    // Handle Upload Gambar (Contoh Sederhana)
    $path_gambar = $path_gambar_lama; // Default ke gambar lama
    if (isset($_FILES['path_gambar']) && $_FILES['path_gambar']['error'] == 0) {
        $target_dir = "../../assets/images/kursus/";
        // Buat nama file unik untuk menghindari penimpaan
        $image_name = time() . '_' . basename($_FILES["path_gambar"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Cek tipe file (opsional tapi direkomendasikan)
        $allowed_types = array("jpg", "jpeg", "png", "gif");
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["path_gambar"]["tmp_name"], $target_file)) {
                $path_gambar = "assets/images/kursus/" . $image_name; // Simpan path relatif
                 // Hapus gambar lama jika ada dan berbeda, dan bukan default
                if ($path_gambar_lama !== 'assets/images/kursus/default.png' && $path_gambar_lama !== $path_gambar && file_exists("../../".$path_gambar_lama) ) {
                    unlink("../../".$path_gambar_lama);
                }
            } else {
                $pesan = '<p class="pesan error">Maaf, terjadi kesalahan saat mengupload gambar.</p>';
            }
        } else {
            $pesan = '<p class="pesan error">Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.</p>';
        }
    }


    if (isset($_POST['edit_id_kelas']) && !empty($_POST['edit_id_kelas'])) {
        // Mode Edit
        $edit_id = mysqli_real_escape_string($conn, $_POST['edit_id_kelas']);
        $query_update = "UPDATE kelas_seni SET 
                            id_kelas = '$id_kelas', 
                            nama_kelas = '$nama_kelas', 
                            deskripsi_singkat = '$deskripsi_singkat', 
                            deskripsi_lengkap = '$deskripsi_lengkap', 
                            path_gambar = '$path_gambar', 
                            harga_online = " . ($harga_online ? "'$harga_online'" : "NULL") . ", 
                            harga_offline = " . ($harga_offline ? "'$harga_offline'" : "NULL") . ", 
                            tipe_kelas_tersedia = " . ($tipe_kelas_tersedia ? "'$tipe_kelas_tersedia'" : "NULL") . ", 
                            id_mentor = " . ($id_mentor ? "'$id_mentor'" : "NULL") . "
                        WHERE id_kelas = '$edit_id'";
        if (mysqli_query($conn, $query_update)) {
            $pesan = '<p class="pesan sukses">Kursus berhasil diperbarui.</p>';
        } else {
            $pesan = '<p class="pesan error">Gagal memperbarui kursus: ' . mysqli_error($conn) . '</p>';
        }
    } else {
        // Mode Tambah
        // Cek apakah id_kelas sudah ada
        $cek_id = mysqli_query($conn, "SELECT id_kelas FROM kelas_seni WHERE id_kelas = '$id_kelas'");
        if(mysqli_num_rows($cek_id) > 0){
            $pesan = '<p class="pesan error">ID Kelas sudah ada. Gunakan ID lain.</p>';
        } else {
            $query_insert = "INSERT INTO kelas_seni (id_kelas, nama_kelas, deskripsi_singkat, deskripsi_lengkap, path_gambar, harga_online, harga_offline, tipe_kelas_tersedia, id_mentor) 
                             VALUES ('$id_kelas', '$nama_kelas', '$deskripsi_singkat', '$deskripsi_lengkap', '$path_gambar', " . ($harga_online ? "'$harga_online'" : "NULL") . ", " . ($harga_offline ? "'$harga_offline'" : "NULL") . ", " . ($tipe_kelas_tersedia ? "'$tipe_kelas_tersedia'" : "NULL") . ", " . ($id_mentor ? "'$id_mentor'" : "NULL") . ")";
            if (mysqli_query($conn, $query_insert)) {
                $pesan = '<p class="pesan sukses">Kursus baru berhasil ditambahkan.</p>';
            } else {
                $pesan = '<p class="pesan error">Gagal menambahkan kursus: ' . mysqli_error($conn) . '</p>';
            }
        }
    }
}

// Logika untuk Hapus Kursus
if (isset($_GET['hapus_id_kelas'])) {
    $hapus_id = mysqli_real_escape_string($conn, $_GET['hapus_id_kelas']);
    
    // Ambil path gambar untuk dihapus dari server
    $q_gambar = mysqli_query($conn, "SELECT path_gambar FROM kelas_seni WHERE id_kelas = '$hapus_id'");
    if($r_gambar = mysqli_fetch_assoc($q_gambar)){
        $gambar_untuk_dihapus = $r_gambar['path_gambar'];
    }

    // Perlu dicek apakah ada pendaftaran yang terkait sebelum menghapus (opsional, tergantung aturan bisnis)
    // Jika ada, mungkin tidak boleh dihapus atau pendaftaran terkait harus di-handle
    $query_hapus = "DELETE FROM kelas_seni WHERE id_kelas = '$hapus_id'";
    if (mysqli_query($conn, $query_hapus)) {
        if (isset($gambar_untuk_dihapus) && $gambar_untuk_dihapus !== 'assets/images/kursus/default.png' && file_exists("../../".$gambar_untuk_dihapus)) {
            unlink("../../".$gambar_untuk_dihapus);
        }
        $pesan = '<p class="pesan sukses">Kursus berhasil dihapus.</p>';
    } else {
        $pesan = '<p class="pesan error">Gagal menghapus kursus: ' . mysqli_error($conn) . '. Mungkin masih ada data pendaftaran terkait.</p>';
    }
}

// Logika untuk Mode Edit (mengisi form)
if (isset($_GET['edit_id_kelas'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit_id_kelas']);
    $query_edit = "SELECT * FROM kelas_seni WHERE id_kelas = '$edit_id'";
    $hasil_edit = mysqli_query($conn, $query_edit);
    if ($hasil_edit && mysqli_num_rows($hasil_edit) > 0) {
        $kursus_to_edit = mysqli_fetch_assoc($hasil_edit);
        $edit_mode = true;
    }
}


// Mengambil daftar kursus
$query_daftar_kursus = "SELECT ks.*, m.username as nama_mentor FROM kelas_seni ks LEFT JOIN mentor m ON ks.id_mentor = m.id_mentor ORDER BY ks.nama_kelas ASC";
$hasil_daftar_kursus = mysqli_query($conn, $query_daftar_kursus);

// Mengambil daftar mentor untuk dropdown
$mentors = [];
$query_mentors = "SELECT id_mentor, username FROM mentor ORDER BY username ASC";
$result_mentors = mysqli_query($conn, $query_mentors);
if ($result_mentors) {
    while ($row_mentor = mysqli_fetch_assoc($result_mentors)) {
        $mentors[] = $row_mentor;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kelola Kursus - Admin ngeWIP ArtClass</title>
  <script>
  if (localStorage.getItem("theme") === "light") {
    document.documentElement.classList.add("light-mode");
  }
</script>
  <link rel="stylesheet" href="../../assets/css/admin.css" />
  <style>
    .form-container { background-color: #2b2b2b; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
    .form-container h3 { color: #00ffcc; margin-top: 0; }
    .form-container label { display: block; margin-bottom: 5px; color: #ccc; }
    .form-container input[type="text"],
    .form-container input[type="number"],
    .form-container textarea,
    .form-container select {
        width: calc(100% - 22px); padding: 10px; margin-bottom: 10px;
        border-radius: 4px; border: 1px solid #444; background-color: #333; color: #fff;
    }
    .form-container textarea { min-height: 80px; }
    .form-container input[type="checkbox"] { margin-right: 5px; }
    .form-container .checkbox-group label { display: inline-block; margin-right: 15px; color:#fff;}

    .form-container button {
        padding: 10px 20px; background-color: #00ffcc; color: #111;
        border: none; border-radius: 4px; cursor: pointer; font-weight: bold;
    }
    .form-container button:hover { background-color: #00e6b2; }
    .form-container .cancel-edit { background-color: #777; color: #fff; margin-left:10px; }
    .form-container .cancel-edit:hover { background-color: #555; }

    .table-container { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; color: #ccc; }
    table th, table td { border: 1px solid #444; padding: 10px; text-align: left; }
    table th { background-color: #333; color: #00ffcc; }
    table td img { max-width: 100px; height: auto; border-radius: 4px; }
    table td a { color: #00ffcc; text-decoration: none; margin-right: 10px; }
    table td a:hover { text-decoration: underline; }
    .pesan { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align:center; }
    .pesan.sukses { background-color: #28a74533; color: #a3ffb8; border: 1px solid #28a74588; }
    .pesan.error { background-color: #dc354533; color: #ffacb3; border: 1px solid #dc354588; }
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
        <a href="kelola_kursus.php" class="active">Kelola Kursus</a>
        <a href="verifikasi_pembayaran.php">Verifikasi Pembayaran</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header>
        <button id="modeToggle" style="position: fixed; top: 10px; right: 10px; z-index: 200;">☀️/🌙</button>
        <h1>Kelola Program Kursus</h1>
      </header>

      <?= $pesan; // Tampilkan pesan sukses/error ?>

      <div class="form-container">
        <h3><?= $edit_mode ? 'Edit Kursus' : 'Tambah Kursus Baru'; ?></h3>
        <form action="kelola_kursus.php" method="POST" enctype="multipart/form-data">
            <?php if ($edit_mode && $kursus_to_edit): ?>
                <input type="hidden" name="edit_id_kelas" value="<?= htmlspecialchars($kursus_to_edit['id_kelas']); ?>">
                <input type="hidden" name="path_gambar_lama" value="<?= htmlspecialchars($kursus_to_edit['path_gambar']); ?>">
            <?php endif; ?>

            <div>
                <label for="id_kelas">ID Kelas (Contoh: KS005):</label>
                <input type="text" id="id_kelas" name="id_kelas" value="<?= htmlspecialchars($kursus_to_edit['id_kelas'] ?? ''); ?>" required <?= $edit_mode ? 'readonly' : ''; ?>>
            </div>
            <div>
                <label for="nama_kelas">Nama Kelas:</label>
                <input type="text" id="nama_kelas" name="nama_kelas" value="<?= htmlspecialchars($kursus_to_edit['nama_kelas'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="deskripsi_singkat">Deskripsi Singkat:</label>
                <textarea id="deskripsi_singkat" name="deskripsi_singkat"><?= htmlspecialchars($kursus_to_edit['deskripsi_singkat'] ?? ''); ?></textarea>
            </div>
            <div>
                <label for="deskripsi_lengkap">Deskripsi Lengkap:</label>
                <textarea id="deskripsi_lengkap" name="deskripsi_lengkap"><?= htmlspecialchars($kursus_to_edit['deskripsi_lengkap'] ?? ''); ?></textarea>
            </div>
             <div>
                <label for="path_gambar">Gambar Kursus (Kosongkan jika tidak ingin mengubah):</label>
                <?php if ($edit_mode && !empty($kursus_to_edit['path_gambar']) && $kursus_to_edit['path_gambar'] !== 'assets/images/kursus/default.png'): ?>
                    <p><img src="../../<?= htmlspecialchars($kursus_to_edit['path_gambar']); ?>" alt="Gambar saat ini" style="max-width:150px; margin-bottom:10px;"></p>
                <?php endif; ?>
                <input type="file" id="path_gambar" name="path_gambar" accept="image/*">
            </div>
            <div>
                <label for="harga_online">Harga Online (Rp):</label>
                <input type="number" id="harga_online" name="harga_online" step="1000" value="<?= htmlspecialchars($kursus_to_edit['harga_online'] ?? ''); ?>">
            </div>
            <div>
                <label for="harga_offline">Harga Offline (Rp):</label>
                <input type="number" id="harga_offline" name="harga_offline" step="1000" value="<?= htmlspecialchars($kursus_to_edit['harga_offline'] ?? ''); ?>">
            </div>
            <div>
                <label>Tipe Kelas Tersedia:</label>
                <div class="checkbox-group">
                    <?php 
                    $tipe_tersedia_edit = [];
                    if($edit_mode && !empty($kursus_to_edit['tipe_kelas_tersedia'])){
                        $tipe_tersedia_edit = explode(',', $kursus_to_edit['tipe_kelas_tersedia']);
                    }
                    ?>
                    <input type="checkbox" id="tipe_online" name="tipe_kelas_tersedia[]" value="Online" <?= in_array('Online', $tipe_tersedia_edit) ? 'checked' : ''; ?>>
                    <label for="tipe_online">Online</label>
                    <input type="checkbox" id="tipe_offline" name="tipe_kelas_tersedia[]" value="Offline" <?= in_array('Offline', $tipe_tersedia_edit) ? 'checked' : ''; ?>>
                    <label for="tipe_offline">Offline</label>
                </div>
            </div>
            <div>
                <label for="id_mentor">Mentor:</label>
                <select id="id_mentor" name="id_mentor">
                    <option value="">-- Pilih Mentor --</option>
                    <?php foreach ($mentors as $mentor): ?>
                        <option value="<?= htmlspecialchars($mentor['id_mentor']); ?>" <?= (($kursus_to_edit['id_mentor'] ?? '') == $mentor['id_mentor']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($mentor['username']); ?> (<?= htmlspecialchars($mentor['id_mentor']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit"><?= $edit_mode ? 'Update Kursus' : 'Tambah Kursus'; ?></button>
            <?php if ($edit_mode): ?>
                <a href="kelola_kursus.php" class="cancel-edit" style="text-decoration:none; padding: 10px 15px;">Batal Edit</a>
            <?php endif; ?>
        </form>
      </div>

      <div class="table-container">
        <h3>Daftar Kursus yang Ada</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Gambar</th>
                    <th>Harga Online</th>
                    <th>Harga Offline</th>
                    <th>Tipe</th>
                    <th>Mentor</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($hasil_daftar_kursus && mysqli_num_rows($hasil_daftar_kursus) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($hasil_daftar_kursus)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id_kelas']); ?></td>
                        <td><?= htmlspecialchars($row['nama_kelas']); ?></td>
                        <td><img src="../../<?= htmlspecialchars(!empty($row['path_gambar']) ? $row['path_gambar'] : 'assets/images/kursus/default.png'); ?>" alt="<?= htmlspecialchars($row['nama_kelas']); ?>"></td>
                        <td><?= !is_null($row['harga_online']) ? 'Rp ' . number_format($row['harga_online'], 0, ',', '.') : '-'; ?></td>
                        <td><?= !is_null($row['harga_offline']) ? 'Rp ' . number_format($row['harga_offline'], 0, ',', '.') : '-'; ?></td>
                        <td><?= htmlspecialchars($row['tipe_kelas_tersedia'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($row['nama_mentor'] ?? '-'); ?></td>
                        <td>
                            <a href="kelola_kursus.php?edit_id_kelas=<?= htmlspecialchars($row['id_kelas']); ?>">Edit</a>
                            <a href="kelola_kursus.php?hapus_id_kelas=<?= htmlspecialchars($row['id_kelas']); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus kursus ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">Belum ada data kursus.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>
    </main>
  </div>
<script src="../assets/js/script.js"></script>
</body>
</html>