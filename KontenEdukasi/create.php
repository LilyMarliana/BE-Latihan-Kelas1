<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST dengan pengecekan
$judul              = isset($_POST['judul']) ? $_POST['judul'] : null;
$slug               = isset($_POST['slug']) ? $_POST['slug'] : null;
$kategori           = isset($_POST['kategori']) ? $_POST['kategori'] : null;
$targetSegmen       = isset($_POST['targetSegmen']) ? $_POST['targetSegmen'] : null;
$formatKonten       = isset($_POST['formatKonten']) ? $_POST['formatKonten'] : null;
$isiKonten          = isset($_POST['isiKonten']) ? $_POST['isiKonten'] : null;
$excerptSingkat     = isset($_POST['excerptSingkat']) ? $_POST['excerptSingkat'] : null;
$gambarUtama        = isset($_POST['gambarUtama']) ? $_POST['gambarUtama'] : null;
$urlVideo           = isset($_POST['urlVideo']) ? $_POST['urlVideo'] : null;
$urlFile            = isset($_POST['urlFile']) ? $_POST['urlFile'] : null;
$authorID           = isset($_POST['authorID']) ? $_POST['authorID'] : null;
$sumberReferensi    = isset($_POST['sumberReferensi']) ? $_POST['sumberReferensi'] : null;
$tags               = isset($_POST['tags']) ? $_POST['tags'] : null;
$statusPublish      = isset($_POST['statusPublish']) ? $_POST['statusPublish'] : 'published';

// Validasi input wajib
if (empty($judul) || empty($slug) || empty($kategori) || empty($targetSegmen) || empty($formatKonten)) {
    echo json_encode([
        "status" => "error",
        "message" => "judul, slug, kategori, targetSegmen, dan formatKonten wajib diisi"
    ]);
    exit;
}

// Validasi kategori
$validKategori = ['stunting', 'gizi_balita', 'gizi_remaja', 'kehamilan', 'parenting', 'resep_sehat'];
if (!in_array($kategori, $validKategori)) {
    echo json_encode([
        "status" => "error",
        "message" => "Kategori tidak valid. Pilihan yang valid: " . implode(', ', $validKategori)
    ]);
    exit;
}

// Validasi targetSegmen
$validTargetSegmen = ['orangtua', 'remaja', 'ibu_hamil', 'umum'];
if (!in_array($targetSegmen, $validTargetSegmen)) {
    echo json_encode([
        "status" => "error",
        "message" => "Target segmen tidak valid. Pilihan yang valid: " . implode(', ', $validTargetSegmen)
    ]);
    exit;
}

// Validasi formatKonten
$validFormat = ['artikel', 'video', 'infografis', 'quiz', 'podcast'];
if (!in_array($formatKonten, $validFormat)) {
    echo json_encode([
        "status" => "error",
        "message" => "Format konten tidak valid. Pilihan yang valid: " . implode(', ', $validFormat)
    ]);
    exit;
}

// Validasi statusPublish
$validStatus = ['draft', 'published', 'archived'];
if (!in_array($statusPublish, $validStatus)) {
    echo json_encode([
        "status" => "error",
        "message" => "Status publish tidak valid. Pilihan yang valid: " . implode(', ', $validStatus)
    ]);
    exit;
}

// Bangun query dan parameter dinamis berdasarkan nilai yang tidak null
$fields = [];
$values = [];
$paramTypes = '';
$params = [];

$fields[] = 'judul';
$values[] = '?';
$paramTypes .= 's';
$params[] = $judul;

$fields[] = 'slug';
$values[] = '?';
$paramTypes .= 's';
$params[] = $slug;

$fields[] = 'kategori';
$values[] = '?';
$paramTypes .= 's';
$params[] = $kategori;

$fields[] = 'targetSegmen';
$values[] = '?';
$paramTypes .= 's';
$params[] = $targetSegmen;

$fields[] = 'formatKonten';
$values[] = '?';
$paramTypes .= 's';
$params[] = $formatKonten;

if ($isiKonten !== null) {
    $fields[] = 'isiKonten';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $isiKonten;
}

if ($excerptSingkat !== null) {
    $fields[] = 'excerptSingkat';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $excerptSingkat;
}

if ($gambarUtama !== null) {
    $fields[] = 'gambarUtama';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $gambarUtama;
}

if ($urlVideo !== null) {
    $fields[] = 'urlVideo';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $urlVideo;
}

if ($urlFile !== null) {
    $fields[] = 'urlFile';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $urlFile;
}

if ($authorID !== null) {
    $fields[] = 'authorID';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $authorID;
}

if ($sumberReferensi !== null) {
    $fields[] = 'sumberReferensi';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $sumberReferensi;
}

if ($tags !== null) {
    $fields[] = 'tags';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $tags;
}

$fields[] = 'statusPublish';
$values[] = '?';
$paramTypes .= 's';
$params[] = $statusPublish;

// Gabungkan field dan value untuk query
$fieldsStr = implode(', ', $fields);
$valuesStr = implode(', ', $values);

$sql = "INSERT INTO kontenedukasi ({$fieldsStr}) VALUES ({$valuesStr})";
$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data konten edukasi berhasil ditambahkan",
        "data" => [
            "kontenID" => $last_id,
            "judul" => $judul,
            "slug" => $slug,
            "kategori" => $kategori,
            "targetSegmen" => $targetSegmen,
            "formatKonten" => $formatKonten,
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
        "status"  => "error",
        "message" => $stmt->error
    ]);
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>