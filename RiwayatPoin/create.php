<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$userID             = $_POST['userID'];
$tipeAktivitas      = $_POST['tipeAktivitas'];
$poinDiperoleh      = $_POST['poinDiperoleh'];
$deskripsi          = $_POST['deskripsi'];
$referensiID        = $_POST['referensiID'];

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO riwayatpoin
    (userID, tipeAktivitas, poinDiperoleh, deskripsi, referensiID)
    VALUES (?, ?, ?, ?, ?)
");

// s = string, i = integer
$stmt->bind_param(
    "isssi",
    $userID,
    $tipeAktivitas,
    $poinDiperoleh,
    $deskripsi,
    $referensiID
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data riwayat poin berhasil ditambahkan",
        "data" => [
            "riwayatID" => $last_id,
            "userID" => $userID,
            "tipeAktivitas" => $tipeAktivitas,
            "poinDiperoleh" => $poinDiperoleh,
            "deskripsi" => $deskripsi,
            "referensiID" => $referensiID
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