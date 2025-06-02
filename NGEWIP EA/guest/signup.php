<?php
include '../database/db.php'; // sesuaikan dengan lokasi file koneksi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cek apakah username sudah dipakai
    $check = mysqli_query($conn, "SELECT * FROM member WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Username sudah digunakan!'); window.history.back();</script>";
        exit;
    }

    // // Hash password
    // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Simpan ke database
    $query = "INSERT INTO member (username, email, password) VALUES ('$username', '$email', '$password')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Pendaftaran berhasil! Silakan login.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat menyimpan data.'); window.history.back();</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Signup</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <style>
    body {
      background: #121212;
      color: #eee;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .signup-box {
      background: #1c1c1c;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 0 15px #00ffccaa;
      width: 320px;
      text-align: center;
    }
    input, button {
      width: 90%;
      padding: 12px;
      margin: 10px 0;
      border-radius: 6px;
      font-size: 16px;
      border: none;
    }
    button {
      background: #00ffcc;
      font-weight: bold;
      cursor: pointer;
    }
    button:hover {
      background: #00e6b2;
    }
    .error-msg {
      color: #ff6666;
      font-weight: bold;
      display: none;
      margin-top: 10px;
    }
    .success-msg {
      color: #66ff99;
      font-weight: bold;
      display: none;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="signup-box">
    <h2>Daftar Akun Baru</h2>
    <form id="signupForm" onclick="href='login.html'" method="post">
      <input type="text" name="username" id="newUsername" placeholder="Username" required />
      <input type="email" name = "email" id="newEmail" placeholder="Email" require />
      <input type="password" name="password" id="newPassword" placeholder="Password" required />
      <button type="submit">Daftar</button>
      <p class="error-msg" id="errorMsg">Username sudah dipakai!</p>
      <p class="success-msg" id="successMsg">Berhasil daftar! Silakan login.</p>
    </form>
    <a href="login.html" style="color:#ccc; display:block; margin-top:15px; text-decoration:none;">Sudah punya akun? Login di sini</a>
  </div>

<script src="../assets/js/auth.js"></script>

</body>
</html>
