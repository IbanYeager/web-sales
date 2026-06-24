<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Panggil koneksi database
require 'koneksi.php';

$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '2026';

// Menggunakan Prepared Statements untuk keamanan
$query = "SELECT TRIM(kecamatan) AS kecamatan, COUNT(*) AS total_unit
          FROM tabel_polreg
          WHERE tahun = ? AND kecamatan IS NOT NULL AND TRIM(kecamatan) <> ''
          GROUP BY TRIM(kecamatan)
          ORDER BY total_unit DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $tahun); // 's' berarti satu variabel bertipe String
$stmt->execute();
$result = $stmt->get_result();

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "kecamatan"  => $row['kecamatan'],
            "total_unit" => (int)$row['total_unit']
        ];
    }
}

echo json_encode(["ok" => true, "data" => $data]);

// Tutup statement dan koneksi
$stmt->close();
$conn->close();
?>