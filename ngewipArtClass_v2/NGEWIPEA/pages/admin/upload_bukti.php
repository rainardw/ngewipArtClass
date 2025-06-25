<?php
session_start();
include '../../database/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../../guest/login.php");
    exit();
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pendaftaran = mysqli_real_escape_string($conn, $_POST['id_pendaftaran']);
    $jumlah_bayar = mysqli_real_escape_string($conn, $_POST['jumlah_bayar']);
    $metode = mysqli_real_escape_string($conn, $_POST['metode_pembayaran'] ?? 'Transfer Bank');
    $id_pembayaran = 'PAY' . date('YmdHis') . rand(100,999);

    $nama_file = $_FILES['bukti']['name'];
    $tmp_file = $_FILES['bukti']['tmp_name'];
    $ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);
    $nama_baru = uniqid('bukti_') . '.' . $ekstensi;

    $target_dir = "../../assets/images/bukti/";
    $file_path_simpan = $target_dir . $nama_baru;
    $path_db = 'assets/images/bukti/' . $nama_baru;

    if (move_uploaded_file($tmp_file, $file_path_simpan)) {
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
        $pesan = "Gagal mengunggah bukti pembayaran. Pastikan file tidak rusak.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Bukti Pembayaran</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body { background: #111; color: #eee; font-family: sans-serif; padding: 30px; }
        .container { max-width: 500px; margin: auto; background: #222; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px #00ffcc55; }
        label, select, input[type="file"] { display: block; margin-bottom: 10px; }
        button { background: #00ffcc; color: #000; padding: 10px 20px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
        button:hover { background: #00e6b2; }
        .error-msg { color: #ff8080; margin-top: 10px; }
    </style>
</head>
<body>
    <button id="modeToggle" style="position: fixed; top: 10px; right: 10px; z-index: 200;">☀️/🌙</button>
<div class="container">
    <h2>Upload Bukti Pembayaran</h2>
    <?php if ($pesan): ?>
        <p class="error-msg"><?= htmlspecialchars($pesan); ?></p>
    <?php endif; ?>
    <form action="upload_bukti.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_pendaftaran" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">
        <input type="hidden" name="jumlah_bayar" value="<?= htmlspecialchars($_GET['jumlah'] ?? '') ?>">

        <label for="bukti">Pilih Bukti Pembayaran (JPG/PNG/PDF):</label>
        <input type="file" name="bukti" id="bukti" accept="image/*,.pdf" required>

        <label for="metode_pembayaran">Metode Pembayaran:</label>
        <select name="metode_pembayaran" id="metode_pembayaran">
            <option value="Transfer Bank">Transfer Bank</option>
            <option value="E-Wallet">E-Wallet</option>
            <option value="QRIS">QRIS</option>
        </select>

        <button type="submit">Upload & Konfirmasi</button>
    </form>
</div>
<script src="../assets/js/script.js"></script>
</body>
</html>
