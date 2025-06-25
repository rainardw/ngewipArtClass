<?php
session_start();
include '../../database/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../../guest/login.php");
    exit();
}

$pesan = '';

// ✅ Ambil data dari GET untuk ditampilkan dan dicek valid tidak
$id_pendaftaran_dari_get = $_GET['id'] ?? '';
$jumlah_bayar_dari_get = $_GET['jumlah'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pendaftaran = mysqli_real_escape_string($conn, $_POST['id_pendaftaran']);
    $jumlah_bayar = mysqli_real_escape_string($conn, $_POST['jumlah_bayar']);
    $metode = mysqli_real_escape_string($conn, $_POST['metode_pembayaran'] ?? 'Transfer Bank');
    $id_pembayaran = 'PAY' . date('YmdHis') . rand(100,999);

    $nama_file = $_FILES['bukti']['name'];
    $tmp_file = $_FILES['bukti']['tmp_name'];

    $target_dir = "../../assets/images/bukti/";
    $file_path_simpan = $target_dir . basename($nama_file);
    $path_db = 'assets/images/bukti/' . basename($nama_file); // path untuk disimpan di DB

    // ✅ Pastikan id_pendaftaran tidak kosong
    if (empty($id_pendaftaran) || empty($jumlah_bayar)) {
        $pesan = "Data tidak lengkap. ID atau jumlah bayar kosong.";
    } elseif (move_uploaded_file($tmp_file, $file_path_simpan)) {
        $query_insert = "INSERT INTO pembayaran 
            (id_pembayaran, id_pendaftaran_kursus, tanggal_bayar, waktu, jumlah_bayar, metode_pembayaran, status, bukti_pembayaran)
            VALUES 
            ('$id_pembayaran', '$id_pendaftaran', CURDATE(), CURTIME(), '$jumlah_bayar', '$metode', 'Pending', '$path_db')";

        if (mysqli_query($conn, $query_insert)) {
            $query_update = "UPDATE pendaftaran_kursus 
                             SET status_pendaftaran = 'Menunggu Verifikasi' 
                             WHERE id_pendaftaran = '$id_pendaftaran'";
            mysqli_query($conn, $query_update);
            header("Location: kursus_saya.php?pesan=sukses");
            exit();
        } else {
            $pesan = "Gagal menyimpan data pembayaran: " . mysqli_error($conn);
        }
    } else {
        $pesan = "Gagal mengunggah bukti pembayaran. Coba cek kembali file-nya.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Bukti Pembayaran</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <button id="modeToggle" style="position: fixed; top: 10px; right: 10px; z-index: 200;">☀️/🌙</button>
<div class="container">
    <h2>Upload Bukti Pembayaran</h2>
    <?php if ($pesan): ?>
        <p style="color:red"><?= htmlspecialchars($pesan); ?></p>
    <?php endif; ?>

    <form action="upload_bukti.php?id=<?= urlencode($id_pendaftaran_dari_get) ?>&jumlah=<?= urlencode($jumlah_bayar_dari_get) ?>" method="POST" enctype="multipart/form-data">
        <!-- Hidden input isi dari GET (pastikan tidak kosong) -->
        <input type="hidden" name="id_pendaftaran" value="<?= htmlspecialchars($id_pendaftaran_dari_get) ?>">
        <input type="hidden" name="jumlah_bayar" value="<?= htmlspecialchars($jumlah_bayar_dari_get) ?>">

        <label for="bukti">Pilih Bukti Pembayaran (Gambar):</label><br>
        <input type="file" name="bukti" id="bukti" accept="image/*" required><br><br>

        <label for="metode_pembayaran">Metode Pembayaran:</label><br>
        <select name="metode_pembayaran" id="metode_pembayaran">
            <option value="Transfer Bank">Transfer Bank</option>
            <option value="E-Wallet">E-Wallet</option>
            <option value="QRIS">QRIS</option>
        </select><br><br>

        <button type="submit">Upload & Konfirmasi</button>
    </form>
</div>
<script src="../assets/js/script.js"></script>
</body>
</html>
