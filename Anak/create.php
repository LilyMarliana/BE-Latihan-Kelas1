<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$orangtuaID          = $_POST['orangtuaID'];
$namaLengkap         = $_POST['namaLengkap'];
$jenisKelamin        = $_POST['jenisKelamin'];
$tanggalLahir        = $_POST['tanggalLahir'];
$tempatLahir         = isset($_POST['tempatLahir']) ? $_POST['tempatLahir'] : null;
$beratBadanLahir     = isset($_POST['beratBadanLahir']) ? $_POST['beratBadanLahir'] : null;
$tinggiLahir         = isset($_POST['tinggiLahir']) ? $_POST['tinggiLahir'] : null;
$golonganDarah       = isset($_POST['golonganDarah']) ? $_POST['golonganDarah'] : null;
$riwayatKelahiran    = isset($_POST['riwayatKelahiran']) ? $_POST['riwayatKelahiran'] : null;
$fotoProfile         = isset($_POST['fotoProfile']) ? $_POST['fotoProfile'] : null;
$statusAktif         = isset($_POST['statusAktif']) ? $_POST['statusAktif'] : 1;

// Validasi input
if (empty($orangtuaID) || empty($namaLengkap) || empty($jenisKelamin) || empty($tanggalLahir)) {
    echo json_encode([
        "status" => "error",
        "message" => "OrangTuaID, namaLengkap, jenisKelamin, dan tanggalLahir wajib diisi"
    ]);
    exit;
}

// Validasi jenisKelamin
if (!in_array($jenisKelamin, ['L', 'P'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Jenis kelamin harus L atau P"
    ]);
    exit;
}

// Validasi statusAktif
if ($statusAktif !== null && !in_array($statusAktif, [0, 1])) {
    echo json_encode([
        "status" => "error",
        "message" => "Status aktif harus 0 atau 1"
    ]);
    exit;
}

// Validasi golongan darah jika diisi
if ($golonganDarah !== null) {
    $validGolDarah = ['A', 'B', 'AB', 'O'];
    if (!in_array($golonganDarah, $validGolDarah)) {
        echo json_encode([
            "status" => "error",
            "message" => "Golongan darah tidak valid"
        ]);
        exit;
    }
}

// Bangun query dan parameter dinamis berdasarkan nilai yang tidak null
$fields = [];
$values = [];
$paramTypes = '';
$params = [];

$fields[] = 'orangtuaID';
$values[] = '?';
$paramTypes .= 'i';
$params[] = $orangtuaID;

$fields[] = 'namaLengkap';
$values[] = '?';
$paramTypes .= 's';
$params[] = $namaLengkap;

$fields[] = 'jenisKelamin';
$values[] = '?';
$paramTypes .= 's';
$params[] = $jenisKelamin;

$fields[] = 'tanggalLahir';
$values[] = '?';
$paramTypes .= 's';
$params[] = $tanggalLahir;

if ($tempatLahir !== null) {
    $fields[] = 'tempatLahir';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $tempatLahir;
}

if ($beratBadanLahir !== null) {
    $fields[] = 'beratBadanLahir';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($beratBadanLahir);
}

if ($tinggiLahir !== null) {
    $fields[] = 'tinggiLahir';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($tinggiLahir);
}

if ($golonganDarah !== null) {
    $fields[] = 'golonganDarah';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $golonganDarah;
}

if ($riwayatKelahiran !== null) {
    $fields[] = 'riwayatKelahiran';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $riwayatKelahiran;
}

if ($fotoProfile !== null) {
    $fields[] = 'fotoProfile';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $fotoProfile;
}

$fields[] = 'statusAktif';
$values[] = '?';
$paramTypes .= 'i';
$params[] = $statusAktif;

// Gabungkan field dan value untuk query
$fieldsStr = implode(', ', $fields);
$valuesStr = implode(', ', $values);

$sql = "INSERT INTO anak ({$fieldsStr}) VALUES ({$valuesStr})";
$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Anak berhasil ditambahkan",
        "data" => [
            "anakID" => $last_id,
            "namaLengkap" => $namaLengkap,
            "jenisKelamin" => $jenisKelamin,
            "tanggalLahir" => $tanggalLahir,
            "orangtuaID" => $orangtuaID
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