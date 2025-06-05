<?php
session_start();
include '../../database/db.php';

// Pastikan yang login adalah mentor
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'mentor') {
    header("Location: ../../guest/login.php");
    exit();
}

$id_mentor_login = $_SESSION['user_id'];
$pesan = '';
$edit_mode_materi = false;
$materi_to_edit = null;
$selected_id_kelas = null;
$nama_kelas_dipilih = '';
$daftar_materi_kelas = [];

// Ambil daftar kursus yang diampu oleh mentor ini
$kursus_mentor = [];
$query_kursus_ampu = "SELECT id_kelas, nama_kelas FROM kelas_seni WHERE id_mentor = ? ORDER BY nama_kelas ASC";
$stmt_kursus_ampu = mysqli_prepare($conn, $query_kursus_ampu);
mysqli_stmt_bind_param($stmt_kursus_ampu, "s", $id_mentor_login);
mysqli_stmt_execute($stmt_kursus_ampu);
$hasil_kursus_ampu = mysqli_stmt_get_result($stmt_kursus_ampu);
if ($hasil_kursus_ampu) {
    while ($row = mysqli_fetch_assoc($hasil_kursus_ampu)) {
        $kursus_mentor[] = $row;
    }
}
mysqli_stmt_close($stmt_kursus_ampu);

