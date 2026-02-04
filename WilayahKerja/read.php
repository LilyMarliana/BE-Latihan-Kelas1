<?php
// Koneksi database
include '../db.php';

// Set response JSON
header('Content-Type: application/json');

$data = [];

// ===============================
// JIKA ADA PARAMETER wilayahID
// ===============================
if (isset($_GET['wilayahID'])) {

    $wilayahID = $_GET['wilayahID'];

    $sql = "SELECT * FROM wilayahkerja WHERE wilayahID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $wilayahID);
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
    $sql = "SELECT * FROM wilayahkerja";
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