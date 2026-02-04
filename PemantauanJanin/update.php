<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST) dengan pengecekan
$pemantauanID           = isset($_POST['pemantauanID']) ? $_POST['pemantauanID'] : null;
$ibuHamilID             = isset($_POST['ibuHamilID']) ? $_POST['ibuHamilID'] : null;
$tanggalPemeriksaan     = isset($_POST['tanggalPemeriksaan']) ? $_POST['tanggalPemeriksaan'] : null;
$usiaKehamilanSaatIni   = isset($_POST['usiaKehamilanSaatIni']) ? $_POST['usiaKehamilanSaatIni'] : null;
$beratBadanIbu          = isset($_POST['beratBadanIbu']) ? $_POST['beratBadanIbu'] : null;
$tekananDarahSistolik   = isset($_POST['tekananDarahSistolik']) ? $_POST['tekananDarahSistolik'] : null;
$tekananDarahDiastolik  = isset($_POST['tekananDarahDiastolik']) ? $_POST['tekananDarahDiastolik'] : null;
$tinggiPundusFundus     = isset($_POST['tinggiPundusFundus']) ? $_POST['tinggiPundusFundus'] : null;
$denyutJantungJanin     = isset($_POST['denyutJantungJanin']) ? $_POST['denyutJantungJanin'] : null;
$gerakanJanin           = isset($_POST['gerakanJanin']) ? $_POST['gerakanJanin'] : null;
$posisiJanin            = isset($_POST['posisiJanin']) ? $_POST['posisiJanin'] : null;
$estimasiBeratJanin     = isset($_POST['estimasiBeratJanin']) ? $_POST['estimasiBeratJanin'] : null;
$catatanDokter          = isset($_POST['catatanDokter']) ? $_POST['catatanDokter'] : null;
$petugasID              = isset($_POST['petugasID']) ? $_POST['petugasID'] : null;

// Validasi pemantauanID wajib diisi
if (empty($pemantauanID)) {
    echo json_encode([
        "status" => "error",
        "message" => "pemantauanID wajib diisi"
    ]);
    exit;
}

// Validasi gerakanJanin jika diisi
if ($gerakanJanin !== null) {
    $validGerakan = ['aktif', 'normal', 'kurang'];
    if (!in_array($gerakanJanin, $validGerakan)) {
        echo json_encode([
            "status" => "error",
            "message" => "Gerakan janin tidak valid. Pilihan: aktif, normal, kurang"
        ]);
        exit;
    }
}

// Bangun query dinamis berdasarkan field yang diisi
$fields = [];
$params = [];
$paramTypes = '';

if ($ibuHamilID !== null) {
    $fields[] = "ibuHamilID = ?";
    $params[] = $ibuHamilID;
    $paramTypes .= 'i';
}

if ($tanggalPemeriksaan !== null) {
    $fields[] = "tanggalPemeriksaan = ?";
    $params[] = $tanggalPemeriksaan;
    $paramTypes .= 's';
}

if ($usiaKehamilanSaatIni !== null) {
    $fields[] = "usiaKehamilanSaatIni = ?";
    $params[] = $usiaKehamilanSaatIni;
    $paramTypes .= 'i';
}

if ($beratBadanIbu !== null) {
    $fields[] = "beratBadanIbu = ?";
    $params[] = floatval($beratBadanIbu);
    $paramTypes .= 'd';
}

if ($tekananDarahSistolik !== null) {
    $fields[] = "tekananDarahSistolik = ?";
    $params[] = $tekananDarahSistolik;
    $paramTypes .= 'i';
}

if ($tekananDarahDiastolik !== null) {
    $fields[] = "tekananDarahDiastolik = ?";
    $params[] = $tekananDarahDiastolik;
    $paramTypes .= 'i';
}

if ($tinggiPundusFundus !== null) {
    $fields[] = "tinggiPundusFundus = ?";
    $params[] = floatval($tinggiPundusFundus);
    $paramTypes .= 'd';
}

if ($denyutJantungJanin !== null) {
    $fields[] = "denyutJantungJanin = ?";
    $params[] = $denyutJantungJanin;
    $paramTypes .= 'i';
}

if ($gerakanJanin !== null) {
    $fields[] = "gerakanJanin = ?";
    $params[] = $gerakanJanin;
    $paramTypes .= 's';
}

if ($posisiJanin !== null) {
    $fields[] = "posisiJanin = ?";
    $params[] = $posisiJanin;
    $paramTypes .= 's';
}

if ($estimasiBeratJanin !== null) {
    $fields[] = "estimasiBeratJanin = ?";
    $params[] = floatval($estimasiBeratJanin);
    $paramTypes .= 'd';
}

if ($catatanDokter !== null) {
    $fields[] = "catatanDokter = ?";
    $params[] = $catatanDokter;
    $paramTypes .= 's';
}

if ($petugasID !== null) {
    $fields[] = "petugasID = ?";
    $params[] = $petugasID;
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
$sql = "UPDATE pemantauanjanin SET {$fieldsStr} WHERE pemantauanID = ?";
$params[] = $pemantauanID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Data pemantauan janin berhasil diperbarui",
            "data" => [
                "pemantauanID" => $pemantauanID,
                "ibuHamilID" => $ibuHamilID,
                "tanggalPemeriksaan" => $tanggalPemeriksaan,
                "usiaKehamilanSaatIni" => $usiaKehamilanSaatIni,
                "beratBadanIbu" => $beratBadanIbu,
                "tekananDarahSistolik" => $tekananDarahSistolik,
                "tekananDarahDiastolik" => $tekananDarahDiastolik,
                "tinggiPundusFundus" => $tinggiPundusFundus,
                "denyutJantungJanin" => $denyutJantungJanin,
                "gerakanJanin" => $gerakanJanin,
                "posisiJanin" => $posisiJanin,
                "estimasiBeratJanin" => $estimasiBeratJanin,
                "catatanDokter" => $catatanDokter,
                "petugasID" => $petugasID
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Data pemantauan janin tidak ditemukan"
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