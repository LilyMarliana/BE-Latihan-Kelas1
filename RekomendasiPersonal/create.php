<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
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

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO rekomendasipersonal
    (userID, anakID, jenisRekomendasi, judulRekomendasi, deskripsi, prioritas, targetNutrisi, contohMenu, tipsImplementasi, tanggalKadaluarsa, sumberRekomendasi, pembuatID)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// s = string, i = integer
$stmt->bind_param(
    "iisssssssssi",
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
    $pembuatID
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data rekomendasi personal berhasil ditambahkan",
        "data" => [
            "rekomendasiID" => $last_id,
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
        "status"  => "error",
        "message" => $stmt->error
    ]);
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>