// Jika ada id_kelas yang dipilih (misalnya dari form atau GET parameter)
if (isset($_REQUEST['id_kelas_pilihan'])) { // Bisa dari GET atau POST
    $selected_id_kelas = mysqli_real_escape_string($conn, $_REQUEST['id_kelas_pilihan']);

    // Validasi apakah mentor ini benar mengampu kelas tersebut
    $is_mentor_of_class = false;
    foreach ($kursus_mentor as $km) {
        if ($km['id_kelas'] === $selected_id_kelas) {
            $is_mentor_of_class = true;
            $nama_kelas_dipilih = $km['nama_kelas'];
            break;
        }
    }

    if ($is_mentor_of_class) {
        // Logika untuk menangani form Tambah/Edit Materi
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_materi'])) {
            $judul_materi = mysqli_real_escape_string($conn, $_POST['judul_materi']);
            $deskripsi_materi = mysqli_real_escape_string($conn, $_POST['deskripsi_materi']);
            $konten_materi_input = $_POST['konten_materi']; // Tidak langsung escape, tergantung tipe
            $tipe_konten = mysqli_real_escape_string($conn, $_POST['tipe_konten']);
            $urutan = (int)($_POST['urutan'] ?? 0);
            $id_materi_edit = $_POST['id_materi_edit'] ?? null;

            $konten_final = $konten_materi_input; // Default

            if ($tipe_konten === 'File' && isset($_FILES['file_konten']) && $_FILES['file_konten']['error'] == 0) {
                $target_dir_materi = "../../assets/materi/"; // Pastikan folder ini ada dan writable
                if (!is_dir($target_dir_materi)) {
                    mkdir($target_dir_materi, 0777, true);
                }
                $file_name = time() . '_' . basename($_FILES["file_konten"]["name"]);
                $target_file_materi = $target_dir_materi . $file_name;
                
                if (move_uploaded_file($_FILES["file_konten"]["tmp_name"], $target_file_materi)) {
                    $konten_final = "assets/materi/" . $file_name; // Simpan path relatif
                } else {
                    $pesan = '<p class="pesan error">Gagal mengupload file materi.</p>';
                    $konten_final = null; // Tandai gagal upload
                }
            } elseif ($tipe_konten === 'File' && !empty($id_materi_edit) && empty($_FILES['file_konten']['name'])) {
                // Jika edit dan tidak ada file baru diupload, gunakan path file lama
                $q_old_file = mysqli_prepare($conn, "SELECT konten_materi FROM materi_pelajaran WHERE id_materi = ? AND tipe_konten = 'File'");
                mysqli_stmt_bind_param($q_old_file, "i", $id_materi_edit);
                mysqli_stmt_execute($q_old_file);
                $res_old_file = mysqli_stmt_get_result($q_old_file);
                if ($r_old_file = mysqli_fetch_assoc($res_old_file)) {
                    $konten_final = $r_old_file['konten_materi'];
                }
                mysqli_stmt_close($q_old_file);
            }


            if ($konten_final !== null || $tipe_konten !== 'File') { // Hanya proses jika upload file berhasil atau bukan tipe file
                if (!empty($id_materi_edit)) {
                    // Mode Edit Materi
                    $query_update_materi = "UPDATE materi_pelajaran SET judul_materi = ?, deskripsi_materi = ?, konten_materi = ?, tipe_konten = ?, urutan = ? 
                                            WHERE id_materi = ? AND id_kelas = ?";
                    $stmt_update = mysqli_prepare($conn, $query_update_materi);
                    mysqli_stmt_bind_param($stmt_update, "ssssiss", $judul_materi, $deskripsi_materi, $konten_final, $tipe_konten, $urutan, $id_materi_edit, $selected_id_kelas);
                    if (mysqli_stmt_execute($stmt_update)) {
                        $pesan = '<p class="pesan sukses">Materi berhasil diperbarui.</p>';
                    } else {
                        $pesan = '<p class="pesan error">Gagal memperbarui materi: ' . mysqli_stmt_error($stmt_update) . '</p>';
                    }
                    mysqli_stmt_close($stmt_update);
                } else {
                    // Mode Tambah Materi
                    $query_insert_materi = "INSERT INTO materi_pelajaran (id_kelas, judul_materi, deskripsi_materi, konten_materi, tipe_konten, urutan) 
                                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_insert = mysqli_prepare($conn, $query_insert_materi);
                    mysqli_stmt_bind_param($stmt_insert, "sssssi", $selected_id_kelas, $judul_materi, $deskripsi_materi, $konten_final, $tipe_konten, $urutan);
                    if (mysqli_stmt_execute($stmt_insert)) {
                        $pesan = '<p class="pesan sukses">Materi baru berhasil ditambahkan.</p>';
                    } else {
                        $pesan = '<p class="pesan error">Gagal menambahkan materi: ' . mysqli_stmt_error($stmt_insert) . '</p>';
                    }
                    mysqli_stmt_close($stmt_insert);
                }
            }
            // Reset mode edit setelah submit
            $edit_mode_materi = false;
            $materi_to_edit = null;
        }

        // Logika untuk Hapus Materi
        if (isset($_GET['hapus_id_materi']) && $_GET['id_kelas_pilihan'] === $selected_id_kelas) {
            $id_materi_hapus = (int)$_GET['hapus_id_materi'];
            
            // Optional: Hapus file fisik jika tipe kontennya 'File'
            $q_check_file = mysqli_prepare($conn, "SELECT konten_materi, tipe_konten FROM materi_pelajaran WHERE id_materi = ? AND id_kelas = ?");
            mysqli_stmt_bind_param($q_check_file, "is", $id_materi_hapus, $selected_id_kelas);
            mysqli_stmt_execute($q_check_file);
            $res_check_file = mysqli_stmt_get_result($q_check_file);
            if($r_file = mysqli_fetch_assoc($res_check_file)){
                if($r_file['tipe_konten'] === 'File' && !empty($r_file['konten_materi']) && file_exists("../../".$r_file['konten_materi'])){
                    unlink("../../".$r_file['konten_materi']);
                }
            }
            mysqli_stmt_close($q_check_file);

            $query_hapus_materi = "DELETE FROM materi_pelajaran WHERE id_materi = ? AND id_kelas = ?";
            $stmt_hapus = mysqli_prepare($conn, $query_hapus_materi);
            mysqli_stmt_bind_param($stmt_hapus, "is", $id_materi_hapus, $selected_id_kelas);
            if (mysqli_stmt_execute($stmt_hapus)) {
                $pesan = '<p class="pesan sukses">Materi berhasil dihapus.</p>';
            } else {
                $pesan = '<p class="pesan error">Gagal menghapus materi: ' . mysqli_stmt_error($stmt_hapus) . '</p>';
            }
            mysqli_stmt_close($stmt_hapus);
        }

        // Logika untuk Mode Edit Materi (mengisi form)
        if (isset($_GET['edit_id_materi']) && $_GET['id_kelas_pilihan'] === $selected_id_kelas) {
            $id_materi_edit = (int)$_GET['edit_id_materi'];
            $query_edit_materi = "SELECT * FROM materi_pelajaran WHERE id_materi = ? AND id_kelas = ?";
            $stmt_edit_materi = mysqli_prepare($conn, $query_edit_materi);
            mysqli_stmt_bind_param($stmt_edit_materi, "is", $id_materi_edit, $selected_id_kelas);
            mysqli_stmt_execute($stmt_edit_materi);
            $hasil_edit_materi = mysqli_stmt_get_result($stmt_edit_materi);
            if ($hasil_edit_materi && mysqli_num_rows($hasil_edit_materi) > 0) {
                $materi_to_edit = mysqli_fetch_assoc($hasil_edit_materi);
                $edit_mode_materi = true;
            }
            mysqli_stmt_close($stmt_edit_materi);
        }

        // Ambil daftar materi untuk kelas yang dipilih
        $query_daftar_materi = "SELECT * FROM materi_pelajaran WHERE id_kelas = ? ORDER BY urutan ASC, id_materi ASC";
        $stmt_daftar_materi = mysqli_prepare($conn, $query_daftar_materi);
        mysqli_stmt_bind_param($stmt_daftar_materi, "s", $selected_id_kelas);
        mysqli_stmt_execute($stmt_daftar_materi);
        $hasil_daftar_materi = mysqli_stmt_get_result($stmt_daftar_materi);
        if ($hasil_daftar_materi) {
            while ($row = mysqli_fetch_assoc($hasil_daftar_materi)) {
                $daftar_materi_kelas[] = $row;
            }
        }
        mysqli_stmt_close($stmt_daftar_materi);
    } else {
        $pesan = "<p class='pesan error'>Anda tidak memiliki hak akses untuk mengelola materi kelas ini.</p>";
        $selected_id_kelas = null; // Reset jika tidak berhak
        $nama_kelas_dipilih = '';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kelola Materi Kursus Saya - Mentor</title>
  <link rel="stylesheet" href="../../assets/css/mentor.css" />
  <style>
    /* Anda bisa menggunakan beberapa style dari admin.css atau buat style khusus */
    .main-content { padding: 20px; }
    .form-section, .list-section { background-color: #2b2b2b; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    h3, h4 { color: #00ffcc; }
    label { display: block; margin-bottom: 5px; color: #ccc; }
    input[type="text"], input[type="number"], textarea, select, input[type="file"] {
        width: calc(100% - 22px); padding: 10px; margin-bottom: 10px;
        border-radius: 4px; border: 1px solid #444; background-color: #333; color: #fff;
    }
    textarea { min-height: 100px; }
    button {
        padding: 10px 15px; background-color: #00ffcc; color: #111;
        border: none; border-radius: 4px; cursor: pointer; font-weight: bold;
    }
    button:hover { background-color: #00e6b2; }
    .cancel-edit-materi { background-color: #777; color: #fff; margin-left:10px; text-decoration:none; padding: 10px 15px; border-radius:4px; }
    .cancel-edit-materi:hover { background-color: #555; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; color: #ccc; font-size:0.9em; }
    table th, table td { border: 1px solid #444; padding: 8px; text-align: left; }
    table th { background-color: #333; color: #00ffcc; }
    table td a { color: #00ffcc; text-decoration: none; margin-right: 8px; }
    table td a:hover { text-decoration: underline; }
    .pesan { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align:center; }
    .pesan.sukses { background-color: #28a74533; color: #a3ffb8; border: 1px solid #28a74588; }
    .pesan.error { background-color: #dc354533; color: #ffacb3; border: 1px solid #dc354588; }
  </style>
</head>
<body>
  <div class="mentor-container">
    <aside class="sidebar">
      <img src="../../assets/images/logo.png" class="logo" alt="Logo" />
      <h2>Mentor Panel</h2>
      <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="live-class.php">Live Class</a>
        <a href="lihat_murid.php">Lihat Murid</a>
        <a href="kelola_materi_saya.php" class="active">Kelola Materi Saya</a>
        <a href="../../pages/galery_karya.php">Galery Karya</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header>
        <h1>Kelola Materi Kursus Saya</h1>
      </header>

      <?= $pesan; ?>

      <div class="form-section">
        <h3>Pilih Kursus untuk Dikelola Materinya</h3>
        <form action="kelola_materi_saya.php" method="GET">
            <label for="id_kelas_pilihan">Pilih Kursus:</label>
            <select name="id_kelas_pilihan" id="id_kelas_pilihan" onchange="this.form.submit()" required>
                <option value="">-- Pilih Kursus --</option>
                <?php foreach($kursus_mentor as $km): ?>
                    <option value="<?= htmlspecialchars($km['id_kelas']); ?>" <?= ($selected_id_kelas === $km['id_kelas']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($km['nama_kelas']); ?> (<?= htmlspecialchars($km['id_kelas']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
      </div>

      <?php if ($selected_id_kelas && $is_mentor_of_class): // Hanya tampilkan form dan daftar materi jika kursus valid dipilih ?>
        <div class="form-section">
            <h4><?= $edit_mode_materi ? 'Edit Materi untuk: ' : 'Tambah Materi Baru untuk: '; ?> <?= htmlspecialchars($nama_kelas_dipilih); ?></h4>
            <form action="kelola_materi_saya.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_kelas_pilihan" value="<?= htmlspecialchars($selected_id_kelas); ?>">
                <?php if ($edit_mode_materi && $materi_to_edit): ?>
                    <input type="hidden" name="id_materi_edit" value="<?= htmlspecialchars($materi_to_edit['id_materi']); ?>">
                <?php endif; ?>

                <div>
                    <label for="judul_materi">Judul Materi:</label>
                    <input type="text" id="judul_materi" name="judul_materi" value="<?= htmlspecialchars($materi_to_edit['judul_materi'] ?? ''); ?>" required>
                </div>
                <div>
                    <label for="deskripsi_materi">Deskripsi Singkat Materi (Opsional):</label>
                    <textarea id="deskripsi_materi" name="deskripsi_materi"><?= htmlspecialchars($materi_to_edit['deskripsi_materi'] ?? ''); ?></textarea>
                </div>
                <div>
                    <label for="tipe_konten">Tipe Konten:</label>
                    <select name="tipe_konten" id="tipe_konten" required onchange="toggleKontenInput(this.value)">
                        <option value="Teks" <?= (($materi_to_edit['tipe_konten'] ?? '') === 'Teks') ? 'selected' : ''; ?>>Teks</option>
                        <option value="VideoEmbed" <?= (($materi_to_edit['tipe_konten'] ?? '') === 'VideoEmbed') ? 'selected' : ''; ?>>Video Embed (HTML)</option>
                        <option value="Link" <?= (($materi_to_edit['tipe_konten'] ?? '') === 'Link') ? 'selected' : ''; ?>>Link Eksternal</option>
                        <option value="File" <?= (($materi_to_edit['tipe_konten'] ?? '') === 'File') ? 'selected' : ''; ?>>Upload File</option>
                    </select>
                </div>
                <div id="konten_input_area">
                    <?php
                    $konten_value = $materi_to_edit['konten_materi'] ?? '';
                    $tipe_konten_edit = $materi_to_edit['tipe_konten'] ?? 'Teks';
                    
                    if ($tipe_konten_edit === 'Teks' || $tipe_konten_edit === 'VideoEmbed') {
                        echo '<div><label for="konten_materi_text">Konten (Teks/HTML Embed):</label><textarea id="konten_materi_text" name="konten_materi">' . htmlspecialchars($konten_value) . '</textarea></div>';
                    } elseif ($tipe_konten_edit === 'Link') {
                        echo '<div><label for="konten_materi_link">URL Link:</label><input type="url" id="konten_materi_link" name="konten_materi" value="' . htmlspecialchars($konten_value) . '"></div>';
                    } elseif ($tipe_konten_edit === 'File') {
                        echo '<div><label for="file_konten">Upload File Materi Baru (Kosongkan jika tidak ingin mengubah file):</label><input type="file" id="file_konten" name="file_konten" accept=".pdf,.doc,.docx,.zip,.jpg,.png"></div>';
                        if ($edit_mode_materi && !empty($konten_value)) {
                             echo '<p style="font-size:0.8em; color:#ccc;">File saat ini: <a href="../../'.htmlspecialchars($konten_value).'" target="_blank">'.basename($konten_value).'</a></p>';
                        }
                    }
                    ?>
                </div>
                 <div>
                    <label for="urutan">Urutan Tampil (Angka, opsional):</label>
                    <input type="number" id="urutan" name="urutan" value="<?= htmlspecialchars($materi_to_edit['urutan'] ?? '0'); ?>" min="0">
                </div>
                <button type="submit" name="submit_materi"><?= $edit_mode_materi ? 'Update Materi' : 'Tambah Materi'; ?></button>
                <?php if ($edit_mode_materi): ?>
                    <a href="kelola_materi_saya.php?id_kelas_pilihan=<?= htmlspecialchars($selected_id_kelas); ?>" class="cancel-edit-materi">Batal Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="list-section">
            <h4>Daftar Materi untuk: <?= htmlspecialchars($nama_kelas_dipilih); ?></h4>
            <table>
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Judul Materi</th>
                        <th>Tipe Konten</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($daftar_materi_kelas)): ?>
                        <?php foreach($daftar_materi_kelas as $materi): ?>
                        <tr>
                            <td><?= htmlspecialchars($materi['urutan']); ?></td>
                            <td><?= htmlspecialchars($materi['judul_materi']); ?></td>
                            <td><?= htmlspecialchars($materi['tipe_konten']); ?></td>
                            <td>
                                <a href="kelola_materi_saya.php?id_kelas_pilihan=<?= htmlspecialchars($selected_id_kelas); ?>&edit_id_materi=<?= $materi['id_materi']; ?>">Edit</a>
                                <a href="kelola_materi_saya.php?id_kelas_pilihan=<?= htmlspecialchars($selected_id_kelas); ?>&hapus_id_materi=<?= $materi['id_materi']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus materi ini?');">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">Belum ada materi untuk kursus ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
      <?php elseif(isset($_REQUEST['id_kelas_pilihan']) && !$is_mentor_of_class && !empty($_REQUEST['id_kelas_pilihan'])): ?>
         <p class="pesan error">Anda tidak berhak mengelola materi untuk kelas yang dipilih.</p>
      <?php elseif(empty($kursus_mentor)): ?>
         <p class="pesan error">Anda belum ditugaskan untuk mengampu kelas apapun.</p>
      <?php endif; ?>
    </main>
  </div>
<script>
function toggleKontenInput(tipe) {
    const area = document.getElementById('konten_input_area');
    let html = '';
    let currentValue = ''; // Ambil dari input yang sudah ada jika perlu saat edit

    // Jika dalam mode edit dan tipe berubah, kita mungkin ingin mengosongkan nilai atau mengambil nilai lama jika sesuai
    <?php if ($edit_mode_materi && $materi_to_edit): ?>
    const materiEdit = <?= json_encode($materi_to_edit); ?>;
    if (tipe === materiEdit.tipe_konten) {
        currentValue = materiEdit.konten_materi;
    }
    <?php endif; ?>

    if (tipe === 'Teks' || tipe === 'VideoEmbed') {
        html = '<div><label for="konten_materi_text">Konten (Teks/HTML Embed):</label><textarea id="konten_materi_text" name="konten_materi">' + currentValue + '</textarea></div>';
    } else if (tipe === 'Link') {
        html = '<div><label for="konten_materi_link">URL Link:</label><input type="url" id="konten_materi_link" name="konten_materi" value="' + currentValue + '"></div>';
    } else if (tipe === 'File') {
        html = '<div><label for="file_konten">Upload File Materi:</label><input type="file" id="file_konten" name="file_konten" accept=".pdf,.doc,.docx,.zip,.jpg,.png"></div>';
        <?php if ($edit_mode_materi && $materi_to_edit && $materi_to_edit['tipe_konten'] === 'File' && !empty($materi_to_edit['konten_materi'])): ?>
        if (tipe === materiEdit.tipe_konten) { // Hanya tampilkan file lama jika tipenya masih File
            html += '<p style="font-size:0.8em; color:#ccc;">File saat ini: <a href="../../<?= htmlspecialchars(str_replace("'", "\\'", $materi_to_edit['konten_materi'])); ?>" target="_blank"><?= basename(htmlspecialchars(str_replace("'", "\\'", $materi_to_edit['konten_materi']))); ?></a> (Kosongkan pilihan file di atas jika tidak ingin mengubah)</p>';
        }
        <?php endif; ?>
    }
    area.innerHTML = html;
}

// Panggil saat load jika dalam mode edit untuk menampilkan input yang sesuai
<?php if ($edit_mode_materi && $materi_to_edit): ?>
document.addEventListener('DOMContentLoaded', function() {
    toggleKontenInput(document.getElementById('tipe_konten').value);
});
<?php else: ?>
// Panggil saat load untuk pertama kali jika bukan mode edit
document.addEventListener('DOMContentLoaded', function() {
    if(document.getElementById('tipe_konten')) { // Pastikan elemen ada
       toggleKontenInput(document.getElementById('tipe_konten').value);
    }
});
<?php endif; ?>
</script>
</body>
</html>