<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$riwayatID          = $_POST['riwayatID'];
$userID             = $_POST['userID'];
$tipeAktivitas      = $_POST['tipeAktivitas'];
$poinDiperoleh      = $_POST['poinDiperoleh'];
$deskripsi          = $_POST['deskripsi'];
$referensiID        = $_POST['referensiID'];

// query update
$stmt = $conn->prepare("
    UPDATE riwayatpoin
    SET
        userID = ?,
        tipeAktivitas = ?,
        poinDiperoleh = ?,
        deskripsi = ?,
        referensiID = ?
    WHERE riwayatID = ?
");

// s = string, i = integer
$stmt->bind_param(
    "isssii",
    $userID,
    $tipeAktivitas,
    $poinDiperoleh,
    $deskripsi,
    $referensiID,
    $riwayatID
);

// eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data riwayat poin berhasil diperbarui",
        "data" => [
            "riwayatID" => $riwayatID,
            "userID" => $userID,
            "tipeAktivitas" => $tipeAktivitas,
            "poinDiperoleh" => $poinDiperoleh,
            "deskripsi" => $deskripsi,
            "referensiID" => $referensiID
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