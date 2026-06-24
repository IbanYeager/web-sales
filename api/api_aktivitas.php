<?php
// Matikan error bawaan PHP agar format JSON tidak rusak jika ada masalah
error_reporting(0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Panggil file koneksi database
require 'koneksi.php';

// Mendapatkan parameter limit jika disediakan, default ke 10
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if ($limit <= 0) {
    $limit = 10;
}

// Query untuk mengambil aktivitas terbaru
$query = "SELECT id, tipe_aktivitas, keterangan, lokasi, foto, created_at FROM aktivitas ORDER BY id DESC LIMIT ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $data]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal mengambil data: " . $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Gagal mempersiapkan query: " . $conn->error]);
}

$conn->close();
?>
