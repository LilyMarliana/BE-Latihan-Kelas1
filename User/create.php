<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$email             = isset($_POST['email']) ? $_POST['email'] : null;
$password          = isset($_POST['password']) ? $_POST['password'] : null;  // Password plain text
$role              = isset($_POST['role']) ? $_POST['role'] : null;
$nomorTelepon      = isset($_POST['nomorTelepon']) ? $_POST['nomorTelepon'] : null;
$fotoProfile       = isset($_POST['fotoProfile']) ? $_POST['fotoProfile'] : null;

// Validasi input
if (empty($email) || empty($password) || empty($role)) {
    echo json_encode([
        "status" => "error",
        "message" => "Email, password, dan role wajib diisi"
    ]);
    exit;
}

// Validasi role
$validRoles = ['orangtua', 'ibu_hamil', 'remaja', 'tenaga_kesehatan'];
if (!in_array($role, $validRoles)) {
    echo json_encode([
        "status" => "error",
        "message" => "Role tidak valid"
    ]);
    exit;
}

// Hash password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO user
    (email, passwordHash, role, nomorTelepon, fotoProfile)
    VALUES (?, ?, ?, ?, ?)
");

// s = string
$stmt->bind_param(
    "sssss",
    $email,
    $passwordHash,
    $role,
    $nomorTelepon,
    $fotoProfile
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "User berhasil ditambahkan",
        "data" => [
            "userID" => $last_id,
            "email" => $email,
            "role" => $role,
            "nomorTelepon" => $nomorTelepon,
            "fotoProfile" => $fotoProfile
        ]
    ]);

} else {

    echo json_encode([
        "status"  => "error",
        "message" => $stmt->error
    ]);
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>