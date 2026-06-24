<?php
// api_promo.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'koneksi.php';

// Ambil semua data dari tabel paket_kredit, urutkan berdasarkan Nama Paket dan Tipe Mobil
$sql = "SELECT * FROM paket_kredit ORDER BY nama_paket ASC, tipe_mobil ASC, tenor ASC";

$result = $conn->query($sql);

$data = array();

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else if ($result === false) {
    // Error di SQL
    die(json_encode(array('error' => 'Query error: ' . $conn->error)));
}

// Kirim data dalam format JSON
echo json_encode($data);

$conn->close();
?>