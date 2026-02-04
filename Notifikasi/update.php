<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$notifID        = $_POST['notifID'];
$userID         = $_POST['userID'];
$jenisNotif     = $_POST['jenisNotif'];
$judul          = $_POST['judul'];
$isiNotif       = $_POST['isiNotif'];
$prioritas      = $_POST['prioritas'];
$actionURL      = $_POST['actionURL'];
$actionLabel    = $_POST['actionLabel'];
$referensiID    = $_POST['referensiID'];
$referensiTipe  = $_POST['referensiTipe'];

// query update
$stmt = $conn->prepare("
    UPDATE notifikasi
    SET
        userID = ?,
        jenisNotif = ?,
        judul = ?,
        isiNotif = ?,
        prioritas = ?,
        actionURL = ?,
        actionLabel = ?,
        referensiID = ?,
        referensiTipe = ?
    WHERE notifID = ?
");

// s = string, i = integer
$stmt->bind_param(
    "isssssssii",
    $userID,
    $jenisNotif,
    $judul,
    $isiNotif,
    $prioritas,
    $actionURL,
    $actionLabel,
    $referensiID,
    $referensiTipe,
    $notifID
);

// eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data notifikasi berhasil diperbarui",
        "data" => [
            "notifID" => $notifID,
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
        "status" => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>