<?php
$host = "localhost";
$user = "root"; 
$pass = "";     
$db   = "db_sales_app";

// Mematikan peringatan error bawaan PHP agar tidak merusak balasan JSON
error_reporting(0);

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    // Jika gagal konek DB, kirim pesan error berformat JSON
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode(["ok" => false, "message" => "Database gagal: " . $conn->connect_error]);
    exit; // Hentikan eksekusi
}
?>