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

// Logika Edit Member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_member'])) {
    $id_member_edit = (int)$_POST['id_member_edit'];
    $username_edit = mysqli_real_escape_string($conn, trim($_POST['username_edit']));
    $email_edit = mysqli_real_escape_string($conn, trim($_POST['email_edit']));
    $password_edit_plain = trim($_POST['password_edit']); // Password teks biasa

    // Validasi dasar
    if (empty($username_edit) || empty($email_edit)) {
        $pesan = '<p class="pesan error">Username dan Email tidak boleh kosong.</p>';
    } elseif (!filter_var($email_edit, FILTER_VALIDATE_EMAIL)) {
        $pesan = '<p class="pesan error">Format email tidak valid.</p>';
    } else {
        // Cek duplikasi username atau email (kecuali untuk user yang sedang diedit)
        $cek_duplikat = "SELECT * FROM member WHERE (username = ? OR email = ?) AND id_member != ?";
        $stmt_cek = mysqli_prepare($conn, $cek_duplikat);
        mysqli_stmt_bind_param($stmt_cek, "ssi", $username_edit, $email_edit, $id_member_edit);
        mysqli_stmt_execute($stmt_cek);
        $hasil_cek = mysqli_stmt_get_result($stmt_cek);

        if (mysqli_num_rows($hasil_cek) > 0) {
            $existing = mysqli_fetch_assoc($hasil_cek);
            if ($existing['username'] === $username_edit) $pesan = '<p class="pesan error">Username sudah digunakan oleh member lain.</p>';
            else $pesan = '<p class="pesan error">Email sudah digunakan oleh member lain.</p>';
        } else {
            if (!empty($password_edit_plain)) { // Jika password diisi, update password
                if(strlen($password_edit_plain) < 6) {
                    $pesan = '<p class="pesan error">Password baru minimal 6 karakter.</p>';
                } else {
                    $query_update = "UPDATE member SET username = ?, email = ?, password = ? WHERE id_member = ?";
                    $stmt_update = mysqli_prepare($conn, $query_update);
                    mysqli_stmt_bind_param($stmt_update, "sssi", $username_edit, $email_edit, $password_edit_plain, $id_member_edit);
                }
            } else { // Jika password dikosongkan, jangan update password
                $query_update = "UPDATE member SET username = ?, email = ? WHERE id_member = ?";
                $stmt_update = mysqli_prepare($conn, $query_update);
                mysqli_stmt_bind_param($stmt_update, "ssi", $username_edit, $email_edit, $id_member_edit);
            }

            if(empty($pesan)){ // Jika tidak ada error dari validasi password
                if (mysqli_stmt_execute($stmt_update)) {
                    $pesan = '<p class="pesan sukses">Data member berhasil diperbarui.</p>';
                } else {
                    $pesan = '<p class="pesan error">Gagal memperbarui data member: ' . mysqli_stmt_error($stmt_update) . '</p>';
                }
                mysqli_stmt_close($stmt_update);
            }
        }
        mysqli_stmt_close($stmt_cek);
    }
    $edit_mode = false; // Keluar dari mode edit setelah update
    $user_to_edit = null;
}


// Logika Hapus Member
if (isset($_GET['hapus_id_member'])) {
    $id_member_hapus = (int)$_GET['hapus_id_member'];
    // PERHATIAN: Menghapus member akan menghapus pendaftaran dan karya seninya juga karena ON DELETE CASCADE
    $query_hapus = "DELETE FROM member WHERE id_member = ?";
    $stmt_hapus = mysqli_prepare($conn, $query_hapus);
    mysqli_stmt_bind_param($stmt_hapus, "i", $id_member_hapus);
    if (mysqli_stmt_execute($stmt_hapus)) {
        $pesan = '<p class="pesan sukses">Member berhasil dihapus.</p>';
    } else {
        $pesan = '<p class="pesan error">Gagal menghapus member: ' . mysqli_stmt_error($stmt_hapus) . '</p>';
    }
    mysqli_stmt_close($stmt_hapus);
}

