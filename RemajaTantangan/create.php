<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$remajaID           = $_POST['remajaID'];
$tantanganID        = $_POST['tantanganID'];
$progressSaatIni    = $_POST['progressSaatIni'];
$targetValue        = $_POST['targetValue'];
$persentaseSelesai  = $_POST['persentaseSelesai'];
$poinDiperoleh      = $_POST['poinDiperoleh'];

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO remajatantangan
    (remajaID, tantanganID, progressSaatIni, targetValue, persentaseSelesai, poinDiperoleh)
    VALUES (?, ?, ?, ?, ?, ?)
");

// s = string, i = integer, d = decimal
$stmt->bind_param(
    "iidddi",
    $remajaID,
    $tantanganID,
    $progressSaatIni,
    $targetValue,
    $persentaseSelesai,
    $poinDiperoleh
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data remaja tantangan berhasil ditambahkan",
        "data" => [
            "partisipasiID" => $last_id,
            "remajaID" => $remajaID,
            "tantanganID" => $tantanganID,
            "progressSaatIni" => $progressSaatIni,
            "targetValue" => $targetValue,
            "persentaseSelesai" => $persentaseSelesai,
            "poinDiperoleh" => $poinDiperoleh
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