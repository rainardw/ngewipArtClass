<?php
session_start();
include '../../database/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../guest/login.php");
    exit();
}

$pesan = '';

// Logika untuk verifikasi atau tolak pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verifikasi_pembayaran'])) {
        $id_pembayaran = mysqli_real_escape_string($conn, $_POST['id_pembayaran']);
        $id_pendaftaran = mysqli_real_escape_string($conn, $_POST['id_pendaftaran']);
        $id_admin_verif = $_SESSION['user_id'];

        $query_update_bayar = "UPDATE pembayaran SET status = 'Lunas', verifikasi_admin_oleh = '$id_admin_verif', tanggal_verifikasi = NOW()
                               WHERE id_pembayaran = '$id_pembayaran' AND status = 'Pending'";

        if (mysqli_query($conn, $query_update_bayar) && mysqli_affected_rows($conn) > 0) {
            $query_update_daftar = "UPDATE pendaftaran_kursus SET status_pendaftaran = 'Aktif' 
                                    WHERE id_pendaftaran = '$id_pendaftaran' AND status_pendaftaran = 'Menunggu Verifikasi'";
            if (mysqli_query($conn, $query_update_daftar)) {
                $pesan = '<p class="pesan sukses">Pembayaran berhasil diverifikasi dan pendaftaran diaktifkan.</p>';
            } else {
                $pesan = '<p class="pesan error">Pembayaran diverifikasi, tapi gagal update status pendaftaran: '.mysqli_error($conn).'</p>';
            }
        } else {
            $pesan = '<p class="pesan error">Gagal verifikasi pembayaran atau pembayaran tidak ditemukan: ' . mysqli_error($conn) . '</p>';
        }

    } elseif (isset($_POST['tolak_pembayaran'])) {
        $id_pembayaran = mysqli_real_escape_string($conn, $_POST['id_pembayaran']);
        $id_pendaftaran = mysqli_real_escape_string($conn, $_POST['id_pendaftaran']);
        $id_admin_verif = $_SESSION['user_id'];

        $query_update_bayar = "UPDATE pembayaran SET status = 'Ditolak', verifikasi_admin_oleh = '$id_admin_verif', tanggal_verifikasi = NOW()
                               WHERE id_pembayaran = '$id_pembayaran' AND status = 'Pending'";

        if (mysqli_query($conn, $query_update_bayar) && mysqli_affected_rows($conn) > 0) {
            $query_update_daftar = "UPDATE pendaftaran_kursus SET status_pendaftaran = 'Dibatalkan' 
                                    WHERE id_pendaftaran = '$id_pendaftaran' AND status_pendaftaran = 'Menunggu Verifikasi'";
            mysqli_query($conn, $query_update_daftar);
            $pesan = '<p class="pesan sukses">Pembayaran berhasil ditolak.</p>';
        } else {
            $pesan = '<p class="pesan error">Gagal menolak pembayaran atau data tidak ditemukan: ' . mysqli_error($conn) . '</p>';
        }
    }
}

// ❗ FIXED QUERY: Ambil hanya yang status 'Pending' (menunggu verifikasi)
$query_verifikasi = "SELECT p.id_pembayaran, pk.id_pendaftaran, m.username AS nama_member, m.email AS email_member,
                            ks.nama_kelas, pk.tipe_yang_dipilih, p.jumlah_bayar, p.metode_pembayaran, p.tanggal_bayar, p.waktu, p.bukti_pembayaran, p.status AS status_pembayaran_db
                     FROM pembayaran p
                     JOIN pendaftaran_kursus pk ON p.id_pendaftaran_kursus = pk.id_pendaftaran
                     JOIN member m ON pk.id_member = m.id_member
                     JOIN kelas_seni ks ON pk.id_kelas = ks.id_kelas
                     WHERE p.status = 'Pending'
                     ORDER BY p.tanggal_bayar DESC, p.waktu DESC";

