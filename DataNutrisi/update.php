<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$nutrisiID           = isset($_POST['nutrisiID']) ? $_POST['nutrisiID'] : null;
$userID              = isset($_POST['userID']) ? $_POST['userID'] : null;
$anakID              = isset($_POST['anakID']) ? $_POST['anakID'] : null;
$tanggalCatat        = isset($_POST['tanggalCatat']) ? $_POST['tanggalCatat'] : null;
$totalKalori         = isset($_POST['totalKalori']) ? $_POST['totalKalori'] : null;
$totalProtein        = isset($_POST['totalProtein']) ? $_POST['totalProtein'] : null;
$totalKarbohidrat    = isset($_POST['totalKarbohidrat']) ? $_POST['totalKarbohidrat'] : null;
$totalLemak          = isset($_POST['totalLemak']) ? $_POST['totalLemak'] : null;
$totalSerat          = isset($_POST['totalSerat']) ? $_POST['totalSerat'] : null;
$catatanHarian       = isset($_POST['catatanHarian']) ? $_POST['catatanHarian'] : null;

// Validasi input
if (empty($nutrisiID)) {
    echo json_encode([
        "status" => "error",
        "message" => "NutrisiID wajib diisi"
    ]);
    exit;
}

// Bangun query dinamis berdasarkan field yang diisi
$fields = [];
$params = [];
$paramTypes = '';

if ($userID !== null) {
    $fields[] = "userID = ?";
    $params[] = $userID;
    $paramTypes .= 'i';
}

if ($anakID !== null) {
    $fields[] = "anakID = ?";
    $params[] = $anakID;
    $paramTypes .= 'i';
}

if ($tanggalCatat !== null) {
    $fields[] = "tanggalCatat = ?";
    $params[] = $tanggalCatat;
    $paramTypes .= 's';
}

if ($totalKalori !== null) {
    $fields[] = "totalKalori = ?";
    $params[] = $totalKalori;
    $paramTypes .= 'd';
}

if ($totalProtein !== null) {
    $fields[] = "totalProtein = ?";
    $params[] = $totalProtein;
    $paramTypes .= 'd';
}

if ($totalKarbohidrat !== null) {
    $fields[] = "totalKarbohidrat = ?";
    $params[] = $totalKarbohidrat;
    $paramTypes .= 'd';
}

if ($totalLemak !== null) {
    $fields[] = "totalLemak = ?";
    $params[] = $totalLemak;
    $paramTypes .= 'd';
}

if ($totalSerat !== null) {
    $fields[] = "totalSerat = ?";
    $params[] = $totalSerat;
    $paramTypes .= 'd';
}

if ($catatanHarian !== null) {
    $fields[] = "catatanHarian = ?";
    $params[] = $catatanHarian;
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
$sql = "UPDATE datanutrisi SET {$fieldsStr} WHERE nutrisiID = ?";
$params[] = $nutrisiID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Data Nutrisi berhasil diperbarui",
            "data" => [
                "nutrisiID" => $nutrisiID,
                "tanggalCatat" => $tanggalCatat,
                "totalKalori" => $totalKalori
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Data Nutrisi tidak ditemukan"
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