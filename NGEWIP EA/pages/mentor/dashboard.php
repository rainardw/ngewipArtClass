<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Mentor</title>
  <link rel="stylesheet" href="../../assets/css/mentor.css" />
</head>
<body>
    <script>
    const role = sessionStorage.getItem('userRole');
    if (role !== 'mentor') {
        alert('Kamu harus login sebagai mentor dulu!');
        window.location.href = '../../login.html';
    }
    </script>
  <div class="mentor-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <img src="../../assets/images/logo.png" class="logo" alt="Logo" />
      <h2>Mentor Panel</h2>
      <nav>
        <a href="dashboard.html" class="active">Dashboard</a>
        <a href="live-class.html">Live Class</a>
        <a href="../../pages/index.html">Kembali ke Beranda</a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header>
        <h1>Selamat Datang, Mentor!</h1>
        <p>Berikut ringkasan aktivitas dan kelas kamu.</p>
      </header>

      <section class="mentor-stats">
        <div class="stat-card">
          <h3>5</h3>
          <p>Kelas yang Diampu</p>
        </div>
        <div class="stat-card">
          <h3>112</h3>
          <p>Siswa Aktif</p>
        </div>
        <div class="stat-card">
          <h3>2</h3>
          <p>Live Class Hari I
