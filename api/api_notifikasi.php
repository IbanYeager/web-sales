<?php
// api_notifikasi.php
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

require 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sales_id = isset($_GET['sales_account_id']) ? intval($_GET['sales_account_id']) : 1;

    $query = "SELECT id, title, body, time_label, unread, status_icon FROM tabel_notifikasi WHERE sales_account_id = ? ORDER BY id DESC";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $sales_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $row['unread'] = (bool)$row['unread'];
            $data[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $data]);
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal mengambil data: " . $conn->error]);
    }
} elseif ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $sales_id = isset($payload['sales_account_id']) ? intval($payload['sales_account_id']) : (isset($_POST['sales_account_id']) ? intval($_POST['sales_account_id']) : 1);
    $action = $payload['action'] ?? $_POST['action'] ?? 'read_all';

    if ($action === 'read_all') {
        $query = "UPDATE tabel_notifikasi SET unread = 0 WHERE sales_account_id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $sales_id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Semua notifikasi ditandai dibaca"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Gagal memperbarui notifikasi: " . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal mempersiapkan query: " . $conn->error]);
        }
    } elseif ($action === 'read_single') {
        $notif_id = isset($payload['id']) ? intval($payload['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
        $query = "UPDATE tabel_notifikasi SET unread = 0 WHERE id = ? AND sales_account_id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ii", $notif_id, $sales_id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Notifikasi berhasil ditandai dibaca"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Gagal memperbarui notifikasi: " . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal mempersiapkan query: " . $conn->error]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Metode request tidak didukung."]);
}

$conn->close();
?>
