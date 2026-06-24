<?php
// api_olx.php
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

require 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sales_id = isset($_GET['sales_account_id']) ? intval($_GET['sales_account_id']) : 1;

    $query = "SELECT id, nama_kendaraan, jenis_type, tahun, warna, harga_estimasi, lokasi_kecamatan, deskripsi_kondisi, foto_paths, created_at 
              FROM tabel_trade_in 
              WHERE sales_account_id = ? 
              ORDER BY id DESC";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $sales_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['foto_paths']) {
                $row['foto_paths'] = json_decode($row['foto_paths'], true);
            } else {
                $row['foto_paths'] = [];
            }
            $data[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $data]);
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal mengambil data: " . $conn->error]);
    }
} elseif ($method === 'POST') {
    $sales_id = isset($_POST['sales_account_id']) ? intval($_POST['sales_account_id']) : 1;
    $nama = $_POST['nama_kendaraan'] ?? '';
    $jenis = $_POST['jenis_type'] ?? '';
    $tahun = isset($_POST['tahun']) ? intval($_POST['tahun']) : 0;
    $warna = $_POST['warna'] ?? '';
    $harga = isset($_POST['harga_estimasi']) ? intval($_POST['harga_estimasi']) : 0;
    $lokasi = $_POST['lokasi_kecamatan'] ?? '';
    $deskripsi = $_POST['deskripsi_kondisi'] ?? '';

    if (empty($nama) || empty($jenis) || $harga <= 0 || empty($lokasi) || empty($deskripsi)) {
        echo json_encode(["status" => "error", "message" => "Harap lengkapi semua kolom yang wajib diisi."]);
        exit;
    }

    // Menangani Upload Multi Foto
    $uploaded_files = [];
    $upload_dir = '../uploads/olx/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (isset($_FILES['foto'])) {
        $jumlah_foto = count($_FILES['foto']['name']);
        for ($i = 0; $i < $jumlah_foto; $i++) {
            if ($_FILES['foto']['error'][$i] == 0) {
                $file_name = time() . '_' . uniqid() . '_' . basename($_FILES['foto']['name'][$i]);
                $file_path = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES['foto']['tmp_name'][$i], $file_path)) {
                    $uploaded_files[] = 'uploads/olx/' . $file_name;
                }
            }
        }
    }

    $foto_json = json_encode($uploaded_files);

    $query = "INSERT INTO tabel_trade_in (sales_account_id, nama_kendaraan, jenis_type, tahun, warna, harga_estimasi, lokasi_kecamatan, deskripsi_kondisi, foto_paths) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("issisisss", $sales_id, $nama, $jenis, $tahun, $warna, $harga, $lokasi, $deskripsi, $foto_json);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Listing Trade-In OLX berhasil disimpan!", "id" => $stmt->insert_id]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal menyimpan ke database: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal mempersiapkan query: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Metode request tidak didukung."]);
}

$conn->close();
?>
