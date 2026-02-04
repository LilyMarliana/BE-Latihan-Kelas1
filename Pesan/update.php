<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$pesanID        = $_POST['pesanID'];
$konsultasiID   = $_POST['konsultasiID'];
$pengirimID     = $_POST['pengirimID'];
$tipePengirim   = $_POST['tipePengirim'];
$isiPesan       = $_POST['isiPesan'];
$fileLampiran   = $_POST['fileLampiran'];
$tipeFile       = $_POST['tipeFile'];

// query update
$stmt = $conn->prepare("
    UPDATE pesan
    SET
        konsultasiID = ?,
        pengirimID = ?,
        tipePengirim = ?,
        isiPesan = ?,
        fileLampiran = ?,
        tipeFile = ?
    WHERE pesanID = ?
");

// s = string, i = integer
$stmt->bind_param(
    "iissssi",
    $konsultasiID,
    $pengirimID,
    $tipePengirim,
    $isiPesan,
    $fileLampiran,
    $tipeFile,
    $pesanID
);

// eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data pesan berhasil diperbarui",
        "data" => [
            "pesanID" => $pesanID,
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
        "status" => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>