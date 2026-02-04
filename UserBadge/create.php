<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$userID         = $_POST['userID'];
$badgeID        = $_POST['badgeID'];

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO userbadge
    (userID, badgeID)
    VALUES (?, ?)
");

// i = integer
$stmt->bind_param(
    "ii",
    $userID,
    $badgeID
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data user badge berhasil ditambahkan",
        "data" => [
            "userBadgeID" => $last_id,
            "userID" => $userID,
            "badgeID" => $badgeID
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