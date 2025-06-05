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
  <style>
    body { background: #121212; color: #eee; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px 0; }
    .signup-box { background: #1c1c1c; padding: 30px; border-radius: 12px; box-shadow: 0 0 15px #00ffccaa; width: 100%; max-width: 380px; text-align: center; }
    .signup-box h2 { color: #00ffcc; margin-bottom: 25px; }
    .signup-box input { width: calc(100% - 24px); padding: 12px; margin: 10px 0; border-radius: 6px; font-size: 16px; border: 1px solid #333; background-color: #2b2b2b; color: #eee; }
    .signup-box input::placeholder { color: #888; }
    .signup-box button { width: 100%; padding: 12px; margin: 20px 0 10px; border-radius: 6px; font-size: 16px; border: none; background: #00ffcc; color: #121212; font-weight: bold; cursor: pointer; transition: background-color 0.3s ease; }
    .signup-box button:hover { background: #00e6b2; }
    .form-message { font-weight: bold; margin-bottom: 15px; padding: 10px; border-radius: 6px; display: block; }
    .form-message.error { color: #ff6666; background-color: rgba(255, 102, 102, 0.1); }
    .login-link { color:#ccc; display:block; margin-top:15px; text-decoration:none; font-size: 0.9em; }
    .login-link:hover { color: #00ffcc; }
  </style>
</head>
<script src="../assets/js/script.js"></script>
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
</body>
</html>