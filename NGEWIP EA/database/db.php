<?php
$host = "localhost";      // Host XAMPP
$user = "root";           // User default XAMPP
$pass = "";               // Password default kosong
$db   = "wipart";         // Nama database

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>
