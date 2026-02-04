<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST) dengan pengecekan
$tantanganID          = isset($_POST['tantanganID']) ? $_POST['tantanganID'] : null;
$namaTantangan        = isset($_POST['namaTantangan']) ? $_POST['namaTantangan'] : null;
$deskripsi            = isset($_POST['deskripsi']) ? $_POST['deskripsi'] : null;
$tipeTarget           = isset($_POST['tipeTarget']) ? $_POST['tipeTarget'] : null;
$targetValue          = isset($_POST['targetValue']) ? $_POST['targetValue'] : null;
$satuanTarget         = isset($_POST['satuanTarget']) ? $_POST['satuanTarget'] : null;
$periodeAktif         = isset($_POST['periodeAktif']) ? $_POST['periodeAktif'] : null;
$tanggalMulai         = isset($_POST['tanggalMulai']) ? $_POST['tanggalMulai'] : null;
$tanggalSelesai       = isset($_POST['tanggalSelesai']) ? $_POST['tanggalSelesai'] : null;
$poinReward           = isset($_POST['poinReward']) ? $_POST['poinReward'] : null;
$badgeRewardID        = isset($_POST['badgeRewardID']) ? $_POST['badgeRewardID'] : null;
$targetPeserta        = isset($_POST['targetPeserta']) ? $_POST['targetPeserta'] : null;

// Validasi tantanganID wajib diisi
if (empty($tantanganID)) {
    echo json_encode([
        "status" => "error",
        "message" => "tantanganID wajib diisi"
    ]);
    exit;
}

// Validasi tipeTarget jika diisi
if ($tipeTarget !== null) {
    $validTipeTarget = ['nutrisi', 'aktivitas', 'pemantauan', 'edukasi', 'sosial'];
    if (!in_array($tipeTarget, $validTipeTarget)) {
        echo json_encode([
            "status" => "error",
            "message" => "Tipe target tidak valid. Pilihan yang valid: " . implode(', ', $validTipeTarget)
        ]);
        exit;
    }
}

// Validasi periodeAktif jika diisi
if ($periodeAktif !== null) {
    $validPeriode = ['harian', 'mingguan', 'bulanan', 'custom'];
    if (!in_array($periodeAktif, $validPeriode)) {
        echo json_encode([
            "status" => "error",
            "message" => "Periode aktif tidak valid. Pilihan yang valid: " . implode(', ', $validPeriode)
        ]);
        exit;
    }
}

// Validasi targetPeserta jika diisi
if ($targetPeserta !== null) {
    $validTargetPeserta = ['remaja', 'orangtua', 'semua'];
    if (!in_array($targetPeserta, $validTargetPeserta)) {
        echo json_encode([
            "status" => "error",
            "message" => "Target peserta tidak valid. Pilihan yang valid: " . implode(', ', $validTargetPeserta)
        ]);
        exit;
    }
}

// Bangun query dinamis berdasarkan field yang diisi
$fields = [];
$params = [];
$paramTypes = '';

if ($namaTantangan !== null) {
    $fields[] = "namaTantangan = ?";
    $params[] = $namaTantangan;
    $paramTypes .= 's';
}

if ($deskripsi !== null) {
    $fields[] = "deskripsi = ?";
    $params[] = $deskripsi;
    $paramTypes .= 's';
}

if ($tipeTarget !== null) {
    $fields[] = "tipeTarget = ?";
    $params[] = $tipeTarget;
    $paramTypes .= 's';
}

if ($targetValue !== null) {
    $fields[] = "targetValue = ?";
    $params[] = floatval($targetValue);
    $paramTypes .= 'd';
}

if ($satuanTarget !== null) {
    $fields[] = "satuanTarget = ?";
    $params[] = $satuanTarget;
    $paramTypes .= 's';
}

if ($periodeAktif !== null) {
    $fields[] = "periodeAktif = ?";
    $params[] = $periodeAktif;
    $paramTypes .= 's';
}

if ($tanggalMulai !== null) {
    $fields[] = "tanggalMulai = ?";
    $params[] = $tanggalMulai;
    $paramTypes .= 's';
}

if ($tanggalSelesai !== null) {
    $fields[] = "tanggalSelesai = ?";
    $params[] = $tanggalSelesai;
    $paramTypes .= 's';
}

if ($poinReward !== null) {
    $fields[] = "poinReward = ?";
    $params[] = $poinReward;
    $paramTypes .= 'i';
}

if ($badgeRewardID !== null) {
    $fields[] = "badgeRewardID = ?";
    $params[] = $badgeRewardID;
    $paramTypes .= 'i';
}

if ($targetPeserta !== null) {
    $fields[] = "targetPeserta = ?";
    $params[] = $targetPeserta;
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
$sql = "UPDATE tantangan SET {$fieldsStr} WHERE tantanganID = ?";
$params[] = $tantanganID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Data tantangan berhasil diperbarui",
            "data" => [
                "tantanganID" => $tantanganID,
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
            "status" => "error",
            "message" => "Tantangan tidak ditemukan"
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