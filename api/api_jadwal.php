<?php
// api_jadwal.php
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Ambil parameter sales_account_id, default ke 1 untuk demo/sales pertama
    $sales_id = isset($_GET['sales_account_id']) ? intval($_GET['sales_account_id']) : 1;

    $query = "SELECT id, waktu, judul, deskripsi, status FROM tabel_jadwal WHERE sales_account_id = ? ORDER BY waktu ASC";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $sales_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Konversi waktu dari hh:mm:ss ke hh:mm
            if (isset($row['waktu'])) {
                $row['waktu'] = substr($row['waktu'], 0, 5);
            }
            $data[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $data]);
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal mempersiapkan query: " . $conn->error]);
    }
} elseif ($method === 'POST') {
    // Menerima data dalam format JSON atau x-www-form-urlencoded
    $payload = json_decode(file_get_contents('php://input'), true);
    
    $sales_id = isset($payload['sales_account_id']) ? intval($payload['sales_account_id']) : (isset($_POST['sales_account_id']) ? intval($_POST['sales_account_id']) : 1);
    $waktu = $payload['waktu'] ?? $_POST['waktu'] ?? '';
    $judul = $payload['judul'] ?? $_POST['judul'] ?? '';
    $deskripsi = $payload['deskripsi'] ?? $_POST['deskripsi'] ?? '';
    $status = $payload['status'] ?? $_POST['status'] ?? 'Terjadwal';

    if (empty($waktu) || empty($judul) || empty($deskripsi)) {
        echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap."]);
        exit;
    }

    $query = "INSERT INTO tabel_jadwal (sales_account_id, waktu, judul, deskripsi, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("issss", $sales_id, $waktu, $judul, $deskripsi, $status);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Jadwal berhasil disimpan", "id" => $stmt->insert_id]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal menyimpan jadwal: " . $stmt->error]);
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
