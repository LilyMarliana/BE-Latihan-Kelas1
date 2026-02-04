<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$userID              = $_POST['userID'];
$tenagaKesehatanID   = $_POST['tenagaKesehatanID'];
$tipeKonsultasi      = $_POST['tipeKonsultasi'];
$statusKonsultasi    = isset($_POST['statusKonsultasi']) ? $_POST['statusKonsultasi'] : 'dijadwalkan';
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
if (empty($userID) || empty($tenagaKesehatanID) || empty($tipeKonsultasi)) {
    echo json_encode([
        "status" => "error",
        "message" => "UserID, tenagaKesehatanID, dan tipeKonsultasi wajib diisi"
    ]);
    exit;
}

// Validasi tipeKonsultasi
$validTipeKonsultasi = ['chat', 'video_call', 'phone_call'];
if (!in_array($tipeKonsultasi, $validTipeKonsultasi)) {
    echo json_encode([
        "status" => "error",
        "message" => "Tipe konsultasi tidak valid"
    ]);
    exit;
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

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO konsultasi
    (userID, tenagaKesehatanID, tipeKonsultasi, statusKonsultasi, tanggalJadwal, tanggalMulai, tanggalSelesai, durasi, topikKonsultasi, keluhanUtama, catatanDokter, diagnosaSementara, rekomendasiTindakan, urlRekaman, rating, feedback)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// i = integer, s = string
$stmt->bind_param(
    "iisssssisssssssi",
    $userID,
    $tenagaKesehatanID,
    $tipeKonsultasi,
    $statusKonsultasi,
    $tanggalJadwal,
    $tanggalMulai,
    $tanggalSelesai,
    $durasi,
    $topikKonsultasi,
    $keluhanUtama,
    $catatanDokter,
    $diagnosaSementara,
    $rekomendasiTindakan,
    $urlRekaman,
    $rating,
    $feedback
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Konsultasi berhasil ditambahkan",
        "data" => [
            "konsultasiID" => $last_id,
            "userID" => $userID,
            "tenagaKesehatanID" => $tenagaKesehatanID,
            "tipeKonsultasi" => $tipeKonsultasi
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