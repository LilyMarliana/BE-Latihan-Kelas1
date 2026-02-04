<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST dengan pengecekan
$tanggalCatat        = isset($_POST['tanggalCatat']) ? $_POST['tanggalCatat'] : null;
$userID              = isset($_POST['userID']) ? $_POST['userID'] : null;
$anakID              = isset($_POST['anakID']) ? $_POST['anakID'] : null;
$totalKalori         = isset($_POST['totalKalori']) ? $_POST['totalKalori'] : 0;
$totalProtein        = isset($_POST['totalProtein']) ? $_POST['totalProtein'] : 0;
$totalKarbohidrat    = isset($_POST['totalKarbohidrat']) ? $_POST['totalKarbohidrat'] : 0;
$totalLemak          = isset($_POST['totalLemak']) ? $_POST['totalLemak'] : 0;
$totalSerat          = isset($_POST['totalSerat']) ? $_POST['totalSerat'] : 0;
$catatanHarian       = isset($_POST['catatanHarian']) ? $_POST['catatanHarian'] : null;

// Validasi input
if (empty($tanggalCatat)) {
    echo json_encode([
        "status" => "error",
        "message" => "Tanggal catat wajib diisi"
    ]);
    exit;
}

// Harus memiliki salah satu dari userID atau anakID
if (empty($userID) && empty($anakID)) {
    echo json_encode([
        "status" => "error",
        "message" => "Harus menyertakan userID atau anakID"
    ]);
    exit;
}

// Bangun query dan parameter dinamis berdasarkan nilai yang tidak null
$fields = [];
$values = [];
$paramTypes = '';
$params = [];

if ($userID !== null) {
    $fields[] = 'userID';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $userID;
} else {
    $fields[] = 'userID';
    $values[] = 'NULL';
}

if ($anakID !== null) {
    $fields[] = 'anakID';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $anakID;
} else {
    $fields[] = 'anakID';
    $values[] = 'NULL';
}

$fields[] = 'tanggalCatat';
$values[] = '?';
$paramTypes .= 's';
$params[] = $tanggalCatat;

$fields[] = 'totalKalori';
$values[] = '?';
$paramTypes .= 'd';
$params[] = floatval($totalKalori);

$fields[] = 'totalProtein';
$values[] = '?';
$paramTypes .= 'd';
$params[] = floatval($totalProtein);

$fields[] = 'totalKarbohidrat';
$values[] = '?';
$paramTypes .= 'd';
$params[] = floatval($totalKarbohidrat);

$fields[] = 'totalLemak';
$values[] = '?';
$paramTypes .= 'd';
$params[] = floatval($totalLemak);

$fields[] = 'totalSerat';
$values[] = '?';
$paramTypes .= 'd';
$params[] = floatval($totalSerat);

if ($catatanHarian !== null) {
    $fields[] = 'catatanHarian';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $catatanHarian;
} else {
    $fields[] = 'catatanHarian';
    $values[] = 'NULL';
}

// Gabungkan field dan value untuk query
$fieldsStr = implode(', ', $fields);
$valuesStr = implode(', ', $values);

$sql = "INSERT INTO datanutrisi ({$fieldsStr}) VALUES ({$valuesStr})";
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
        "message" => "Data Nutrisi berhasil ditambahkan",
        "data" => [
            "nutrisiID" => $last_id,
            "tanggalCatat" => $tanggalCatat,
            "userID" => $userID,
            "anakID" => $anakID
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