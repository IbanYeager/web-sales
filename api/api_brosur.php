<?php
// api_brosur.php
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require 'koneksi.php';

$query = "SELECT id, nama, deskripsi, pdf_url FROM tabel_brosur ORDER BY nama ASC";
$result = $conn->query($query);

if ($result) {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $data]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal mengambil data: " . $conn->error]);
}

$conn->close();
?>