// Untuk mengisi form edit
if (isset($_GET['edit_id_member'])) {
    $id_member_edit_form = (int)$_GET['edit_id_member'];
    $query_get_user = "SELECT id_member, username, email FROM member WHERE id_member = ?";
    $stmt_get_user = mysqli_prepare($conn, $query_get_user);
    mysqli_stmt_bind_param($stmt_get_user, "i", $id_member_edit_form);
    mysqli_stmt_execute($stmt_get_user);
    $result_user = mysqli_stmt_get_result($stmt_get_user);
    if($user_data = mysqli_fetch_assoc($result_user)){
        $user_to_edit = $user_data;
        $edit_mode = true;
    } else {
        $pesan = '<p class="pesan error">Member tidak ditemukan.</p>';
    }
    mysqli_stmt_close($stmt_get_user);
}


// Ambil semua member
$daftar_member = [];
$query_all_member = "SELECT id_member, username, email FROM member ORDER BY username ASC";
$hasil_all_member = mysqli_query($conn, $query_all_member);
if ($hasil_all_member) {
    while($row = mysqli_fetch_assoc($hasil_all_member)){
        $daftar_member[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kelola Member - Admin ngeWIP ArtClass</title>
  <script>
  if (localStorage.getItem("theme") === "light") {
    document.documentElement.classList.add("light-mode");
  }
</script>
  <link rel="stylesheet" href="../../assets/css/admin.css" />
  <style>
    /* Style mirip kelola_kursus.php */
    .form-container { background-color: #2b2b2b; padding: 20px; border-radius: 8px; margin-bottom: 30px; max-width:500px;}
    .form-container h3 { color: #00ffcc; margin-top: 0; }
    .form-container label { display: block; margin-bottom: 5px; color: #ccc; }
    .form-container input[type="text"],
    .form-container input[type="email"],
    .form-container input[type="password"] {
        width: calc(100% - 22px); padding: 10px; margin-bottom: 10px;
        border-radius: 4px; border: 1px solid #444; background-color: #333; color: #fff;
    }
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
<button id="modeToggle" style="position: fixed; top: 10px; right: 10px; z-index: 200;">☀️/🌙</button>
  <div class="admin-container">
    <aside class="sidebar">
      <img src="../../assets/images/logo.png" class="logo" alt="Logo" />
      <h2>Admin Panel</h2>
      <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="analytics.php">Analytics</a>
        <a href="kelola_kursus.php">Kelola Kursus</a>
        <a href="verifikasi_pembayaran.php">Verifikasi Pembayaran</a>
        <a href="kelola_member.php" class="active">Kelola Member</a>
        <a href="kelola_mentor.php">Kelola Mentor</a>
        <a href="kelola_karya_seni.php">Kelola Karya Seni</a>
        <a href="../../guest/index.php">Kembali ke Beranda</a>
        <a href="../../guest/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content">
      <header><h1>Kelola Data Member</h1></header>
      <?= $pesan; ?>

      <?php if($edit_mode && $user_to_edit): ?>
      <div class="form-container">
        <h3>Edit Member: <?= htmlspecialchars($user_to_edit['username']); ?></h3>
        <form action="kelola_member.php" method="POST">
            <input type="hidden" name="id_member_edit" value="<?= $user_to_edit['id_member']; ?>">
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
            <button type="submit" name="update_member">Update Member</button>
            <a href="kelola_member.php" class="cancel-edit">Batal Edit</a>
        </form>
      </div>
      <?php endif; ?>

      <div class="table-container">
        <h3>Daftar Semua Member</h3>
        <table>
            <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php if(!empty($daftar_member)): foreach($daftar_member as $member): ?>
                <tr>
                    <td><?= $member['id_member']; ?></td>
                    <td><?= htmlspecialchars($member['username']); ?></td>
                    <td><?= htmlspecialchars($member['email']); ?></td>
                    <td>
                        <a href="kelola_member.php?edit_id_member=<?= $member['id_member']; ?>">Edit</a>
                        <a href="kelola_member.php?hapus_id_member=<?= $member['id_member']; ?>" onclick="return confirm('PERHATIAN: Menghapus member juga akan menghapus semua data pendaftaran dan karya seni yang terkait. Apakah Anda yakin?');">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="4">Tidak ada data member.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>
    </main>
  </div>
<script src="../assets/js/script.js"></script>
</body>
</html>