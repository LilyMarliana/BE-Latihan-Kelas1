<?php
// Koneksi database
include '../db.php';

// Set response JSON
header('Content-Type: application/json');

$data = [];

// ===============================
// JIKA ADA PARAMETER badgeID
// ===============================
if (isset($_GET['badgeID'])) {

    $badgeID = $_GET['badgeID'];

    // Validasi input
    if (empty($badgeID)) {
        echo json_encode([
            "status" => "error",
            "message" => "BadgeID wajib diisi"
        ]);
        exit;
    }

    $sql = "SELECT * FROM badge WHERE badgeID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $badgeID);
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
    $sql = "SELECT * FROM badge";
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