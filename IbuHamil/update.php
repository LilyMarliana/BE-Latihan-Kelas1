<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$ibuHamilID                      = isset($_POST['ibuHamilID']) ? $_POST['ibuHamilID'] : null;
$orangtuaID                      = isset($_POST['orangtuaID']) ? $_POST['orangtuaID'] : null;
$usiaKehamilan                   = isset($_POST['usiaKehamilan']) ? $_POST['usiaKehamilan'] : null;
$tanggalHPL                      = isset($_POST['tanggalHPL']) ? $_POST['tanggalHPL'] : null;
$trimester                       = isset($_POST['trimester']) ? $_POST['trimester'] : null;
$beratBadanSebelumHamil          = isset($_POST['beratBadanSebelumHamil']) ? $_POST['beratBadanSebelumHamil'] : null;
$tinggiBadan                     = isset($_POST['tinggiBadan']) ? $_POST['tinggiBadan'] : null;
$golonganDarah                   = isset($_POST['golonganDarah']) ? $_POST['golonganDarah'] : null;
$riwayatPenyakit                 = isset($_POST['riwayatPenyakit']) ? $_POST['riwayatPenyakit'] : null;
$riwayatKehamilanSebelumnya      = isset($_POST['riwayatKehamilanSebelumnya']) ? $_POST['riwayatKehamilanSebelumnya'] : null;
$risikoKehamilan                 = isset($_POST['risikoKehamilan']) ? $_POST['risikoKehamilan'] : null;
$statusImunisasi                 = isset($_POST['statusImunisasi']) ? $_POST['statusImunisasi'] : null;

// Validasi input
if (empty($ibuHamilID)) {
    echo json_encode([
        "status" => "error",
        "message" => "IbuHamilID wajib diisi"
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

// Bangun query dinamis berdasarkan field yang diisi
$fields = [];
$params = [];
$paramTypes = '';

if ($orangtuaID !== null) {
    $fields[] = "orangtuaID = ?";
    $params[] = $orangtuaID;
    $paramTypes .= 'i';
}

if ($usiaKehamilan !== null) {
    $fields[] = "usiaKehamilan = ?";
    $params[] = $usiaKehamilan;
    $paramTypes .= 'i';
}

if ($tanggalHPL !== null) {
    $fields[] = "tanggalHPL = ?";
    $params[] = $tanggalHPL;
    $paramTypes .= 's';
}

if ($trimester !== null) {
    $fields[] = "trimester = ?";
    $params[] = $trimester;
    $paramTypes .= 's';
}

if ($beratBadanSebelumHamil !== null) {
    $fields[] = "beratBadanSebelumHamil = ?";
    $params[] = $beratBadanSebelumHamil;
    $paramTypes .= 'd';
}

if ($tinggiBadan !== null) {
    $fields[] = "tinggiBadan = ?";
    $params[] = $tinggiBadan;
    $paramTypes .= 'd';
}

if ($golonganDarah !== null) {
    $fields[] = "golonganDarah = ?";
    $params[] = $golonganDarah;
    $paramTypes .= 's';
}

if ($riwayatPenyakit !== null) {
    $fields[] = "riwayatPenyakit = ?";
    $params[] = $riwayatPenyakit;
    $paramTypes .= 's';
}

if ($riwayatKehamilanSebelumnya !== null) {
    $fields[] = "riwayatKehamilanSebelumnya = ?";
    $params[] = $riwayatKehamilanSebelumnya;
    $paramTypes .= 's';
}

if ($risikoKehamilan !== null) {
    $fields[] = "risikoKehamilan = ?";
    $params[] = $risikoKehamilan;
    $paramTypes .= 's';
}

if ($statusImunisasi !== null) {
    $fields[] = "statusImunisasi = ?";
    $params[] = $statusImunisasi;
    $paramTypes .= 's';
}

if (empty($fields)) {
    echo json_encode([
        "status" => "error",
        "message" => "Tidak ada field yang diupdate"
    ]);
    exit;
}

$fieldsStr = implode(', ', $fields);
$sql = "UPDATE ibuhamil SET {$fieldsStr} WHERE ibuHamilID = ?";
$params[] = $ibuHamilID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Ibu Hamil berhasil diperbarui",
            "data" => [
                "ibuHamilID" => $ibuHamilID,
                "orangtuaID" => $orangtuaID
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Ibu Hamil tidak ditemukan"
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