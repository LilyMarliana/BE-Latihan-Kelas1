<?php
// Koneksi database
include '../db.php';

// Set response JSON
header('Content-Type: application/json');

$data = [];

// ===============================
// JIKA ADA PARAMETER ibuHamilID
// ===============================
if (isset($_GET['ibuHamilID'])) {

    $ibuHamilID = $_GET['ibuHamilID'];

    // Validasi input
    if (empty($ibuHamilID)) {
        echo json_encode([
            "status" => "error",
            "message" => "IbuHamilID wajib diisi"
        ]);
        exit;
    }

    $sql = "SELECT * FROM ibuhamil WHERE ibuHamilID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ibuHamilID);
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
    $sql = "SELECT * FROM ibuhamil ORDER BY ibuHamilID DESC";
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