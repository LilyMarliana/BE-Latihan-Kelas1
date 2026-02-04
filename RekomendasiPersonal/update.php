<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$rekomendasiID          = $_POST['rekomendasiID'];
$userID                 = $_POST['userID'];
$anakID                 = $_POST['anakID'];
$jenisRekomendasi       = $_POST['jenisRekomendasi'];
$judulRekomendasi       = $_POST['judulRekomendasi'];
$deskripsi             = $_POST['deskripsi'];
$prioritas             = $_POST['prioritas'];
$targetNutrisi         = $_POST['targetNutrisi'];
$contohMenu            = $_POST['contohMenu'];
$tipsImplementasi      = $_POST['tipsImplementasi'];
$tanggalKadaluarsa     = $_POST['tanggalKadaluarsa'];
$sumberRekomendasi     = $_POST['sumberRekomendasi'];
$pembuatID             = $_POST['pembuatID'];

// query update
$stmt = $conn->prepare("
    UPDATE rekomendasipersonal
    SET
        userID = ?,
        anakID = ?,
        jenisRekomendasi = ?,
        judulRekomendasi = ?,
        deskripsi = ?,
        prioritas = ?,
        targetNutrisi = ?,
        contohMenu = ?,
        tipsImplementasi = ?,
        tanggalKadaluarsa = ?,
        sumberRekomendasi = ?,
        pembuatID = ?
    WHERE rekomendasiID = ?
");

// s = string, i = integer
$stmt->bind_param(
    "iisssssssssii",
    $userID,
    $anakID,
    $jenisRekomendasi,
    $judulRekomendasi,
    $deskripsi,
    $prioritas,
    $targetNutrisi,
    $contohMenu,
    $tipsImplementasi,
    $tanggalKadaluarsa,
    $sumberRekomendasi,
    $pembuatID,
    $rekomendasiID
);

// eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data rekomendasi personal berhasil diperbarui",
        "data" => [
            "rekomendasiID" => $rekomendasiID,
            "userID" => $userID,
            "anakID" => $anakID,
            "jenisRekomendasi" => $jenisRekomendasi,
            "judulRekomendasi" => $judulRekomendasi,
            "deskripsi" => $deskripsi,
            "prioritas" => $prioritas,
            "targetNutrisi" => $targetNutrisi,
            "contohMenu" => $contohMenu,
            "tipsImplementasi" => $tipsImplementasi,
            "tanggalKadaluarsa" => $tanggalKadaluarsa,
            "sumberRekomendasi" => $sumberRekomendasi,
            "pembuatID" => $pembuatID
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