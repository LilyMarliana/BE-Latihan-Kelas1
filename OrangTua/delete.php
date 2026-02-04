<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$orangtuaID = isset($_POST['orangtuaID']) ? $_POST['orangtuaID'] : null;

// Validasi input
if (empty($orangtuaID)) {
    echo json_encode([
        "status" => "error",
        "message" => "OrangTuaID wajib diisi"
    ]);
    exit;
}

// Cek apakah orangtua ada
$checkSql = "SELECT orangtuaID FROM orangtua WHERE orangtuaID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $orangtuaID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Orang Tua tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus orangtua
$sql = "DELETE FROM orangtua WHERE orangtuaID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orangtuaID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Orang Tua berhasil dihapus"
    ]);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>