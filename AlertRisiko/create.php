<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$jenisRisiko           = $_POST['jenisRisiko'];
$tingkatRisiko         = $_POST['tingkatRisiko'];
$deskripsiRisiko       = $_POST['deskripsiRisiko'];
$anakID                = isset($_POST['anakID']) ? $_POST['anakID'] : null;
$remaja                = isset($_POST['remaja']) ? $_POST['remaja'] : null;
$ibuHamilID            = isset($_POST['ibuHamilID']) ? $_POST['ibuHamilID'] : null;
$parameterPemicu       = isset($_POST['parameterPemicu']) ? $_POST['parameterPemicu'] : null;
$statusTindakLanjut    = isset($_POST['statusTindakLanjut']) ? $_POST['statusTindakLanjut'] : 'menunggu';
$tanggalTindakLanjut   = isset($_POST['tanggalTindakLanjut']) ? $_POST['tanggalTindakLanjut'] : null;
$catatanTindakLanjut   = isset($_POST['catatanTindakLanjut']) ? $_POST['catatanTindakLanjut'] : null;
$tenagaKesehatanID     = isset($_POST['tenagaKesehatanID']) ? $_POST['tenagaKesehatanID'] : null;
$prioritas             = isset($_POST['prioritas']) ? $_POST['prioritas'] : 1;

// Validasi input
if (empty($jenisRisiko) || empty($tingkatRisiko) || empty($deskripsiRisiko)) {
    echo json_encode([
        "status" => "error",
        "message" => "Jenis risiko, tingkat risiko, dan deskripsi risiko wajib diisi"
    ]);
    exit;
}

// Harus memiliki salah satu dari anakID, remaja, atau ibuHamilID
if (empty($anakID) && empty($remaja) && empty($ibuHamilID)) {
    echo json_encode([
        "status" => "error",
        "message" => "Harus menyertakan anakID, remaja, atau ibuHamilID"
    ]);
    exit;
}

// Validasi jenisRisiko
$validJenisRisiko = ['risiko_stunting', 'risiko_gizi_buruk', 'risiko_obesitas', 'growth_faltering', 'risiko_kehamilan', 'nutrisi_tidak_adekuat'];
if (!in_array($jenisRisiko, $validJenisRisiko)) {
    echo json_encode([
        "status" => "error",
        "message" => "Jenis risiko tidak valid"
    ]);
    exit;
}

// Validasi tingkatRisiko
$validTingkatRisiko = ['rendah', 'sedang', 'tinggi', 'kritis'];
if (!in_array($tingkatRisiko, $validTingkatRisiko)) {
    echo json_encode([
        "status" => "error",
        "message" => "Tingkat risiko tidak valid"
    ]);
    exit;
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

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO alertrisiko
    (anakID, remaja, ibuHamilID, jenisRisiko, tingkatRisiko, deskripsiRisiko, parameterPemicu, statusTindakLanjut, tanggalTindakLanjut, catatanTindakLanjut, tenagaKesehatanID, prioritas)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// i = integer, s = string
$stmt->bind_param(
    "iiissssssssi",
    $anakID,
    $remaja,
    $ibuHamilID,
    $jenisRisiko,
    $tingkatRisiko,
    $deskripsiRisiko,
    $parameterPemicu,
    $statusTindakLanjut,
    $tanggalTindakLanjut,
    $catatanTindakLanjut,
    $tenagaKesehatanID,
    $prioritas
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Alert Risiko berhasil ditambahkan",
        "data" => [
            "alertID" => $last_id,
            "jenisRisiko" => $jenisRisiko,
            "tingkatRisiko" => $tingkatRisiko,
            "deskripsiRisiko" => $deskripsiRisiko,
            "anakID" => $anakID,
            "remaja" => $remaja,
            "ibuHamilID" => $ibuHamilID
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