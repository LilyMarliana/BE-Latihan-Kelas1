<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST dengan pengecekan
$namaTantangan        = isset($_POST['namaTantangan']) ? $_POST['namaTantangan'] : null;
$deskripsi            = isset($_POST['deskripsi']) ? $_POST['deskripsi'] : null;
$tipeTarget           = isset($_POST['tipeTarget']) ? $_POST['tipeTarget'] : null;
$targetValue          = isset($_POST['targetValue']) ? $_POST['targetValue'] : null;
$satuanTarget         = isset($_POST['satuanTarget']) ? $_POST['satuanTarget'] : null;
$periodeAktif         = isset($_POST['periodeAktif']) ? $_POST['periodeAktif'] : null;
$tanggalMulai         = isset($_POST['tanggalMulai']) ? $_POST['tanggalMulai'] : null;
$tanggalSelesai       = isset($_POST['tanggalSelesai']) ? $_POST['tanggalSelesai'] : null;
$poinReward           = isset($_POST['poinReward']) ? $_POST['poinReward'] : 10;
$badgeRewardID        = isset($_POST['badgeRewardID']) ? $_POST['badgeRewardID'] : null;
$targetPeserta        = isset($_POST['targetPeserta']) ? $_POST['targetPeserta'] : 'remaja';

// Validasi input wajib
if (empty($namaTantangan) || empty($deskripsi) || empty($tipeTarget)) {
    echo json_encode([
        "status" => "error",
        "message" => "namaTantangan, deskripsi, dan tipeTarget wajib diisi"
    ]);
    exit;
}

// Validasi tipeTarget
$validTipeTarget = ['nutrisi', 'aktivitas', 'pemantauan', 'edukasi', 'sosial'];
if (!in_array($tipeTarget, $validTipeTarget)) {
    echo json_encode([
        "status" => "error",
        "message" => "Tipe target tidak valid. Pilihan yang valid: " . implode(', ', $validTipeTarget)
    ]);
    exit;
}

// Validasi periodeAktif
$validPeriode = ['harian', 'mingguan', 'bulanan', 'custom'];
if (!in_array($periodeAktif, $validPeriode)) {
    echo json_encode([
        "status" => "error",
        "message" => "Periode aktif tidak valid. Pilihan yang valid: " . implode(', ', $validPeriode)
    ]);
    exit;
}

// Validasi targetPeserta
$validTargetPeserta = ['remaja', 'orangtua', 'semua'];
if (!in_array($targetPeserta, $validTargetPeserta)) {
    echo json_encode([
        "status" => "error",
        "message" => "Target peserta tidak valid. Pilihan yang valid: " . implode(', ', $validTargetPeserta)
    ]);
    exit;
}

// Validasi badgeRewardID jika disertakan
if ($badgeRewardID !== null) {
    $checkBadgeSql = "SELECT badgeID FROM badge WHERE badgeID = ?";
    $checkBadgeStmt = $conn->prepare($checkBadgeSql);
    $checkBadgeStmt->bind_param("i", $badgeRewardID);
    $checkBadgeStmt->execute();
    $checkBadgeResult = $checkBadgeStmt->get_result();

    if ($checkBadgeResult->num_rows === 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Badge dengan ID {$badgeRewardID} tidak ditemukan"
        ]);
        exit;
    }
    $checkBadgeStmt->close();
}

// Bangun query dan parameter dinamis berdasarkan nilai yang tidak null
$fields = [];
$values = [];
$paramTypes = '';
$params = [];

$fields[] = 'namaTantangan';
$values[] = '?';
$paramTypes .= 's';
$params[] = $namaTantangan;

$fields[] = 'deskripsi';
$values[] = '?';
$paramTypes .= 's';
$params[] = $deskripsi;

$fields[] = 'tipeTarget';
$values[] = '?';
$paramTypes .= 's';
$params[] = $tipeTarget;

$fields[] = 'periodeAktif';
$values[] = '?';
$paramTypes .= 's';
$params[] = $periodeAktif;

$fields[] = 'tanggalMulai';
$values[] = '?';
$paramTypes .= 's';
$params[] = $tanggalMulai;

$fields[] = 'tanggalSelesai';
$values[] = '?';
$paramTypes .= 's';
$params[] = $tanggalSelesai;

$fields[] = 'poinReward';
$values[] = '?';
$paramTypes .= 'i';
$params[] = $poinReward;

$fields[] = 'targetPeserta';
$values[] = '?';
$paramTypes .= 's';
$params[] = $targetPeserta;

if ($targetValue !== null) {
    $fields[] = 'targetValue';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($targetValue);
}

if ($satuanTarget !== null) {
    $fields[] = 'satuanTarget';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $satuanTarget;
}

if ($badgeRewardID !== null) {
    $fields[] = 'badgeRewardID';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $badgeRewardID;
}

// Gabungkan field dan value untuk query
$fieldsStr = implode(', ', $fields);
$valuesStr = implode(', ', $values);

$sql = "INSERT INTO tantangan ({$fieldsStr}) VALUES ({$valuesStr})";
$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data tantangan berhasil ditambahkan",
        "data" => [
            "tantanganID" => $last_id,
            "namaTantangan" => $namaTantangan,
            "deskripsi" => $deskripsi,
            "tipeTarget" => $tipeTarget,
            "targetValue" => $targetValue,
            "satuanTarget" => $satuanTarget,
            "periodeAktif" => $periodeAktif,
            "tanggalMulai" => $tanggalMulai,
            "tanggalSelesai" => $tanggalSelesai,
            "poinReward" => $poinReward,
            "badgeRewardID" => $badgeRewardID,
            "targetPeserta" => $targetPeserta
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