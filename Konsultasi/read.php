<?php
// Koneksi database
include '../db.php';

// Set response JSON
header('Content-Type: application/json');

$data = [];

// ===============================
// JIKA ADA PARAMETER konsultasiID
// ===============================
if (isset($_GET['konsultasiID'])) {

    $konsultasiID = $_GET['konsultasiID'];

    // Validasi input
    if (empty($konsultasiID)) {
        echo json_encode([
            "status" => "error",
            "message" => "KonsultasiID wajib diisi"
        ]);
        exit;
    }

    $sql = "SELECT * FROM konsultasi WHERE konsultasiID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $konsultasiID);
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
    $sql = "SELECT * FROM konsultasi ORDER BY tanggalJadwal DESC";
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