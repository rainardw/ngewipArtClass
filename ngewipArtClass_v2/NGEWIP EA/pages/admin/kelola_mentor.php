<?php
session_start();
include '../../database/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../guest/login.php");
    exit();
}
$pesan = '';
$edit_mode = false;
$user_to_edit = null;

// Logika Edit Mentor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_mentor'])) {
    $id_mentor_edit = mysqli_real_escape_string($conn, $_POST['id_mentor_edit']);
    $username_edit = mysqli_real_escape_string($conn, trim($_POST['username_edit']));
    $email_edit = mysqli_real_escape_string($conn, trim($_POST['email_edit']));
    $password_edit_plain = trim($_POST['password_edit']);

    if (empty($username_edit) || empty($email_edit)) {
        $pesan = '<p class="pesan error">Username dan Email tidak boleh kosong.</p>';
    } elseif (!filter_var($email_edit, FILTER_VALIDATE_EMAIL)) {
        $pesan = '<p class="pesan error">Format email tidak valid.</p>';
    } else {
        $cek_duplikat = "SELECT * FROM mentor WHERE (username = ? OR email = ?) AND id_mentor != ?";
        $stmt_cek = mysqli_prepare($conn, $cek_duplikat);
        mysqli_stmt_bind_param($stmt_cek, "sss", $username_edit, $email_edit, $id_mentor_edit);
        mysqli_stmt_execute($stmt_cek);
        $hasil_cek = mysqli_stmt_get_result($stmt_cek);

        if (mysqli_num_rows($hasil_cek) > 0) {
            $existing = mysqli_fetch_assoc($hasil_cek);
            if ($existing['username'] === $username_edit) $pesan = '<p class="pesan error">Username sudah digunakan oleh mentor lain.</p>';
            else $pesan = '<p class="pesan error">Email sudah digunakan oleh mentor lain.</p>';
        } else {
            if (!empty($password_edit_plain)) {
                if(strlen($password_edit_plain) < 6) {
                     $pesan = '<p class="pesan error">Password baru minimal 6 karakter.</p>';
                } else {
                    $query_update = "UPDATE mentor SET username = ?, email = ?, password = ? WHERE id_mentor = ?";
                    $stmt_update = mysqli_prepare($conn, $query_update);
                    mysqli_stmt_bind_param($stmt_update, "ssss", $username_edit, $email_edit, $password_edit_plain, $id_mentor_edit);
                }
            } else {
                $query_update = "UPDATE mentor SET username = ?, email = ? WHERE id_mentor = ?";
                $stmt_update = mysqli_prepare($conn, $query_update);
                mysqli_stmt_bind_param($stmt_update, "sss", $username_edit, $email_edit, $id_mentor_edit);
            }
            
            if(empty($pesan)){
                if (mysqli_stmt_execute($stmt_update)) {
                    $pesan = '<p class="pesan sukses">Data mentor berhasil diperbarui.</p>';
                } else {
                    $pesan = '<p class="pesan error">Gagal memperbarui data mentor: ' . mysqli_stmt_error($stmt_update) . '</p>';
                }
                mysqli_stmt_close($stmt_update);
            }
        }
        mysqli_stmt_close($stmt_cek);
    }
    $edit_mode = false;
    $user_to_edit = null;
}

// Logika Hapus Mentor
if (isset($_GET['hapus_id_mentor'])) {
    $id_mentor_hapus = mysqli_real_escape_string($conn, $_GET['hapus_id_mentor']);
    // Saat mentor dihapus, kelas_seni yang terkait akan set id_mentor menjadi NULL (ON DELETE SET NULL)
    $query_hapus = "DELETE FROM mentor WHERE id_mentor = ?";
    $stmt_hapus = mysqli_prepare($conn, $query_hapus);
    mysqli_stmt_bind_param($stmt_hapus, "s", $id_mentor_hapus);
    if (mysqli_stmt_execute($stmt_hapus)) {
        $pesan = '<p class="pesan sukses">Mentor berhasil dihapus.</p>';
    } else {
        $pesan = '<p class="pesan error">Gagal menghapus mentor: ' . mysqli_stmt_error($stmt_hapus) . '</p>';
    }
    mysqli_stmt_close($stmt_hapus);
}

