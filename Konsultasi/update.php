<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$konsultasiID        = isset($_POST['konsultasiID']) ? $_POST['konsultasiID'] : null;
$userID              = isset($_POST['userID']) ? $_POST['userID'] : null;
$tenagaKesehatanID   = isset($_POST['tenagaKesehatanID']) ? $_POST['tenagaKesehatanID'] : null;
$tipeKonsultasi      = isset($_POST['tipeKonsultasi']) ? $_POST['tipeKonsultasi'] : null;
$statusKonsultasi    = isset($_POST['statusKonsultasi']) ? $_POST['statusKonsultasi'] : null;
$tanggalJadwal       = isset($_POST['tanggalJadwal']) ? $_POST['tanggalJadwal'] : null;
$tanggalMulai        = isset($_POST['tanggalMulai']) ? $_POST['tanggalMulai'] : null;
$tanggalSelesai      = isset($_POST['tanggalSelesai']) ? $_POST['tanggalSelesai'] : null;
$durasi              = isset($_POST['durasi']) ? $_POST['durasi'] : null;
$topikKonsultasi     = isset($_POST['topikKonsultasi']) ? $_POST['topikKonsultasi'] : null;
$keluhanUtama        = isset($_POST['keluhanUtama']) ? $_POST['keluhanUtama'] : null;
$catatanDokter       = isset($_POST['catatanDokter']) ? $_POST['catatanDokter'] : null;
$diagnosaSementara   = isset($_POST['diagnosaSementara']) ? $_POST['diagnosaSementara'] : null;
$rekomendasiTindakan = isset($_POST['rekomendasiTindakan']) ? $_POST['rekomendasiTindakan'] : null;
$urlRekaman          = isset($_POST['urlRekaman']) ? $_POST['urlRekaman'] : null;
$rating              = isset($_POST['rating']) ? $_POST['rating'] : null;
$feedback            = isset($_POST['feedback']) ? $_POST['feedback'] : null;

// Validasi input
if (empty($konsultasiID)) {
    echo json_encode([
        "status" => "error",
        "message" => "KonsultasiID wajib diisi"
    ]);
    exit;
}

// Validasi tipeKonsultasi jika diisi
if ($tipeKonsultasi !== null) {
    $validTipeKonsultasi = ['chat', 'video_call', 'phone_call'];
    if (!in_array($tipeKonsultasi, $validTipeKonsultasi)) {
        echo json_encode([
            "status" => "error",
            "message" => "Tipe konsultasi tidak valid"
        ]);
        exit;
    }
}

// Validasi statusKonsultasi jika diisi
if ($statusKonsultasi !== null) {
    $validStatus = ['dijadwalkan', 'berlangsung', 'selesai', 'dibatalkan'];
    if (!in_array($statusKonsultasi, $validStatus)) {
        echo json_encode([
            "status" => "error",
            "message" => "Status konsultasi tidak valid"
        ]);
        exit;
    }
}

// Validasi rating jika diisi
if ($rating !== null && ($rating < 1 || $rating > 5)) {
    echo json_encode([
        "status" => "error",
        "message" => "Rating harus antara 1-5"
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

if ($tenagaKesehatanID !== null) {
    $fields[] = "tenagaKesehatanID = ?";
    $params[] = $tenagaKesehatanID;
    $paramTypes .= 'i';
}

if ($tipeKonsultasi !== null) {
    $fields[] = "tipeKonsultasi = ?";
    $params[] = $tipeKonsultasi;
    $paramTypes .= 's';
}

if ($statusKonsultasi !== null) {
    $fields[] = "statusKonsultasi = ?";
    $params[] = $statusKonsultasi;
    $paramTypes .= 's';
}

if ($tanggalJadwal !== null) {
    $fields[] = "tanggalJadwal = ?";
    $params[] = $tanggalJadwal;
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

if ($durasi !== null) {
    $fields[] = "durasi = ?";
    $params[] = $durasi;
    $paramTypes .= 'i';
}

if ($topikKonsultasi !== null) {
    $fields[] = "topikKonsultasi = ?";
    $params[] = $topikKonsultasi;
    $paramTypes .= 's';
}

if ($keluhanUtama !== null) {
    $fields[] = "keluhanUtama = ?";
    $params[] = $keluhanUtama;
    $paramTypes .= 's';
}

if ($catatanDokter !== null) {
    $fields[] = "catatanDokter = ?";
    $params[] = $catatanDokter;
    $paramTypes .= 's';
}

if ($diagnosaSementara !== null) {
    $fields[] = "diagnosaSementara = ?";
    $params[] = $diagnosaSementara;
    $paramTypes .= 's';
}

if ($rekomendasiTindakan !== null) {
    $fields[] = "rekomendasiTindakan = ?";
    $params[] = $rekomendasiTindakan;
    $paramTypes .= 's';
}

if ($urlRekaman !== null) {
    $fields[] = "urlRekaman = ?";
    $params[] = $urlRekaman;
    $paramTypes .= 's';
}

if ($rating !== null) {
    $fields[] = "rating = ?";
    $params[] = $rating;
    $paramTypes .= 'i';
}

if ($feedback !== null) {
    $fields[] = "feedback = ?";
    $params[] = $feedback;
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
$sql = "UPDATE konsultasi SET {$fieldsStr} WHERE konsultasiID = ?";
$params[] = $konsultasiID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Konsultasi berhasil diperbarui",
            "data" => [
                "konsultasiID" => $konsultasiID,
                "tipeKonsultasi" => $tipeKonsultasi
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Konsultasi tidak ditemukan"
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