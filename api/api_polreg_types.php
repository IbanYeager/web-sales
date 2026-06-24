<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'koneksi.php';

$kecamatan = isset($_GET['kecamatan']) ? $_GET['kecamatan'] : '';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '2026';
$merk = isset($_GET['merk']) ? $_GET['merk'] : '';

$query = "SELECT DISTINCT type FROM tabel_polreg WHERE kecamatan = ? AND tahun = ? AND type IS NOT NULL AND type <> ''";
$params = [$kecamatan, $tahun];
$types = "ss";

if ($merk !== '') {
    $query .= " AND merk = ? ";
    $params[] = $merk;
    $types .= "s";
}

$query .= " AND type IS NOT NULL AND type <> '' ORDER BY type";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = (string)$row['type'];
}

echo json_encode(["ok" => true, "data" => $data]);

$stmt->close();
$conn->close();
?>

