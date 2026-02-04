<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$partisipasiID      = $_POST['partisipasiID'];
$remajaID           = $_POST['remajaID'];
$tantanganID        = $_POST['tantanganID'];
$progressSaatIni    = $_POST['progressSaatIni'];
$targetValue        = $_POST['targetValue'];
$persentaseSelesai  = $_POST['persentaseSelesai'];
$poinDiperoleh      = $_POST['poinDiperoleh'];

// query update
$stmt = $conn->prepare("
    UPDATE remajatantangan
    SET
        remajaID = ?,
        tantanganID = ?,
        progressSaatIni = ?,
        targetValue = ?,
        persentaseSelesai = ?,
        poinDiperoleh = ?
    WHERE partisipasiID = ?
");

// s = string, i = integer, d = decimal
$stmt->bind_param(
    "iidddii",
    $remajaID,
    $tantanganID,
    $progressSaatIni,
    $targetValue,
    $persentaseSelesai,
    $poinDiperoleh,
    $partisipasiID
);

// eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data remaja tantangan berhasil diperbarui",
        "data" => [
            "partisipasiID" => $partisipasiID,
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
        "status" => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>