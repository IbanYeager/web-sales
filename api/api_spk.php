<?php
// api_spk.php
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Jika ada parameter all=true, maka tampilkan semua (untuk supervisor)
    $all = isset($_GET['all']) && $_GET['all'] === 'true';
    $sales_id = isset($_GET['sales_account_id']) ? intval($_GET['sales_account_id']) : 1;

    if ($all) {
        $query = "SELECT s.id, s.sales_account_id, sa.nama_lengkap as nama_sales, s.nama_customer, s.no_hp, s.model, s.nominal, s.tipe_pembelian, s.status, s.created_at 
                  FROM tabel_spk s
                  LEFT JOIN sales_accounts sa ON s.sales_account_id = sa.id
                  ORDER BY s.id DESC";
        $stmt = $conn->prepare($query);
    } else {
        $query = "SELECT id, nama_customer, no_hp, model, nominal, tipe_pembelian, status, created_at 
                  FROM tabel_spk 
                  WHERE sales_account_id = ? 
                  ORDER BY id DESC";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $sales_id);
        }
    }

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $row['nominal_jt'] = round($row['nominal'] / 1000000);
            $data[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $data]);
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal mempersiapkan query: " . $conn->error]);
    }
} elseif ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    
    $action = $payload['action'] ?? $_POST['action'] ?? 'submit';

    if ($action === 'submit') {
        $sales_id = isset($payload['sales_account_id']) ? intval($payload['sales_account_id']) : (isset($_POST['sales_account_id']) ? intval($_POST['sales_account_id']) : 1);
        $nama = $payload['nama_customer'] ?? $_POST['nama_customer'] ?? '';
        $hp = $payload['no_hp'] ?? $_POST['no_hp'] ?? '';
        $model = $payload['model'] ?? $_POST['model'] ?? '';
        $tipe_pembelian = $payload['tipe_pembelian'] ?? $_POST['tipe_pembelian'] ?? 'Kredit';
        
        $nominal = isset($payload['nominal']) ? intval($payload['nominal']) : (isset($_POST['nominal']) ? intval($_POST['nominal']) : 0);
        if ($nominal == 0) {
            if (stripos($model, 'avanza') !== false) $nominal = 285000000;
            elseif (stripos($model, 'calya') !== false) $nominal = 185000000;
            elseif (stripos($model, 'raize') !== false) $nominal = 248000000;
            else $nominal = 312000000;
        }

        if (empty($nama) || empty($hp) || empty($model)) {
            echo json_encode(["status" => "error", "message" => "Nama customer, No HP, dan Model mobil harus diisi."]);
            exit;
        }

        $query = "INSERT INTO tabel_spk (sales_account_id, nama_customer, no_hp, model, nominal, tipe_pembelian, status) VALUES (?, ?, ?, ?, ?, ?, 'Menunggu')";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("isssis", $sales_id, $nama, $hp, $model, $nominal, $tipe_pembelian);
            if ($stmt->execute()) {
                // Tambahkan notifikasi otomatis untuk supervisor
                $spk_id = $stmt->insert_id;
                $notif_title = "SPK Baru — " . $nama;
                $notif_body = "Sales mengajukan SPK unit " . $model . " seharga " . round($nominal / 1000000) . " Jt (" . $tipe_pembelian . ").";
                $notif_query = "INSERT INTO tabel_notifikasi (sales_account_id, title, body, time_label, status_icon) VALUES (?, ?, ?, 'Baru saja', 'check-to-slot')";
                $n_stmt = $conn->prepare($notif_query);
                if ($n_stmt) {
                    $n_stmt->bind_param("iss", $sales_id, $notif_title, $notif_body);
                    $n_stmt->execute();
                    $n_stmt->close();
                }

                echo json_encode(["status" => "success", "message" => "SPK berhasil diajukan ke Supervisor!", "id" => $spk_id]);
            } else {
                echo json_encode(["status" => "error", "message" => "Gagal menyimpan SPK: " . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal mempersiapkan query: " . $conn->error]);
        }
    } elseif ($action === 'update_status') {
        $spk_id = isset($payload['spk_id']) ? intval($payload['spk_id']) : (isset($_POST['spk_id']) ? intval($_POST['spk_id']) : 0);
        $status = $payload['status'] ?? $_POST['status'] ?? ''; // Disetujui / Ditolak

        if ($spk_id == 0 || !in_array($status, ['Disetujui', 'Ditolak'])) {
            echo json_encode(["status" => "error", "message" => "Parameter tidak valid."]);
            exit;
        }

        $query = "UPDATE tabel_spk SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("si", $status, $spk_id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Status SPK berhasil diperbarui menjadi " . $status]);
            } else {
                echo json_encode(["status" => "error", "message" => "Gagal memperbarui status SPK: " . $stmt->error]);
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
