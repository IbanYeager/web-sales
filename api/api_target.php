<?php
require 'koneksi.php';
header("Content-Type: application/json; charset=UTF-8");

// Ambil ID sales dari request parameter (contoh: api_target.php?id_sales=1)
$id_sales = isset($_GET['id_sales']) ? intval($_GET['id_sales']) : 0;

// Ambil bulan dan tahun saat ini
$bulan = date('n'); // Format 1-12
$tahun = date('Y');

if ($id_sales == 0) {
    echo json_encode(["status" => "error", "message" => "ID Sales tidak valid"]);
    exit;
}

// 1. Ambil target bulanan dari database
$query_bulanan = $conn->prepare("SELECT id_target_bulanan, target_total FROM target_do_bulanan WHERE sales_account_id = ? AND periode_bulan = ? AND periode_tahun = ?");
$query_bulanan->bind_param("iii", $id_sales, $bulan, $tahun);
$query_bulanan->execute();
$result_bulanan = $query_bulanan->get_result();

if ($result_bulanan->num_rows > 0) {
    $data_bulanan = $result_bulanan->fetch_assoc();
    $id_target_bulanan = $data_bulanan['id_target_bulanan'];
    $target_total = $data_bulanan['target_total'];

    // 2. Ambil rincian mingguan dari database
    $query_mingguan = $conn->prepare("SELECT minggu_ke, target_unit, realisasi_unit FROM target_do_mingguan WHERE id_target_bulanan = ? ORDER BY minggu_ke ASC");
    $query_mingguan->bind_param("i", $id_target_bulanan);
    $query_mingguan->execute();
    $result_mingguan = $query_mingguan->get_result();

    $mingguan = [];
    $realisasi_total = 0;
    
    while($row = $result_mingguan->fetch_assoc()){
        $mingguan[] = $row;
        $realisasi_total += $row['realisasi_unit']; // Hitung otomatis DO terkumpul
    }

    // 3. Kalkulasi sisa unit & persentase
    $sisa = $target_total - $realisasi_total;
    if ($sisa < 0) $sisa = 0; // Jika over target
    
    $persentase = ($target_total > 0) ? round(($realisasi_total / $target_total) * 100) : 0;

    // Kirim balasan JSON
    echo json_encode([
        "status" => "success",
        "periode" => date('F Y'), // Contoh: June 2026
        "target_total" => $target_total,
        "realisasi_total" => $realisasi_total,
        "sisa" => $sisa,
        "persentase" => $persentase,
        "mingguan" => $mingguan
    ]);
} else {
    echo json_encode(["status" => "empty", "message" => "Belum ada target diset untuk bulan ini."]);
}
?>