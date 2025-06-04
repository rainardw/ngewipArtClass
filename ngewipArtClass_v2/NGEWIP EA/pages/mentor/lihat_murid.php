<?php
session_start();
include '../../database/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'mentor') {
    header("Location: ../../guest/login.php");
    exit();
}

$id_mentor_login = $_SESSION['user_id']; // Asumsikan user_id untuk mentor adalah id_mentor
$murid_di_kelas_mentor = [];

// Ambil kelas yang diampu mentor
$query_kelas_mentor = "SELECT id_kelas, nama_kelas FROM kelas_seni WHERE id_mentor = ?";
$stmt_kelas = mysqli_prepare($conn, $query_kelas_mentor);
mysqli_stmt_bind_param($stmt_kelas, "s", $id_mentor_login);
mysqli_stmt_execute($stmt_kelas);
$hasil_kelas_mentor = mysqli_stmt_get_result($stmt_kelas);

$kelas_ids = [];
$nama_kelas_map = [];
if ($hasil_kelas_mentor && mysqli_num_rows($hasil_kelas_mentor) > 0) {
    while($row_kelas = mysqli_fetch_assoc($hasil_kelas_mentor)){
        $kelas_ids[] = $row_kelas['id_kelas'];
        $nama_kelas_map[$row_kelas['id_kelas']] = $row_kelas['nama_kelas'];
    }
}
mysqli_stmt_close($stmt_kelas);

if (!empty($kelas_ids)) {
    // Ubah array id kelas menjadi string yang dipisahkan koma untuk klausa IN
    $placeholders = implode(',', array_fill(0, count($kelas_ids), '?'));
    $types = str_repeat('s', count($kelas_ids)); // tipe string untuk setiap id_kelas

    $query_murid = "SELECT m.username AS nama_murid, m.email AS email_murid, 
                           pk.id_kelas, pk.tipe_yang_dipilih, pk.tanggal_pendaftaran, pk.status_pendaftaran
                    FROM pendaftaran_kursus pk
                    JOIN member m ON pk.id_member = m.id_member
                    WHERE pk.id_kelas IN ($placeholders) AND pk.status_pendaftaran = 'Aktif' -- Hanya tampilkan murid aktif
                    ORDER BY pk.id_kelas, m.username ASC";
    
    $stmt_murid = mysqli_prepare($conn, $query_murid);
    // Bind parameter secara dinamis
    mysqli_stmt_bind_param($stmt_murid, $types, ...$kelas_ids);
    mysqli_stmt_execute($stmt_murid);
    $hasil_murid = mysqli_stmt_get_result($stmt_murid);

    if ($hasil_murid && mysqli_num_rows($hasil_murid) > 0) {
        while ($row_murid = mysqli_fetch_assoc($hasil_murid)) {
            // Tambahkan nama kelas ke hasil
            $row_murid['nama_kelas'] = $nama_kelas_map[$row_murid['id_kelas']] ?? 'Tidak Diketahui';
            $murid_di_kelas_mentor[] = $row_murid;
        }
    }
    mysqli_stmt_close($stmt_murid);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Lihat Murid - Mentor ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../../assets/css/mentor.css" />
  <style>
    /* Gaya CSS dari kelola_kursus.php bisa digunakan sebagian */
    .table-container { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; color: #ccc; font-size:0.9em;}
    table th, table td { border: 1px solid #444; padding: 8px; text-align: left; }
    table th { background-color: #333; color: #00ffcc; }
    .no-data { text-align: center; color: #aaa; margin-top: 20px; font-size: 1.1em;}
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
        <a href="lihat_murid.php" class="active">Lihat Murid</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header>
        <h1>Daftar Murid di Kelas Anda</h1>
      </header>

      <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nama Murid</th>
                    <th>Email Murid</th>
                    <th>Nama Kelas</th>
                    <th>Tipe Kelas</th>
                    <th>Tanggal Daftar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($murid_di_kelas_mentor)): ?>
                    <?php foreach($murid_di_kelas_mentor as $murid): ?>
                    <tr>
                        <td><?= htmlspecialchars($murid['nama_murid']); ?></td>
                        <td><?= htmlspecialchars($murid['email_murid']); ?></td>
                        <td><?= htmlspecialchars($murid['nama_kelas']); ?></td>
                        <td><?= htmlspecialchars($murid['tipe_yang_dipilih']); ?></td>
                        <td><?= htmlspecialchars(date('d M Y', strtotime($murid['tanggal_pendaftaran']))); ?></td>
                        <td><?= htmlspecialchars($murid['status_pendaftaran']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="no-data">Belum ada murid aktif di kelas yang Anda ampu atau Anda belum mengampu kelas apapun.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>