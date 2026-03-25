<?php
// config.php
$host = "localhost"; // Host database
$username = "root"; // Username database
$password = ""; // Password database 
$database = "delivery_request"; // Nama database

// Set timezone PHP PERTAMA KALI
date_default_timezone_set('Asia/Jakarta');

// Buat koneksi mysqli
$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi mysqli
if ($conn->connect_error) {
    die("Koneksi mysqli gagal: " . $conn->connect_error);
}

// Set charset ke utf8 untuk mysqli
$conn->set_charset("utf8");

// PERBAIKAN: Set timezone MySQL dengan beberapa cara
$conn->query("SET time_zone = '+07:00'");
$conn->query("SET @@session.time_zone = '+07:00'");

// Tambahkan koneksi PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Set timezone PDO
    $pdo->exec("SET time_zone = '+07:00'");
    $pdo->exec("SET @@session.time_zone = '+07:00'");
    
} catch (PDOException $e) {
    die("Koneksi PDO gagal: " . $e->getMessage());
}
?>