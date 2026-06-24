<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Panggil koneksi database
require 'koneksi.php';

$kecamatan = isset($_GET['kecamatan']) ? $_GET['kecamatan'] : '';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '2026';

// Menggunakan Prepared Statements untuk keamanan (Mencegah SQL Injection)
$query = "SELECT merk, type, COUNT(*) AS unit 
          FROM tabel_polreg 
          WHERE kecamatan = ? AND tahun = ? 
          GROUP BY merk, type 
          ORDER BY unit DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $kecamatan, $tahun); // 'ss' berarti dua variabel bertipe String
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "merk" => $row['merk'],
        "type" => $row['type'],
        "unit" => (int)$row['unit']
    ];
}

echo json_encode(["ok" => true, "data" => $data]);

// Tutup statement dan koneksi
$stmt->close();
$conn->close();
?>