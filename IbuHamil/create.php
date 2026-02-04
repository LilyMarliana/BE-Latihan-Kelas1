<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$orangtuaID                      = $_POST['orangtuaID'];
$usiaKehamilan                   = isset($_POST['usiaKehamilan']) ? $_POST['usiaKehamilan'] : null;
$tanggalHPL                      = isset($_POST['tanggalHPL']) ? $_POST['tanggalHPL'] : null;
$trimester                       = isset($_POST['trimester']) ? $_POST['trimester'] : null;
$beratBadanSebelumHamil          = isset($_POST['beratBadanSebelumHamil']) ? $_POST['beratBadanSebelumHamil'] : null;
$tinggiBadan                     = isset($_POST['tinggiBadan']) ? $_POST['tinggiBadan'] : null;
$golonganDarah                   = isset($_POST['golonganDarah']) ? $_POST['golonganDarah'] : null;
$riwayatPenyakit                 = isset($_POST['riwayatPenyakit']) ? $_POST['riwayatPenyakit'] : null;
$riwayatKehamilanSebelumnya      = isset($_POST['riwayatKehamilanSebelumnya']) ? $_POST['riwayatKehamilanSebelumnya'] : null;
$risikoKehamilan                 = isset($_POST['risikoKehamilan']) ? $_POST['risikoKehamilan'] : 'rendah';
$statusImunisasi                 = isset($_POST['statusImunisasi']) ? $_POST['statusImunisasi'] : null;

// Validasi input
if (empty($orangtuaID)) {
    echo json_encode([
        "status" => "error",
        "message" => "OrangTuaID wajib diisi"
    ]);
    exit;
}

// Validasi trimester jika diisi
if ($trimester !== null && !in_array($trimester, ['1', '2', '3'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Trimester harus 1, 2, atau 3"
    ]);
    exit;
}

// Validasi risikoKehamilan jika diisi
if ($risikoKehamilan !== null) {
    $validRisiko = ['rendah', 'sedang', 'tinggi'];
    if (!in_array($risikoKehamilan, $validRisiko)) {
        echo json_encode([
            "status" => "error",
            "message" => "Risiko kehamilan tidak valid"
        ]);
        exit;
    }
}

// Validasi golongan darah jika diisi
if ($golonganDarah !== null) {
    $validGolDarah = ['A', 'B', 'AB', 'O'];
    if (!in_array($golonganDarah, $validGolDarah)) {
        echo json_encode([
            "status" => "error",
            "message" => "Golongan darah tidak valid"
        ]);
        exit;
    }
}

// Bangun query dan parameter dinamis berdasarkan nilai yang tidak null
$fields = [];
$values = [];
$paramTypes = '';
$params = [];

$fields[] = 'orangtuaID';
$values[] = '?';
$paramTypes .= 'i';
$params[] = $orangtuaID;

if ($usiaKehamilan !== null) {
    $fields[] = 'usiaKehamilan';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $usiaKehamilan;
}

if ($tanggalHPL !== null) {
    $fields[] = 'tanggalHPL';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $tanggalHPL;
}

if ($trimester !== null) {
    $fields[] = 'trimester';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $trimester;
}

if ($beratBadanSebelumHamil !== null) {
    $fields[] = 'beratBadanSebelumHamil';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($beratBadanSebelumHamil);
}

if ($tinggiBadan !== null) {
    $fields[] = 'tinggiBadan';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($tinggiBadan);
}

if ($golonganDarah !== null) {
    $fields[] = 'golonganDarah';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $golonganDarah;
}

if ($riwayatPenyakit !== null) {
    $fields[] = 'riwayatPenyakit';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $riwayatPenyakit;
}

if ($riwayatKehamilanSebelumnya !== null) {
    $fields[] = 'riwayatKehamilanSebelumnya';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $riwayatKehamilanSebelumnya;
}

if ($risikoKehamilan !== null) {
    $fields[] = 'risikoKehamilan';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $risikoKehamilan;
}

if ($statusImunisasi !== null) {
    $fields[] = 'statusImunisasi';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $statusImunisasi;
}

// Gabungkan field dan value untuk query
$fieldsStr = implode(', ', $fields);
$valuesStr = implode(', ', $values);

$sql = "INSERT INTO ibuhamil ({$fieldsStr}) VALUES ({$valuesStr})";
$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Ibu Hamil berhasil ditambahkan",
        "data" => [
            "ibuHamilID" => $last_id,
            "orangtuaID" => $orangtuaID
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