<?php
// Koneksi database
include '../db.php';

// Set response JSON
header('Content-Type: application/json');

$data = [];

// ===============================
// JIKA ADA PARAMETER userBadgeID
// ===============================
if (isset($_GET['userBadgeID'])) {

    $userBadgeID = $_GET['userBadgeID'];

    $sql = "SELECT * FROM userbadge WHERE userBadgeID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userBadgeID);
    $stmt->execute();

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();

} else {

    // ===============================
    // JIKA TANPA PARAMETER (AMBIL SEMUA)
    // ===============================
    $sql = "SELECT * FROM userbadge";
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
}

// ===============================
// RESPONSE JSON
// ===============================
echo json_encode([
    "status"  => "success",
    "message" => count($data) > 0 ? "Data ditemukan" : "Data kosong",
    "data"    => $data
]);

$conn->close();
?>