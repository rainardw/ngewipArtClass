<?php
session_start();
include '../database/db.php';

$error = '';
$success_message = '';

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? '';
    if ($role === 'admin') header("Location: ../pages/admin/dashboard.php");
    elseif ($role === 'mentor') header("Location: ../pages/mentor/dashboard.php");
    elseif ($role === 'member') header("Location: ../pages/student/dashboard.php");
    else header("Location: login.php"); // Fallback
    exit();
}

if (isset($_SESSION['signup_success_message'])) {
    $success_message = $_SESSION['signup_success_message'];
    unset($_SESSION['signup_success_message']);
}
if (isset($_GET['pesan']) && $_GET['pesan'] === 'login_dulu') {
    $error = 'Anda harus login sebagai member untuk mendaftar kursus.';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['loginEmail']));
    $password_dari_form = trim($_POST['loginPassword']);
    $loginAttempted = true; // Untuk menampilkan error umum jika tidak ada yang cocok

    // Cek admin
    $queryAdmin = "SELECT * FROM admin WHERE email = '$email'";
    $resultAdmin = mysqli_query($conn, $queryAdmin);
    if ($resultAdmin && mysqli_num_rows($resultAdmin) === 1) {
        $admin = mysqli_fetch_assoc($resultAdmin);
        if ($password_dari_form === $admin['password']) {
            $_SESSION['user_id'] = $admin['id_admin'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = 'admin';
            header("Location: ../pages/admin/dashboard.php");
            exit();
        }
    }

    // Cek mentor
    $queryMentor = "SELECT * FROM mentor WHERE email = '$email'";
    $resultMentor = mysqli_query($conn, $queryMentor);
    if ($resultMentor && mysqli_num_rows($resultMentor) === 1) {
        $mentor = mysqli_fetch_assoc($resultMentor);
        if ($password_dari_form === $mentor['password']) {
            $_SESSION['user_id'] = $mentor['id_mentor'];
            $_SESSION['username'] = $mentor['username'];
            $_SESSION['role'] = 'mentor';
            header("Location: ../pages/mentor/dashboard.php");
            exit();
        }
    }

    // Cek member
    $queryMember = "SELECT * FROM member WHERE email = '$email'";
    $resultMember = mysqli_query($conn, $queryMember);
    if ($resultMember && mysqli_num_rows($resultMember) === 1) {
        $member = mysqli_fetch_assoc($resultMember);
        if ($password_dari_form === $member['password']) {
            $_SESSION['user_id'] = $member['id_member']; // Simpan id_member
            $_SESSION['username'] = $member['username'];
            $_SESSION['role'] = 'member';
            
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect_url = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: ../" . $redirect_url); // Path relatif dari root web
            } else {
                header("Location: ../pages/student/dashboard.php");
            }
            exit();
        }
    }
    
    if($loginAttempted) { // Jika sudah mencoba login tapi tidak ada yang cocok
        $error = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - ngeWIP ArtClass</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body class="login-body">
  <div class="login-container">
    <img src="../assets/images/logo.png" alt="Logo" class="login-logo" style="max-width: 120px; margin-bottom: 20px;" />
    <h2>Login</h2>
    <?php if ($error): ?>
      <p style="color:red; text-align:center; margin-bottom:15px;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <p style="color:green; text-align:center; margin-bottom:15px;"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>
    <form method="POST" action="login.php">
      <input type="email" name="loginEmail" placeholder="Email" required />
      <input type="password" name="loginPassword" placeholder="Password" required />
      <button type="submit">Masuk</button>
      <p style="margin-top: 15px;">Belum punya akun? <a href="signup.php">Daftar di sini</a></p>
    </form>
  </div>
</body>
</html>