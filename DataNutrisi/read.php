<?php
// Koneksi database
include '../db.php';

// Set response JSON
header('Content-Type: application/json');

$data = [];

// ===============================
// JIKA ADA PARAMETER nutrisiID
// ===============================
if (isset($_GET['nutrisiID'])) {

    $nutrisiID = $_GET['nutrisiID'];

    // Validasi input
    if (empty($nutrisiID)) {
        echo json_encode([
            "status" => "error",
            "message" => "NutrisiID wajib diisi"
        ]);
        exit;
    }

    $sql = "SELECT * FROM datanutrisi WHERE nutrisiID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $nutrisiID);
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
    $sql = "SELECT * FROM datanutrisi ORDER BY tanggalCatat DESC";
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