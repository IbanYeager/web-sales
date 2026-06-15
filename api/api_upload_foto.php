<?php
// Matikan error bawaan PHP agar tidak merusak format JSON
error_reporting(0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sales_id = $_POST['sales_id'];

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $file = $_FILES['foto'];
        $fileName = time() . '_' . basename($file['name']); 
        
        // Target folder mundur 1 direktori
        $targetDir = "../uploads/"; 
        
        // Buat folder uploads otomatis jika belum ada
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $targetFilePath = $targetDir . $fileName;
        
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');

        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                
                // Dapatkan URL dinamis agar foto bisa diakses dengan benar
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $domain = $_SERVER['HTTP_HOST'];
                // $_SERVER['REQUEST_URI'] menunjuk ke /sales_pov/api/api_upload_foto.php
                // dirname(dirname) mengambil /sales_pov
                $base_dir = rtrim(dirname(dirname($_SERVER['REQUEST_URI'])), '/\\');
                
                // Path lengkap untuk disimpan ke Database
                $fullPath = $protocol . "://" . $domain . $base_dir . "/uploads/" . $fileName;
                
                $sql = "UPDATE sales_accounts SET foto = '$fullPath' WHERE id = '$sales_id'";
                
                if ($conn->query($sql) === TRUE) {
                    echo json_encode(["ok" => true, "message" => "Foto berhasil diunggah", "path" => $fullPath]);
                } else {
                    echo json_encode(["ok" => false, "message" => "Gagal update database: " . $conn->error]);
                }
            } else {
                echo json_encode(["ok" => false, "message" => "Gagal memindahkan file ke folder uploads"]);
            }
        } else {
            echo json_encode(["ok" => false, "message" => "Format file tidak didukung (Hanya JPG, PNG, GIF)"]);
        }
    } else {
        echo json_encode(["ok" => false, "message" => "Tidak ada file yang diunggah atau file rusak"]);
    }
} else {
    echo json_encode(["ok" => false, "message" => "Metode request salah"]);
}

$conn->close();
?>