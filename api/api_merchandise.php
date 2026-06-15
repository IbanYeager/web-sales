<?php
// Matikan error bawaan PHP agar format JSON tidak rusak jika ada masalah
error_reporting(0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Panggil file koneksi database
require 'koneksi.php';

// Query untuk mengambil semua data merchandise, diurutkan dari yang terbaru
$query = "SELECT * FROM merchandise_parts ORDER BY id DESC";
$result = $conn->query($query);

$data = [];

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    echo json_encode(["ok" => true, "data" => $data]);
} else {
    echo json_encode(["ok" => false, "message" => "Gagal mengambil data dari database: " . $conn->error]);
}

$conn->close();
?>