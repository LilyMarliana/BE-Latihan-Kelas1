<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST) dengan pengecekan
$interaksiID        = isset($_POST['interaksiID']) ? $_POST['interaksiID'] : null;
$kontenID           = isset($_POST['kontenID']) ? $_POST['kontenID'] : null;
$userID             = isset($_POST['userID']) ? $_POST['userID'] : null;
$tipeInteraksi      = isset($_POST['tipeInteraksi']) ? $_POST['tipeInteraksi'] : null;
$durasiView         = isset($_POST['durasiView']) ? $_POST['durasiView'] : null;

// Validasi interaksiID wajib diisi
if (empty($interaksiID)) {
    echo json_encode([
        "status" => "error",
        "message" => "interaksiID wajib diisi"
    ]);
    exit;
}

// Validasi tipeInteraksi jika diisi
if ($tipeInteraksi !== null) {
    $validTipe = ['view', 'like', 'share', 'bookmark', 'complete'];
    if (!in_array($tipeInteraksi, $validTipe)) {
        echo json_encode([
            "status" => "error",
            "message" => "Tipe interaksi tidak valid. Pilihan yang valid: " . implode(', ', $validTipe)
        ]);
        exit;
    }
}

// Bangun query dinamis berdasarkan field yang diisi
$fields = [];
$params = [];
$paramTypes = '';

if ($kontenID !== null) {
    $fields[] = "kontenID = ?";
    $params[] = $kontenID;
    $paramTypes .= 'i';
}

if ($userID !== null) {
    $fields[] = "userID = ?";
    $params[] = $userID;
    $paramTypes .= 'i';
}

if ($tipeInteraksi !== null) {
    $fields[] = "tipeInteraksi = ?";
    $params[] = $tipeInteraksi;
    $paramTypes .= 's';
}

if ($durasiView !== null) {
    $fields[] = "durasiView = ?";
    $params[] = $durasiView;
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
$sql = "UPDATE interaksikonten SET {$fieldsStr} WHERE interaksiID = ?";
$params[] = $interaksiID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Data interaksi konten berhasil diperbarui",
            "data" => [
                "interaksiID" => $interaksiID,
                "kontenID" => $kontenID,
                "userID" => $userID,
                "tipeInteraksi" => $tipeInteraksi,
                "durasiView" => $durasiView
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Interaksi konten tidak ditemukan"
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