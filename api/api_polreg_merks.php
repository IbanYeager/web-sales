<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'koneksi.php';

$kecamatan = isset($_GET['kecamatan']) ? $_GET['kecamatan'] : '';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '2026';

$query = "SELECT DISTINCT merk FROM tabel_polreg WHERE kecamatan = ? AND tahun = ? AND merk IS NOT NULL AND merk <> '' ORDER BY merk";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $kecamatan, $tahun);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = (string)$row['merk'];
}

echo json_encode(["ok" => true, "data" => $data]);

$stmt->close();
$conn->close();
?>

