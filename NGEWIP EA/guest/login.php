<?php
session_start();
include '../database/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['loginEmail']);
    $password = $_POST['loginPassword'];

    // Cek di tabel admin
    $queryAdmin = "SELECT * FROM admin WHERE email = '$email'";
    $resultAdmin = mysqli_query($conn, $queryAdmin);

    if ($resultAdmin && mysqli_num_rows($resultAdmin) === 1) {
        $admin = mysqli_fetch_assoc($resultAdmin);
        if ($password === $admin['password']) {
            $_SESSION['user_id'] = $admin['id_admin'];
            $_SESSION['role'] = 'admin';
            header("Location: admin/dashboard.php");
            exit();
        }
    }

    // Cek di tabel mentor
    $queryMentor = "SELECT * FROM mentor WHERE email = '$email'";
    $resultMentor = mysqli_query($conn, $queryMentor);

    if ($resultMentor && mysqli_num_rows($resultMentor) === 1) {
        $mentor = mysqli_fetch_assoc($resultMentor);
        if ($password === $mentor['password']) {
            $_SESSION['user_id'] = $mentor['id_mentor'];
            $_SESSION['role'] = 'mentor';
            header("Location: mentor/dashboard.php");
            exit();
        }
    }

    // Cek di tabel member
    $queryMember = "SELECT * FROM member WHERE email = '$email'";
    $resultMember = mysqli_query($conn, $queryMember);

    if ($resultMember && mysqli_num_rows($resultMember) === 1) {
        $member = mysqli_fetch_assoc($resultMember);
        if ($password === $member['password']) {
            $_SESSION['user_id'] = $member['id_member'];
            $_SESSION['role'] = 'member';
            header("Location: member/dashboard.php");
            exit();
        }
    }

    // Jika gagal semua
    $error = "Email atau password salah!";
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Drawing Course</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body class="login-body">
  <div class="login-container">
    <img src="assets/images/logo.png" alt="Logo" class="login-logo" />
    <h2>Login</h2>

    <?php if ($error): ?>
      <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="email" name="loginEmail" placeholder="Email" required />
      <input type="password" name="loginPassword" placeholder="Password" required />
      <button type="submit">Masuk</button>
      <p>Belum punya akun? <a href="signup.php">Daftar di sini</a></p>
    </form>
  </div>
</body>
</html>
