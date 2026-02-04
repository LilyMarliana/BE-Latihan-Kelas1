<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$konsultasiID   = $_POST['konsultasiID'];
$pengirimID     = $_POST['pengirimID'];
$tipePengirim   = $_POST['tipePengirim'];
$isiPesan       = $_POST['isiPesan'];
$fileLampiran   = $_POST['fileLampiran'];
$tipeFile       = $_POST['tipeFile'];

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO pesan
    (konsultasiID, pengirimID, tipePengirim, isiPesan, fileLampiran, tipeFile)
    VALUES (?, ?, ?, ?, ?, ?)
");

// s = string, i = integer
$stmt->bind_param(
    "iissss",
    $konsultasiID,
    $pengirimID,
    $tipePengirim,
    $isiPesan,
    $fileLampiran,
    $tipeFile
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data pesan berhasil ditambahkan",
        "data" => [
            "pesanID" => $last_id,
            "konsultasiID" => $konsultasiID,
            "pengirimID" => $pengirimID,
            "tipePengirim" => $tipePengirim,
            "isiPesan" => $isiPesan,
            "fileLampiran" => $fileLampiran,
            "tipeFile" => $tipeFile
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