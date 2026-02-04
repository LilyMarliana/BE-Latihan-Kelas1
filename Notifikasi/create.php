<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$userID         = $_POST['userID'];
$jenisNotif     = $_POST['jenisNotif'];
$judul          = $_POST['judul'];
$isiNotif       = $_POST['isiNotif'];
$prioritas      = $_POST['prioritas'];
$actionURL      = $_POST['actionURL'];
$actionLabel    = $_POST['actionLabel'];
$referensiID    = $_POST['referensiID'];
$referensiTipe  = $_POST['referensiTipe'];

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO notifikasi
    (userID, jenisNotif, judul, isiNotif, prioritas, actionURL, actionLabel, referensiID, referensiTipe)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// s = string, i = integer
$stmt->bind_param(
    "isssssssi",
    $userID,
    $jenisNotif,
    $judul,
    $isiNotif,
    $prioritas,
    $actionURL,
    $actionLabel,
    $referensiID,
    $referensiTipe
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data notifikasi berhasil ditambahkan",
        "data" => [
            "notifID" => $last_id,
            "userID" => $userID,
            "jenisNotif" => $jenisNotif,
            "judul" => $judul,
            "isiNotif" => $isiNotif,
            "prioritas" => $prioritas,
            "actionURL" => $actionURL,
            "actionLabel" => $actionLabel,
            "referensiID" => $referensiID,
            "referensiTipe" => $referensiTipe
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