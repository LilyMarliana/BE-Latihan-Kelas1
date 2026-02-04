<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$logID          = $_POST['logID'];
$userID         = $_POST['userID'];
$aktivitas      = $_POST['aktivitas'];
$deskripsi      = $_POST['deskripsi'];
$targetTabel    = $_POST['targetTabel'];
$targetID       = $_POST['targetID'];
$ipAddress      = $_POST['ipAddress'];
$userAgent      = $_POST['userAgent'];
$device         = $_POST['device'];

// query update
$stmt = $conn->prepare("
    UPDATE logaktivitas
    SET
        userID = ?,
        aktivitas = ?,
        deskripsi = ?,
        targetTabel = ?,
        targetID = ?,
        ipAddress = ?,
        userAgent = ?,
        device = ?
    WHERE logID = ?
");

// s = string, i = integer
$stmt->bind_param(
    "isssisssi",
    $userID,
    $aktivitas,
    $deskripsi,
    $targetTabel,
    $targetID,
    $ipAddress,
    $userAgent,
    $device,
    $logID
);

// eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data log aktivitas berhasil diperbarui",
        "data" => [
            "logID" => $logID,
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
        "status" => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>