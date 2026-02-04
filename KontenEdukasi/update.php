<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$kontenID           = $_POST['kontenID'];
$judul              = $_POST['judul'];
$slug               = $_POST['slug'];
$kategori           = $_POST['kategori'];
$targetSegmen       = $_POST['targetSegmen'];
$formatKonten       = $_POST['formatKonten'];
$isiKonten          = $_POST['isiKonten'];
$excerptSingkat     = $_POST['excerptSingkat'];
$gambarUtama        = $_POST['gambarUtama'];
$urlVideo           = $_POST['urlVideo'];
$urlFile            = $_POST['urlFile'];
$authorID           = $_POST['authorID'];
$sumberReferensi    = $_POST['sumberReferensi'];
$tags               = $_POST['tags'];
$statusPublish      = $_POST['statusPublish'];

// query update
$stmt = $conn->prepare("
    UPDATE kontenedukasi
    SET
        judul = ?,
        slug = ?,
        kategori = ?,
        targetSegmen = ?,
        formatKonten = ?,
        isiKonten = ?,
        excerptSingkat = ?,
        gambarUtama = ?,
        urlVideo = ?,
        urlFile = ?,
        authorID = ?,
        sumberReferensi = ?,
        tags = ?,
        statusPublish = ?
    WHERE kontenID = ?
");

// s = string, i = integer
$stmt->bind_param(
    "ssssssssssiissi",
    $judul,
    $slug,
    $kategori,
    $targetSegmen,
    $formatKonten,
    $isiKonten,
    $excerptSingkat,
    $gambarUtama,
    $urlVideo,
    $urlFile,
    $authorID,
    $sumberReferensi,
    $tags,
    $statusPublish,
    $kontenID
);

// eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data konten edukasi berhasil diperbarui",
        "data" => [
            "kontenID" => $kontenID,
            "judul" => $judul,
            "slug" => $slug,
            "kategori" => $kategori,
            "targetSegmen" => $targetSegmen,
            "formatKonten" => $formatKonten,
            "isiKonten" => $isiKonten,
            "excerptSingkat" => $excerptSingkat,
            "gambarUtama" => $gambarUtama,
            "urlVideo" => $urlVideo,
            "urlFile" => $urlFile,
            "authorID" => $authorID,
            "sumberReferensi" => $sumberReferensi,
            "tags" => $tags,
            "statusPublish" => $statusPublish
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