<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST dengan pengecekan
$tanggalPengukuran   = isset($_POST['tanggalPengukuran']) ? $_POST['tanggalPengukuran'] : null;
$usiaSaatUkur        = isset($_POST['usiaSaatUkur']) ? $_POST['usiaSaatUkur'] : null;
$beratBadan          = isset($_POST['beratBadan']) ? $_POST['beratBadan'] : null;
$tinggiBadan         = isset($_POST['tinggiBadan']) ? $_POST['tinggiBadan'] : null;
$anakID              = isset($_POST['anakID']) ? $_POST['anakID'] : null;
$remajaID            = isset($_POST['remajaID']) ? $_POST['remajaID'] : null;
$lingkarKepala       = isset($_POST['lingkarKepala']) ? $_POST['lingkarKepala'] : null;
$lingkarLengan       = isset($_POST['lingkarLengan']) ? $_POST['lingkarLengan'] : null;
$zScoreBeratTinggi   = isset($_POST['zScoreBeratTinggi']) ? $_POST['zScoreBeratTinggi'] : null;
$zScoreTinggiUsia    = isset($_POST['zScoreTinggiUsia']) ? $_POST['zScoreTinggiUsia'] : null;
$zScoreBeratUsia     = isset($_POST['zScoreBeratUsia']) ? $_POST['zScoreBeratUsia'] : null;
$zScoreIMT           = isset($_POST['zScoreIMT']) ? $_POST['zScoreIMT'] : null;
$kategoriStatusGizi  = isset($_POST['kategoriStatusGizi']) ? $_POST['kategoriStatusGizi'] : null;
$kategoriStunting    = isset($_POST['kategoriStunting']) ? $_POST['kategoriStunting'] : 'normal';
$catatanTambahan     = isset($_POST['catatanTambahan']) ? $_POST['catatanTambahan'] : null;
$petugasID           = isset($_POST['petugasID']) ? $_POST['petugasID'] : null;

// Validasi input
if (empty($tanggalPengukuran) || empty($usiaSaatUkur) || empty($beratBadan) || empty($tinggiBadan)) {
    echo json_encode([
        "status" => "error",
        "message" => "Tanggal pengukuran, usia saat ukur, berat badan, dan tinggi badan wajib diisi"
    ]);
    exit;
}

// Harus memiliki salah satu dari anakID atau remajaID
if (empty($anakID) && empty($remajaID)) {
    echo json_encode([
        "status" => "error",
        "message" => "Harus menyertakan anakID atau remajaID"
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

// Bangun query dan parameter dinamis berdasarkan nilai yang tidak null
$fields = [];
$values = [];
$paramTypes = '';
$params = [];

if ($anakID !== null) {
    $fields[] = 'anakID';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $anakID;
} else {
    $fields[] = 'anakID';
    $values[] = 'NULL';
}

if ($remajaID !== null) {
    $fields[] = 'remajaID';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $remajaID;
} else {
    $fields[] = 'remajaID';
    $values[] = 'NULL';
}

$fields[] = 'tanggalPengukuran';
$values[] = '?';
$paramTypes .= 's';
$params[] = $tanggalPengukuran;

$fields[] = 'usiaSaatUkur';
$values[] = '?';
$paramTypes .= 'i';
$params[] = $usiaSaatUkur;

$fields[] = 'beratBadan';
$values[] = '?';
$paramTypes .= 'd';
$params[] = floatval($beratBadan);

$fields[] = 'tinggiBadan';
$values[] = '?';
$paramTypes .= 'd';
$params[] = floatval($tinggiBadan);

if ($lingkarKepala !== null) {
    $fields[] = 'lingkarKepala';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($lingkarKepala);
} else {
    $fields[] = 'lingkarKepala';
    $values[] = 'NULL';
}

if ($lingkarLengan !== null) {
    $fields[] = 'lingkarLengan';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($lingkarLengan);
} else {
    $fields[] = 'lingkarLengan';
    $values[] = 'NULL';
}

if ($zScoreBeratTinggi !== null) {
    $fields[] = 'zScoreBeratTinggi';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($zScoreBeratTinggi);
} else {
    $fields[] = 'zScoreBeratTinggi';
    $values[] = 'NULL';
}

if ($zScoreTinggiUsia !== null) {
    $fields[] = 'zScoreTinggiUsia';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($zScoreTinggiUsia);
} else {
    $fields[] = 'zScoreTinggiUsia';
    $values[] = 'NULL';
}

if ($zScoreBeratUsia !== null) {
    $fields[] = 'zScoreBeratUsia';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($zScoreBeratUsia);
} else {
    $fields[] = 'zScoreBeratUsia';
    $values[] = 'NULL';
}

if ($zScoreIMT !== null) {
    $fields[] = 'zScoreIMT';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($zScoreIMT);
} else {
    $fields[] = 'zScoreIMT';
    $values[] = 'NULL';
}

if ($kategoriStatusGizi !== null) {
    $fields[] = 'kategoriStatusGizi';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $kategoriStatusGizi;
} else {
    $fields[] = 'kategoriStatusGizi';
    $values[] = 'NULL';
}

if ($kategoriStunting !== null) {
    $fields[] = 'kategoriStunting';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $kategoriStunting;
} else {
    $fields[] = 'kategoriStunting';
    $values[] = 'NULL';
}

if ($catatanTambahan !== null) {
    $fields[] = 'catatanTambahan';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $catatanTambahan;
} else {
    $fields[] = 'catatanTambahan';
    $values[] = 'NULL';
}

if ($petugasID !== null) {
    $fields[] = 'petugasID';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $petugasID;
} else {
    $fields[] = 'petugasID';
    $values[] = 'NULL';
}

// Gabungkan field dan value untuk query
$fieldsStr = implode(', ', $fields);
$valuesStr = implode(', ', $values);

$sql = "INSERT INTO dataantropometri ({$fieldsStr}) VALUES ({$valuesStr})";
$stmt = $conn->prepare($sql);

// Bind parameter hanya jika ada parameter yang perlu di-bind
if (!empty($params)) {
    $stmt->bind_param($paramTypes, ...$params);
}

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data Antropometri berhasil ditambahkan",
        "data" => [
            "dataID" => $last_id,
            "tanggalPengukuran" => $tanggalPengukuran,
            "usiaSaatUkur" => $usiaSaatUkur,
            "beratBadan" => $beratBadan,
            "tinggiBadan" => $tinggiBadan
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