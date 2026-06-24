<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipe = $_POST['tipe_Aktivitas'] ?? $_POST['jenisAktivitas'] ?? $_POST['tipe_aktivitas'] ?? '';
    $keterangan = $_POST['keterangan'] ?? $_POST['keteranganAktivitas'] ?? '';
    $lokasi = $_POST['lokasi'] ?? '';

    // Menangani Upload Multi Foto
    $uploaded_files = [];
    $upload_dir = '../uploads/lokasi/';
    
    // Buat folder jika belum ada
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if(isset($_FILES['foto'])) {
        $jumlah_foto = count($_FILES['foto']['name']);
        for($i = 0; $i < $jumlah_foto; $i++) {
            if($_FILES['foto']['error'][$i] == 0) {
                // Buat nama file unik agar tidak bentrok
                $file_name = time() . '_' . uniqid() . '_' . basename($_FILES['foto']['name'][$i]);
                $file_path = $upload_dir . $file_name;
                
                if(move_uploaded_file($_FILES['foto']['tmp_name'][$i], $file_path)) {
                    $uploaded_files[] = $file_name;
                }
            }
        }
    }

    // Gabungkan nama file menjadi 1 string dipisah koma (jika lebih dari 1)
    $foto_string = implode(',', $uploaded_files);

    // Gunakan Prepared Statement untuk keamanan
    $stmt = $conn->prepare("INSERT INTO aktivitas (tipe_aktivitas, keterangan, lokasi, foto) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $tipe, $keterangan, $lokasi, $foto_string);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Aktivitas berhasil disimpan"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menyimpan: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Metode request tidak diizinkan"]);
}
?>