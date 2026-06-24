<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'koneksi.php';

// Ambil tahun unik yang ada di tabel_polreg
$query = "SELECT DISTINCT tahun FROM tabel_polreg WHERE tahun IS NOT NULL AND tahun <> '' ORDER BY tahun DESC";

$result = $conn->query($query);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Pastikan menjadi string untuk konsistensi
        $data[] = (string)$row['tahun'];
    }
}

echo json_encode(["ok" => true, "data" => $data]);

$conn->close();
?>