// Untuk mengisi form edit
if (isset($_GET['edit_id_mentor'])) {
    $id_mentor_edit_form = mysqli_real_escape_string($conn, $_GET['edit_id_mentor']);
    $query_get_user = "SELECT id_mentor, username, email FROM mentor WHERE id_mentor = ?";
    $stmt_get_user = mysqli_prepare($conn, $query_get_user);
    mysqli_stmt_bind_param($stmt_get_user, "s", $id_mentor_edit_form);
    mysqli_stmt_execute($stmt_get_user);
    $result_user = mysqli_stmt_get_result($stmt_get_user);
    if($user_data = mysqli_fetch_assoc($result_user)){
        $user_to_edit = $user_data;
        $edit_mode = true;
    } else {
        $pesan = '<p class="pesan error">Mentor tidak ditemukan.</p>';
    }
    mysqli_stmt_close($stmt_get_user);
}

// Ambil semua mentor
$daftar_mentor = [];
$query_all_mentor = "SELECT id_mentor, username, email FROM mentor ORDER BY username ASC";
$hasil_all_mentor = mysqli_query($conn, $query_all_mentor);
if ($hasil_all_mentor) {
    while($row = mysqli_fetch_assoc($hasil_all_mentor)){
        $daftar_mentor[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kelola Mentor - Admin ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../../assets/css/admin.css" />
  <style> /* Style sama dengan kelola_member.php */
    .form-container { background-color: #2b2b2b; padding: 20px; border-radius: 8px; margin-bottom: 30px; max-width:500px;}
    .form-container h3 { color: #00ffcc; margin-top: 0; }
    .form-container label { display: block; margin-bottom: 5px; color: #ccc; }
    .form-container input[type="text"], .form-container input[type="email"], .form-container input[type="password"] { width: calc(100% - 22px); padding: 10px; margin-bottom: 10px; border-radius: 4px; border: 1px solid #444; background-color: #333; color: #fff; }
    .form-container button { padding: 10px 20px; background-color: #00ffcc; color: #111; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
    .form-container button:hover { background-color: #00e6b2; }
    .form-container .cancel-edit { background-color: #777; color: #fff; margin-left:10px; text-decoration:none; padding: 10px 15px; border-radius:4px;}
    .form-container .cancel-edit:hover { background-color: #555; }
    .table-container { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; color: #ccc; }
    table th, table td { border: 1px solid #444; padding: 10px; text-align: left; }
    table th { background-color: #333; color: #00ffcc; }
    table td a { color: #00ffcc; text-decoration: none; margin-right: 10px; }
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
        <a href="kelola_kursus.php">Kelola Kursus</a>
        <a href="verifikasi_pembayaran.php">Verifikasi Pembayaran</a>
        <a href="kelola_member.php">Kelola Member</a>
        <a href="kelola_mentor.php" class="active">Kelola Mentor</a>
        <a href="kelola_karya_seni.php">Kelola Karya Seni</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header><h1>Kelola Data Mentor</h1></header>
      <?= $pesan; ?>
      <?php if($edit_mode && $user_to_edit): ?>
      <div class="form-container">
        <h3>Edit Mentor: <?= htmlspecialchars($user_to_edit['username']); ?></h3>
        <form action="kelola_mentor.php" method="POST">
            <input type="hidden" name="id_mentor_edit" value="<?= $user_to_edit['id_mentor']; ?>">
            <div>
                <label for="username_edit">Username:</label>
                <input type="text" id="username_edit" name="username_edit" value="<?= htmlspecialchars($user_to_edit['username']); ?>" required>
            </div>
            <div>
                <label for="email_edit">Email:</label>
                <input type="email" id="email_edit" name="email_edit" value="<?= htmlspecialchars($user_to_edit['email']); ?>" required>
            </div>
            <div>
                <label for="password_edit">Password Baru (Kosongkan jika tidak ingin mengubah):</label>
                <input type="password" id="password_edit" name="password_edit">
            </div>
            <button type="submit" name="update_mentor">Update Mentor</button>
            <a href="kelola_mentor.php" class="cancel-edit">Batal Edit</a>
        </form>
      </div>
      <?php endif; ?>

      <div class="table-container">
        <h3>Daftar Semua Mentor</h3>
        <table>
            <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php if(!empty($daftar_mentor)): foreach($daftar_mentor as $mentor_item): ?>
                <tr>
                    <td><?= htmlspecialchars($mentor_item['id_mentor']); ?></td>
                    <td><?= htmlspecialchars($mentor_item['username']); ?></td>
                    <td><?= htmlspecialchars($mentor_item['email']); ?></td>
                    <td>
                        <a href="kelola_mentor.php?edit_id_mentor=<?= htmlspecialchars($mentor_item['id_mentor']); ?>">Edit</a>
                        <a href="kelola_mentor.php?hapus_id_mentor=<?= htmlspecialchars($mentor_item['id_mentor']); ?>" onclick="return confirm('Menghapus mentor akan mengatur ulang ID Mentor pada kelas yang terkait menjadi NULL. Apakah Anda yakin?');">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="4">Tidak ada data mentor.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>