<?php
// api_customer.php
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require 'koneksi.php';

$sales_id = isset($_GET['sales_account_id']) ? intval($_GET['sales_account_id']) : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT id, nama, alamat, status FROM tabel_customer WHERE sales_account_id = ?";
if (!empty($search)) {
    $query .= " AND (nama LIKE ? OR alamat LIKE ?)";
}
$query .= " ORDER BY nama ASC";

$stmt = $conn->prepare($query);
if ($stmt) {
    if (!empty($search)) {
        $search_param = "%" . $search . "%";
        $stmt->bind_param("iss", $sales_id, $search_param, $search_param);
    } else {
        $stmt->bind_param("i", $sales_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $data]);
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Gagal mempersiapkan query: " . $conn->error]);
}

$conn->close();
?>
