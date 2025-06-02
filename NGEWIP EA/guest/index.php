<?php
session_start();
include '../database/db.php'; // koneksi database

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['loginEmail'];
    $password = $_POST['loginPassword'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // redirect
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'mentor':
                    header("Location: mentor/dashboard.php");
                    break;
                default:
                    header("Location: member/dashboard.php");
            }
            exit();
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Email tidak ditemukan!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Drawing Course - Beranda</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <img src="assets/images/logo.png" alt="Logo" class="logo" />
    <div class="nav-toggle" id="navToggle">&#9776;</div>
    <ul class="nav-links" id="navLinks">
      <li><a href="index.html" class="active">Beranda</a></li>
      <li><a href="../pages/tentang.html"/>Tentang</a></li>
      <li><a href="../pages/program.html"/>Program Kursus</a></li>
      <li><a href="..pages/artikel.html"/>Artikel</a></li>
      <li><a href="..pages/biaya.html"/>Biaya & Pendaftaran</a></li>
    </ul>
  </nav>

  <!-- Hero -->
  <header class="hero">
    <div class="hero-content">
      <h1>Selamat Datang di ngeWIP ArtClass!</h1>
      <p>Kembangkan kemampuan menggambarmu bersama mentor terbaik</p>
      <a href="login.php" class="btn">Mulai Belajar</a>
    </div>
    <img src="assets/images/hero-drawing.png" alt="Hero" class="hero-img" />
  </header>

  <footer>
    <p>&copy; 2025 ngeWIP ArtClass BANZAIII</p>
  </footer>

  <script src="assets/js/main.js"></script>
</body>
</html>