$hasil_verifikasi = mysqli_query($conn, $query_verifikasi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Verifikasi Pembayaran - Admin ngeWIP ArtClass</title>
  <script>
  if (localStorage.getItem("theme") === "light") {
    document.documentElement.classList.add("light-mode");
  }
</script>
  <link rel="stylesheet" href="../../assets/css/admin.css" />
  <style>
    .table-container { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; color: #ccc; font-size: 0.9em;}
    table th, table td { border: 1px solid #444; padding: 8px; text-align: left; }
    table th { background-color: #333; color: #00ffcc; }
    table td img.bukti-bayar { max-width: 150px; max-height:150px; height: auto; border-radius: 4px; cursor:pointer; }
    table td form { display: inline-block; margin: 0 5px;}
    table td button {
        padding: 5px 10px; font-size:0.85em; background-color: #00ffcc; color: #111;
        border: none; border-radius: 4px; cursor: pointer; font-weight: bold;
    }
    table td button.tolak { background-color: #dc3545; color: #fff;}
    .pesan { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align:center; }
    .pesan.sukses { background-color: #28a74533; color: #a3ffb8; border: 1px solid #28a74588; }
    .pesan.error { background-color: #dc354533; color: #ffacb3; border: 1px solid #dc354588; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.8); }
    .modal-content { margin: 5% auto; display: block; max-width: 80%; max-height: 85%; }
    .close-modal { position: absolute; top: 15px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; transition: 0.3s; }
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
        <a href="verifikasi_pembayaran.php" class="active">Verifikasi Pembayaran</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header>
        <button id="modeToggle" style="position: fixed; top: 10px; right: 10px; z-index: 200;">☀️/🌙</button>
        <h1>Verifikasi Pembayaran Kursus</h1>
      </header>

      <?= $pesan; ?>

      <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID Bayar</th>
                    <th>ID Daftar</th>
                    <th>Member</th>
                    <th>Email</th>
                    <th>Kursus</th>
                    <th>Tipe</th>
                    <th>Jumlah</th>
                    <th>Metode</th>
                    <th>Tgl Bayar</th>
                    <th>Bukti</th>
                    <th>Status Bayar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($hasil_verifikasi && mysqli_num_rows($hasil_verifikasi) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($hasil_verifikasi)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id_pembayaran']); ?></td>
                        <td><?= htmlspecialchars($row['id_pendaftaran']); ?></td>
                        <td><?= htmlspecialchars($row['nama_member']); ?></td>
                        <td><?= htmlspecialchars($row['email_member']); ?></td>
                        <td><?= htmlspecialchars($row['nama_kelas']); ?></td>
                        <td><?= htmlspecialchars($row['tipe_yang_dipilih']); ?></td>
                        <td><?= 'Rp ' . number_format($row['jumlah_bayar'], 0, ',', '.'); ?></td>
                        <td><?= htmlspecialchars($row['metode_pembayaran']); ?></td>
                        <td><?= date('d M Y', strtotime($row['tanggal_bayar'])); ?></td>
                        <td>
                            <img src="../../<?= htmlspecialchars($row['bukti_pembayaran']); ?>" alt="Bukti" class="bukti-bayar" onclick="showModalImage(this.src)">
                        </td>
                        <td><?= htmlspecialchars($row['status_pembayaran_db']); ?></td>
                        <td>
                            <form action="verifikasi_pembayaran.php" method="POST">
                                <input type="hidden" name="id_pembayaran" value="<?= $row['id_pembayaran']; ?>">
                                <input type="hidden" name="id_pendaftaran" value="<?= $row['id_pendaftaran']; ?>">
                                <button type="submit" name="verifikasi_pembayaran">Verifikasi</button>
                            </form>
                            <form action="verifikasi_pembayaran.php" method="POST">
                                <input type="hidden" name="id_pembayaran" value="<?= $row['id_pembayaran']; ?>">
                                <input type="hidden" name="id_pendaftaran" value="<?= $row['id_pendaftaran']; ?>">
                                <button type="submit" name="tolak_pembayaran" class="tolak">Tolak</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="12">Tidak ada pembayaran yang perlu diverifikasi saat ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>
    </main>
  </div>

  <div id="imageModal" class="modal">
    <span class="close-modal" onclick="document.getElementById('imageModal').style.display='none'">&times;</span>
    <img class="modal-content" id="modalImageContent">
  </div>

<script>
function showModalImage(src) {
    document.getElementById('imageModal').style.display = "block";
    document.getElementById('modalImageContent').src = src;
}
window.onclick = function(event) {
    if (event.target == document.getElementById('imageModal')) {
        document.getElementById('imageModal').style.display = "none";
    }
}
<script src="../assets/js/script.js"></script>
</body>
</html>
