<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$userID         = $_POST['userID'];
$aktivitas      = $_POST['aktivitas'];
$deskripsi      = $_POST['deskripsi'];
$targetTabel    = $_POST['targetTabel'];
$targetID       = $_POST['targetID'];
$ipAddress      = $_POST['ipAddress'];
$userAgent      = $_POST['userAgent'];
$device         = $_POST['device'];

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO logaktivitas
    (userID, aktivitas, deskripsi, targetTabel, targetID, ipAddress, userAgent, device)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

// s = string, i = integer
$stmt->bind_param(
    "isssisss",
    $userID,
    $aktivitas,
    $deskripsi,
    $targetTabel,
    $targetID,
    $ipAddress,
    $userAgent,
    $device
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data log aktivitas berhasil ditambahkan",
        "data" => [
            "logID" => $last_id,
            "userID" => $userID,
            "aktivitas" => $aktivitas,
            "deskripsi" => $deskripsi,
            "targetTabel" => $targetTabel,
            "targetID" => $targetID,
            "ipAddress" => $ipAddress,
            "userAgent" => $userAgent,
            "device" => $device
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