<?php
// config.php
$connection = mysqli_connect('localhost', 'root', '', 'dhira');
if (!$connection) {
    die('Koneksi DB gagal: '.mysqli_connect_error());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
