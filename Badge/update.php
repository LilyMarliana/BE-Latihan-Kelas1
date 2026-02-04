<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$badgeID            = isset($_POST['badgeID']) ? $_POST['badgeID'] : null;
$namaBadge          = isset($_POST['namaBadge']) ? $_POST['namaBadge'] : null;
$deskripsi          = isset($_POST['deskripsi']) ? $_POST['deskripsi'] : null;
$iconBadge          = isset($_POST['iconBadge']) ? $_POST['iconBadge'] : null;
$kategori           = isset($_POST['kategori']) ? $_POST['kategori'] : null;
$syaratPerolehan    = isset($_POST['syaratPerolehan']) ? $_POST['syaratPerolehan'] : null;
$poinReward         = isset($_POST['poinReward']) ? $_POST['poinReward'] : null;
$tingkatKesulitan   = isset($_POST['tingkatKesulitan']) ? $_POST['tingkatKesulitan'] : null;

// Validasi input
if (empty($badgeID)) {
    echo json_encode([
        "status" => "error",
        "message" => "BadgeID wajib diisi"
    ]);
    exit;
}

// query update
$stmt = $conn->prepare("
    UPDATE badge
    SET
        namaBadge = ?,
        deskripsi = ?,
        iconBadge = ?,
        kategori = ?,
        syaratPerolehan = ?,
        poinReward = ?,
        tingkatKesulitan = ?
    WHERE badgeID = ?
");

// s = string, i = integer
$stmt->bind_param(
    "sssssssi",
    $namaBadge,
    $deskripsi,
    $iconBadge,
    $kategori,
    $syaratPerolehan,
    $poinReward,
    $tingkatKesulitan,
    $badgeID
);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Data badge berhasil diperbarui",
            "data" => [
                "badgeID" => $badgeID,
                "namaBadge" => $namaBadge,
                "deskripsi" => $deskripsi,
                "iconBadge" => $iconBadge,
                "kategori" => $kategori,
                "syaratPerolehan" => $syaratPerolehan,
                "poinReward" => $poinReward,
                "tingkatKesulitan" => $tingkatKesulitan
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Badge tidak ditemukan"
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