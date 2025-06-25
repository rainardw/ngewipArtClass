<?php
include '../database/db.php';
session_start();

$error = '';

if (isset($_SESSION['user_id'])) {
    // Redirect jika sudah login
    $role = $_SESSION['role'] ?? '';
    if ($role === 'admin') header("Location: ../pages/admin/dashboard.php");
    elseif ($role === 'mentor') header("Location: ../pages/mentor/dashboard.php");
    elseif ($role === 'member') header("Location: ../pages/student/dashboard.php");
    else header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password_plain = trim($_POST['password']); // Password teks biasa

    if (empty($username) || empty($email) || empty($password_plain)) {
        $error = 'Semua field wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (strlen($password_plain) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        $checkQuery = "SELECT * FROM member WHERE username = '$username' OR email = '$email'";
        $checkResult = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            $existingUser = mysqli_fetch_assoc($checkResult);
            if ($existingUser['username'] === $username) {
                $error = 'Username sudah digunakan!';
            } elseif ($existingUser['email'] === $email) {
                $error = 'Email sudah terdaftar!';
            }
        } else {
            // Simpan password teks biasa
            $query = "INSERT INTO member (username, email, password) VALUES ('$username', '$email', '$password_plain')";
            if (mysqli_query($conn, $query)) {
                $_SESSION['signup_success_message'] = 'Pendaftaran berhasil! Silakan login.';
                header("Location: login.php");
                exit;
            } else {
                $error = 'Terjadi kesalahan saat menyimpan data: ' . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Daftar Akun - ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <style><body class="signup-page">
  </style>
</head>
<body>
  <div class="signup-box">
    <h2>Daftar Akun Baru</h2>
    <?php if (!empty($error)): ?>
        <p class="form-message error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form id="signupForm" method="post" action="signup.php">
      <input type="text" name="username" placeholder="Username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required />
      <input type="email" name="email" placeholder="Email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Daftar</button>
    </form>
    <a href="login.php" class="login-link">Sudah punya akun? Login di sini</a>
  </div>
   <script src="../assets/js/script.js"></script>
</body>
</html>