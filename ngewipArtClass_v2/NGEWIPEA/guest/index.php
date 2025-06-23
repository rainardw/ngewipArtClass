<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ngeWIP ArtClass - Beranda</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>
  <nav class="navbar">
    <img src="../assets/images/logo.png" alt="Logo" class="logo" />
    <div class="nav-toggle" id="navToggle">&#9776;</div>
    <ul class="nav-links" id="navLinks">
      <li><a href="index.php" class="active">Beranda</a></li>
      <li><a href="../pages/tentang.html">Tentang</a></li>
      <li><a href="../pages/program.php">Program Kursus</a></li>
      <li><a href="../pages/artikel.html">Artikel</a></li>
      <li><a href="../pages/biaya.php">Biaya & Pendaftaran</a></li>
      <li><a href="../pages/galery_karya.php">Galery Karya</a></li>
      <?php if (isset($_SESSION['user_id'])): ?>
            <?php
                $dashboard_link = '#';
                $role = $_SESSION['role'] ?? '';
                if ($role === 'admin') $dashboard_link = '../pages/admin/dashboard.php';
                elseif ($role === 'mentor') $dashboard_link = '../pages/mentor/dashboard.php';
                elseif ($role === 'member') $dashboard_link = '../pages/student/dashboard.php';
            ?>
            <li><a href="<?= $dashboard_link ?>">Dashboard</a></li>
            <li><a href="logout.php">Logout</a></li>

        <?php endif; ?>
    </ul>
  </nav>

  <header class="hero">
    <div class="hero-content">
      <h1>Selamat Datang di ngeWIP ArtClass!</h1>
      <p>Kembangkan kemampuan menggambarmu bersama mentor terbaik</p>
      <?php if (isset($_SESSION['user_id'])): ?>
         <a href="<?= $dashboard_link ?>" class="btn">Lanjutkan ke Dashboard</a>
      <?php else: ?>
         <a href="login.php" class="btn">Mulai Belajar/login</a>
      <?php endif; ?>
    </div>
    <img src="../assets/images/hero-drawing.png" alt="Hero" class="hero-img" />
  </header>

  <footer>
    <p>&copy; <?= date("Y") ?> ngeWIP ArtClass BANZAIII</p>
  </footer>

  <script src="../assets/js/script.js"></script>
</body>
</html>
