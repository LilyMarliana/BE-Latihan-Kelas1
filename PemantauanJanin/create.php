<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST dengan pengecekan
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

// Validasi input wajib
if (empty($ibuHamilID) || empty($tanggalPemeriksaan)) {
    echo json_encode([
        "status" => "error",
        "message" => "ibuHamilID dan tanggalPemeriksaan wajib diisi"
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

// Bangun query dan parameter dinamis berdasarkan nilai yang tidak null
$fields = [];
$values = [];
$paramTypes = '';
$params = [];

$fields[] = 'ibuHamilID';
$values[] = '?';
$paramTypes .= 'i';
$params[] = $ibuHamilID;

$fields[] = 'tanggalPemeriksaan';
$values[] = '?';
$paramTypes .= 's';
$params[] = $tanggalPemeriksaan;

if ($usiaKehamilanSaatIni !== null) {
    $fields[] = 'usiaKehamilanSaatIni';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $usiaKehamilanSaatIni;
}

if ($beratBadanIbu !== null) {
    $fields[] = 'beratBadanIbu';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($beratBadanIbu);
}

if ($tekananDarahSistolik !== null) {
    $fields[] = 'tekananDarahSistolik';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $tekananDarahSistolik;
}

if ($tekananDarahDiastolik !== null) {
    $fields[] = 'tekananDarahDiastolik';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $tekananDarahDiastolik;
}

if ($tinggiPundusFundus !== null) {
    $fields[] = 'tinggiPundusFundus';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($tinggiPundusFundus);
}

if ($denyutJantungJanin !== null) {
    $fields[] = 'denyutJantungJanin';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $denyutJantungJanin;
}

if ($gerakanJanin !== null) {
    $fields[] = 'gerakanJanin';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $gerakanJanin;
}

if ($posisiJanin !== null) {
    $fields[] = 'posisiJanin';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $posisiJanin;
}

if ($estimasiBeratJanin !== null) {
    $fields[] = 'estimasiBeratJanin';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($estimasiBeratJanin);
}

if ($catatanDokter !== null) {
    $fields[] = 'catatanDokter';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $catatanDokter;
}

if ($petugasID !== null) {
    $fields[] = 'petugasID';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $petugasID;
}

// Gabungkan field dan value untuk query
$fieldsStr = implode(', ', $fields);
$valuesStr = implode(', ', $values);

$sql = "INSERT INTO pemantauanjanin ({$fieldsStr}) VALUES ({$valuesStr})";
$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data pemantauan janin berhasil ditambahkan",
        "data" => [
            "pemantauanID" => $last_id,
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
        "status"  => "error",
        "message" => $stmt->error
    ]);
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>