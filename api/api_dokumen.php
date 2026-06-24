<?php
// api_dokumen.php
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

require 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 1;

    // Pastikan minimal ada baris checklist default untuk customer ini
    $check_query = "SELECT COUNT(*) as total FROM tabel_dokumen_customer WHERE customer_id = ?";
    $stmt = $conn->prepare($check_query);
    if ($stmt) {
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($res['total'] == 0) {
            // Insert default checklist items
            $defaults = [
                ['KTP Customer', 'Tersimpan', 'uploads/ktp_andi.jpg'],
                ['NPWP', 'Belum Ada', NULL],
                ['Slip Gaji', 'Tersimpan', 'uploads/slip_andi.pdf'],
                ['Rekening Koran', 'Opsional', NULL],
                ['Surat Keterangan Kerja', 'Opsional', NULL]
            ];
            $ins_query = "INSERT INTO tabel_dokumen_customer (customer_id, nama_dokumen, status, file_path) VALUES (?, ?, ?, ?)";
            $stmt_ins = $conn->prepare($ins_query);
            if ($stmt_ins) {
                foreach ($defaults as $d) {
                    $stmt_ins->bind_param("isss", $customer_id, $d[0], $d[1], $d[2]);
                    $stmt_ins->execute();
                }
                $stmt_ins->close();
            }
        }
    }

    $query = "SELECT id, nama_dokumen, status, file_path FROM tabel_dokumen_customer WHERE customer_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $data]);
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal mengambil data: " . $conn->error]);
    }
} elseif ($method === 'POST') {
    $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 1;
    $nama_dokumen = $_POST['nama_dokumen'] ?? '';

    if (empty($nama_dokumen)) {
        echo json_encode(["status" => "error", "message" => "Nama dokumen wajib diisi."]);
        exit;
    }

    $file_path = NULL;
    $status = 'Tersimpan';

    if (isset($_FILES['dokumen_file']) && $_FILES['dokumen_file']['error'] == 0) {
        $upload_dir = '../uploads/dokumen/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . '_' . uniqid() . '_' . basename($_FILES['dokumen_file']['name']);
        $dest_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['dokumen_file']['tmp_name'], $dest_path)) {
            $file_path = 'uploads/dokumen/' . $file_name;
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal mengunggah file."]);
            exit;
        }
    }

    $query = "UPDATE tabel_dokumen_customer SET status = ?, file_path = COALESCE(?, file_path) WHERE customer_id = ? AND nama_dokumen = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ssis", $status, $file_path, $customer_id, $nama_dokumen);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Dokumen " . $nama_dokumen . " berhasil diperbarui.", "file_path" => $file_path]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal memperbarui database: " . $stmt->error]);
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
