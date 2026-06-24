<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'koneksi.php';

$kecamatan = isset($_GET['kecamatan']) ? $_GET['kecamatan'] : '';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '2026';
$merk = isset($_GET['merk']) ? $_GET['merk'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Build query dinamis berdasarkan filter yang dikirim
$query = "SELECT merk, type, COUNT(*) AS unit "+
         "FROM tabel_polreg "+
         "WHERE kecamatan = ? AND tahun = ? ";

$params = [$kecamatan, $tahun];
$types = "ss";

if ($merk !== '') {
    $query .= " AND merk = ? ";
    $params[] = $merk;
    $types .= "s";
}

if ($type !== '') {
    $query .= " AND type = ? ";
    $params[] = $type;
    $types .= "s";
}

$query .= " GROUP BY merk, type ORDER BY unit DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'merk' => $row['merk'],
        'type' => $row['type'],
        'unit' => (int)$row['unit']
    ];
}

echo json_encode(["ok" => true, "data" => $data]);

$stmt->close();
$conn->close();
?>

