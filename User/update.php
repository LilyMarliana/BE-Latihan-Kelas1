<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$userID            = isset($_POST['userID']) ? $_POST['userID'] : null;
$email             = isset($_POST['email']) ? $_POST['email'] : null;
$password          = isset($_POST['password']) ? $_POST['password'] : null;  // Password plain text
$role              = isset($_POST['role']) ? $_POST['role'] : null;
$statusAktif       = isset($_POST['statusAktif']) ? $_POST['statusAktif'] : null;
$nomorTelepon      = isset($_POST['nomorTelepon']) ? $_POST['nomorTelepon'] : null;
$fotoProfile       = isset($_POST['fotoProfile']) ? $_POST['fotoProfile'] : null;

// Validasi input
if (empty($userID)) {
    echo json_encode([
        "status" => "error",
        "message" => "UserID wajib diisi"
    ]);
    exit;
}

// Validasi role jika diisi
if ($role !== null) {
    $validRoles = ['orangtua', 'ibu_hamil', 'remaja', 'tenaga_kesehatan'];
    if (!in_array($role, $validRoles)) {
        echo json_encode([
            "status" => "error",
            "message" => "Role tidak valid"
        ]);
        exit;
    }
}

// Bangun query dinamis berdasarkan field yang diisi
$fields = [];
$params = [];
$paramTypes = '';

if ($email !== null) {
    $fields[] = "email = ?";
    $params[] = $email;
    $paramTypes .= 's';
}

if ($password !== null) {
    $fields[] = "passwordHash = ?";  // Simpan ke kolom passwordHash di database
    $params[] = password_hash($password, PASSWORD_DEFAULT);  // Hash password
    $paramTypes .= 's';
}

if ($role !== null) {
    $fields[] = "role = ?";
    $params[] = $role;
    $paramTypes .= 's';
}

if ($statusAktif !== null) {
    $fields[] = "statusAktif = ?";
    $params[] = $statusAktif;
    $paramTypes .= 'i';
}

if ($nomorTelepon !== null) {
    $fields[] = "nomorTelepon = ?";
    $params[] = $nomorTelepon;
    $paramTypes .= 's';
}

if ($fotoProfile !== null) {
    $fields[] = "fotoProfile = ?";
    $params[] = $fotoProfile;
    $paramTypes .= 's';
}

if (empty($fields)) {
    echo json_encode([
        "status" => "error",
        "message" => "Tidak ada field yang diupdate"
    ]);
    exit;
}

$fieldsStr = implode(', ', $fields);
$sql = "UPDATE user SET {$fieldsStr}, tanggalUpdate = CURRENT_TIMESTAMP WHERE userID = ?";
$params[] = $userID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "User berhasil diperbarui",
            "data" => [
                "userID" => $userID,
                "email" => $email,
                "role" => $role,
                "statusAktif" => $statusAktif,
                "nomorTelepon" => $nomorTelepon,
                "fotoProfile" => $fotoProfile
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "User tidak ditemukan"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>