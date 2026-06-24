<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'koneksi.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->username) && !empty($data->password)) {
    $username = $conn->real_escape_string($data->username);
    $password = $conn->real_escape_string($data->password);

    $query = "SELECT id, username, nama_lengkap, foto, nama_spv FROM sales_accounts WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode([
            "ok" => true,
            "message" => "Login berhasil",
            "sales" => [
                "id" => $user['id'],
                "name" => $user['nama_lengkap'],
                "foto" => $user['foto'],
                "spv" => $user['nama_spv']
            ]
        ]);
    } else {
        echo json_encode(["ok" => false, "message" => "Username atau password salah!"]);
    }
} else {
    echo json_encode(["ok" => false, "message" => "Data tidak lengkap!"]);
}
$conn->close();
?>