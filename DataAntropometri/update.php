<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST) dengan pengecekan
$dataID              = isset($_POST['dataID']) ? $_POST['dataID'] : null;
$anakID              = isset($_POST['anakID']) ? $_POST['anakID'] : null;
$remajaID            = isset($_POST['remajaID']) ? $_POST['remajaID'] : null;
$tanggalPengukuran   = isset($_POST['tanggalPengukuran']) ? $_POST['tanggalPengukuran'] : null;
$usiaSaatUkur        = isset($_POST['usiaSaatUkur']) ? $_POST['usiaSaatUkur'] : null;
$beratBadan          = isset($_POST['beratBadan']) ? $_POST['beratBadan'] : null;
$tinggiBadan         = isset($_POST['tinggiBadan']) ? $_POST['tinggiBadan'] : null;
$lingkarKepala       = isset($_POST['lingkarKepala']) ? $_POST['lingkarKepala'] : null;
$lingkarLengan       = isset($_POST['lingkarLengan']) ? $_POST['lingkarLengan'] : null;
$zScoreBeratTinggi   = isset($_POST['zScoreBeratTinggi']) ? $_POST['zScoreBeratTinggi'] : null;
$zScoreTinggiUsia    = isset($_POST['zScoreTinggiUsia']) ? $_POST['zScoreTinggiUsia'] : null;
$zScoreBeratUsia     = isset($_POST['zScoreBeratUsia']) ? $_POST['zScoreBeratUsia'] : null;
$zScoreIMT           = isset($_POST['zScoreIMT']) ? $_POST['zScoreIMT'] : null;
$kategoriStatusGizi  = isset($_POST['kategoriStatusGizi']) ? $_POST['kategoriStatusGizi'] : null;
$kategoriStunting    = isset($_POST['kategoriStunting']) ? $_POST['kategoriStunting'] : null;
$catatanTambahan     = isset($_POST['catatanTambahan']) ? $_POST['catatanTambahan'] : null;
$petugasID           = isset($_POST['petugasID']) ? $_POST['petugasID'] : null;

// Validasi dataID wajib diisi
if (empty($dataID)) {
    echo json_encode([
        "status" => "error",
        "message" => "dataID wajib diisi"
    ]);
    exit;
}

// Validasi kategoriStatusGizi jika diisi
if ($kategoriStatusGizi !== null) {
    $validKategori = ['gizi_buruk', 'gizi_kurang', 'gizi_baik', 'berisiko_gizi_lebih', 'gizi_lebih', 'obesitas'];
    if (!in_array($kategoriStatusGizi, $validKategori)) {
        echo json_encode([
            "status" => "error",
            "message" => "Kategori status gizi tidak valid"
        ]);
        exit;
    }
}

// Validasi kategoriStunting jika diisi
if ($kategoriStunting !== null) {
    $validStunting = ['sangat_pendek', 'pendek', 'normal', 'tinggi'];
    if (!in_array($kategoriStunting, $validStunting)) {
        echo json_encode([
            "status" => "error",
            "message" => "Kategori stunting tidak valid"
        ]);
        exit;
    }
}

// Bangun query dinamis berdasarkan field yang diisi
$fields = [];
$params = [];
$paramTypes = '';

if ($anakID !== null) {
    $fields[] = "anakID = ?";
    $params[] = $anakID;
    $paramTypes .= 'i';
}

if ($remajaID !== null) {
    $fields[] = "remajaID = ?";
    $params[] = $remajaID;
    $paramTypes .= 'i';
}

if ($tanggalPengukuran !== null) {
    $fields[] = "tanggalPengukuran = ?";
    $params[] = $tanggalPengukuran;
    $paramTypes .= 's';
}

if ($usiaSaatUkur !== null) {
    $fields[] = "usiaSaatUkur = ?";
    $params[] = $usiaSaatUkur;
    $paramTypes .= 'i';
}

if ($beratBadan !== null) {
    $fields[] = "beratBadan = ?";
    $params[] = $beratBadan;
    $paramTypes .= 'd';
}

if ($tinggiBadan !== null) {
    $fields[] = "tinggiBadan = ?";
    $params[] = $tinggiBadan;
    $paramTypes .= 'd';
}

if ($lingkarKepala !== null) {
    $fields[] = "lingkarKepala = ?";
    $params[] = $lingkarKepala;
    $paramTypes .= 'd';
}

if ($lingkarLengan !== null) {
    $fields[] = "lingkarLengan = ?";
    $params[] = $lingkarLengan;
    $paramTypes .= 'd';
}

if ($zScoreBeratTinggi !== null) {
    $fields[] = "zScoreBeratTinggi = ?";
    $params[] = $zScoreBeratTinggi;
    $paramTypes .= 'd';
}

if ($zScoreTinggiUsia !== null) {
    $fields[] = "zScoreTinggiUsia = ?";
    $params[] = $zScoreTinggiUsia;
    $paramTypes .= 'd';
}

if ($zScoreBeratUsia !== null) {
    $fields[] = "zScoreBeratUsia = ?";
    $params[] = $zScoreBeratUsia;
    $paramTypes .= 'd';
}

if ($zScoreIMT !== null) {
    $fields[] = "zScoreIMT = ?";
    $params[] = $zScoreIMT;
    $paramTypes .= 'd';
}

if ($kategoriStatusGizi !== null) {
    $fields[] = "kategoriStatusGizi = ?";
    $params[] = $kategoriStatusGizi;
    $paramTypes .= 's';
}

if ($kategoriStunting !== null) {
    $fields[] = "kategoriStunting = ?";
    $params[] = $kategoriStunting;
    $paramTypes .= 's';
}

if ($catatanTambahan !== null) {
    $fields[] = "catatanTambahan = ?";
    $params[] = $catatanTambahan;
    $paramTypes .= 's';
}

if ($petugasID !== null) {
    $fields[] = "petugasID = ?";
    $params[] = $petugasID;
    $paramTypes .= 'i';
}

if (empty($fields)) {
    echo json_encode([
        "status" => "error",
        "message" => "Tidak ada field yang diupdate"
    ]);
    exit;
}

$fieldsStr = implode(', ', $fields);
$sql = "UPDATE dataantropometri SET {$fieldsStr} WHERE dataID = ?";
$params[] = $dataID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Data Antropometri berhasil diperbarui",
            "data" => [
                "dataID" => $dataID,
                "tanggalPengukuran" => $tanggalPengukuran,
                "beratBadan" => $beratBadan,
                "tinggiBadan" => $tinggiBadan
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Data Antropometri tidak ditemukan"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>