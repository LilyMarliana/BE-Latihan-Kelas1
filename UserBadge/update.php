<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$userBadgeID      = $_POST['userBadgeID'];
$userID           = $_POST['userID'];
$badgeID          = $_POST['badgeID'];

// query update
$stmt = $conn->prepare("
    UPDATE userbadge
    SET
        userID = ?,
        badgeID = ?
    WHERE userBadgeID = ?
");

// i = integer
$stmt->bind_param(
    "iii",
    $userID,
    $badgeID,
    $userBadgeID
);

// eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data user badge berhasil diperbarui",
        "data" => [
            "userBadgeID" => $userBadgeID,
            "userID" => $userID,
            "badgeID" => $badgeID
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