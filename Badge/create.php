<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$namaBadge          = $_POST['namaBadge'];
$deskripsi          = $_POST['deskripsi'];
$iconBadge          = $_POST['iconBadge'];
$kategori           = $_POST['kategori'];
$syaratPerolehan    = $_POST['syaratPerolehan'];
$poinReward         = $_POST['poinReward'];
$tingkatKesulitan   = $_POST['tingkatKesulitan'];

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO badge
    (namaBadge, deskripsi, iconBadge, kategori, syaratPerolehan, poinReward, tingkatKesulitan)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

// s = string
$stmt->bind_param(
    "sssssss",
    $namaBadge,
    $deskripsi,
    $iconBadge,
    $kategori,
    $syaratPerolehan,
    $poinReward,
    $tingkatKesulitan
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data badge berhasil ditambahkan",
        "data" => [
            "badgeID" => $last_id,
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
        "status"  => "error",
        "message" => $stmt->error
    ]);
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>