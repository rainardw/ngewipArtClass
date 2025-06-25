<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "wipart_v2"; // Pastikan nama database ini sesuai

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>
