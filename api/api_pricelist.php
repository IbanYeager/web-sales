<?php
// Set header agar output dibaca sebagai JSON dan mengizinkan request API
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// Panggil koneksi database
require_once 'koneksi.php';

$dataMobil = [];
$sql = "SELECT * FROM pricelist_mobil ORDER BY kategori_order ASC, model ASC, tipe ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        // Logika Harga MT & AT
        $h_mt = (int)$row['harga_mt'];
        $h_at = (int)$row['harga_at'];
        $harga_mulai = 0;
        
        if ($h_mt > 0 && $h_at > 0) {
            $harga_mulai = min($h_mt, $h_at);
        } else if ($h_mt > 0) {
            $harga_mulai = $h_mt;
        } else if ($h_at > 0) {
            $harga_mulai = $h_at;
        }

        // Logika Gambar
        $urlGambar = !empty($row['gambar']) 
            ? "assets/img/mobil/" . $row['gambar'] 
            : "https://placehold.co/400x250/f8f9fa/c8102e?text=" . urlencode($row['model']);

        $dataMobil[] = [
            "kategori_order" => isset($row['kategori_order']) ? $row['kategori_order'] : 'Lainnya',
            "model"          => $row['model'],           // Agya, Veloz, Rush, dll — untuk grouping
            "tipe_paket"     => $row['tipe'],             // 1.2 G MT, 1.5 Q AT, dll
            "nama"           => $row['model'] . " " . $row['tipe'],  // Full name
            "tipe"           => $row['model'],            // backward compat
            "harga_mt"       => $h_mt,
            "harga_at"       => $h_at,
            "harga"          => $harga_mulai,
            "img"            => $urlGambar
        ];
    }
}

$conn->close();

echo json_encode([
    "ok"   => true,
    "data" => $dataMobil
]);
?>