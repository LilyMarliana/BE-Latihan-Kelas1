<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$alertID             = $_POST['alertID'];
$anakID              = isset($_POST['anakID']) ? $_POST['anakID'] : null;
$remaja              = isset($_POST['remaja']) ? $_POST['remaja'] : null;
$ibuHamilID          = isset($_POST['ibuHamilID']) ? $_POST['ibuHamilID'] : null;
$jenisRisiko         = isset($_POST['jenisRisiko']) ? $_POST['jenisRisiko'] : null;
$tingkatRisiko       = isset($_POST['tingkatRisiko']) ? $_POST['tingkatRisiko'] : null;
$deskripsiRisiko     = isset($_POST['deskripsiRisiko']) ? $_POST['deskripsiRisiko'] : null;
$parameterPemicu     = isset($_POST['parameterPemicu']) ? $_POST['parameterPemicu'] : null;
$statusTindakLanjut  = isset($_POST['statusTindakLanjut']) ? $_POST['statusTindakLanjut'] : null;
$tanggalTindakLanjut = isset($_POST['tanggalTindakLanjut']) ? $_POST['tanggalTindakLanjut'] : null;
$catatanTindakLanjut = isset($_POST['catatanTindakLanjut']) ? $_POST['catatanTindakLanjut'] : null;
$tenagaKesehatanID   = isset($_POST['tenagaKesehatanID']) ? $_POST['tenagaKesehatanID'] : null;
$prioritas           = isset($_POST['prioritas']) ? $_POST['prioritas'] : null;

// Validasi jenisRisiko jika diisi
if ($jenisRisiko !== null) {
    $validJenisRisiko = ['risiko_stunting', 'risiko_gizi_buruk', 'risiko_obesitas', 'growth_faltering', 'risiko_kehamilan', 'nutrisi_tidak_adekuat'];
    if (!in_array($jenisRisiko, $validJenisRisiko)) {
        echo json_encode([
            "status" => "error",
            "message" => "Jenis risiko tidak valid"
        ]);
        exit;
    }
}

// Validasi tingkatRisiko jika diisi
if ($tingkatRisiko !== null) {
    $validTingkatRisiko = ['rendah', 'sedang', 'tinggi', 'kritis'];
    if (!in_array($tingkatRisiko, $validTingkatRisiko)) {
        echo json_encode([
            "status" => "error",
            "message" => "Tingkat risiko tidak valid"
        ]);
        exit;
    }
}

// Validasi statusTindakLanjut jika diisi
if ($statusTindakLanjut !== null) {
    $validStatus = ['menunggu', 'ditinjau', 'intervensi', 'selesai'];
    if (!in_array($statusTindakLanjut, $validStatus)) {
        echo json_encode([
            "status" => "error",
            "message" => "Status tindak lanjut tidak valid"
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

if ($remaja !== null) {
    $fields[] = "remaja = ?";
    $params[] = $remaja;
    $paramTypes .= 'i';
}

if ($ibuHamilID !== null) {
    $fields[] = "ibuHamilID = ?";
    $params[] = $ibuHamilID;
    $paramTypes .= 'i';
}

if ($jenisRisiko !== null) {
    $fields[] = "jenisRisiko = ?";
    $params[] = $jenisRisiko;
    $paramTypes .= 's';
}

if ($tingkatRisiko !== null) {
    $fields[] = "tingkatRisiko = ?";
    $params[] = $tingkatRisiko;
    $paramTypes .= 's';
}

if ($deskripsiRisiko !== null) {
    $fields[] = "deskripsiRisiko = ?";
    $params[] = $deskripsiRisiko;
    $paramTypes .= 's';
}

if ($parameterPemicu !== null) {
    $fields[] = "parameterPemicu = ?";
    $params[] = $parameterPemicu;
    $paramTypes .= 's';
}

if ($statusTindakLanjut !== null) {
    $fields[] = "statusTindakLanjut = ?";
    $params[] = $statusTindakLanjut;
    $paramTypes .= 's';
}

if ($tanggalTindakLanjut !== null) {
    $fields[] = "tanggalTindakLanjut = ?";
    $params[] = $tanggalTindakLanjut;
    $paramTypes .= 's';
}

if ($catatanTindakLanjut !== null) {
    $fields[] = "catatanTindakLanjut = ?";
    $params[] = $catatanTindakLanjut;
    $paramTypes .= 's';
}

if ($tenagaKesehatanID !== null) {
    $fields[] = "tenagaKesehatanID = ?";
    $params[] = $tenagaKesehatanID;
    $paramTypes .= 'i';
}

if ($prioritas !== null) {
    $fields[] = "prioritas = ?";
    $params[] = $prioritas;
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
$sql = "UPDATE alertrisiko SET {$fieldsStr} WHERE alertID = ?";
$params[] = $alertID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Alert Risiko berhasil diperbarui",
            "data" => [
                "alertID" => $alertID,
                "jenisRisiko" => $jenisRisiko,
                "tingkatRisiko" => $tingkatRisiko,
                "deskripsiRisiko" => $deskripsiRisiko
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Alert Risiko tidak ditemukan"
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