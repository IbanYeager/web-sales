<?php
// api_promo_tenor.php - Endpoint untuk mengambil daftar tenor unik dari database

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'koneksi.php';

// Query untuk mengambil tenor unik, urut dari kecil ke besar
$sql = "SELECT DISTINCT tenor FROM paket_kredit WHERE tenor IS NOT NULL ORDER BY tenor ASC";

$result = $conn->query($sql);

$tenors = array();

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $tenors[] = (int)$row['tenor'];
    }
} else if ($result === false) {
    // Error di SQL
    die(json_encode(array('error' => 'Query error: ' . $conn->error)));
}

// Kirim data dalam format JSON
echo json_encode($tenors);

$conn->close();
?>
