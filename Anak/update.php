<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$anakID              = $_POST['anakID'];
$orangtuaID          = isset($_POST['orangtuaID']) ? $_POST['orangtuaID'] : null;
$namaLengkap         = isset($_POST['namaLengkap']) ? $_POST['namaLengkap'] : null;
$jenisKelamin        = isset($_POST['jenisKelamin']) ? $_POST['jenisKelamin'] : null;
$tanggalLahir        = isset($_POST['tanggalLahir']) ? $_POST['tanggalLahir'] : null;
$tempatLahir         = isset($_POST['tempatLahir']) ? $_POST['tempatLahir'] : null;
$beratBadanLahir     = isset($_POST['beratBadanLahir']) ? $_POST['beratBadanLahir'] : null;
$tinggiLahir         = isset($_POST['tinggiLahir']) ? $_POST['tinggiLahir'] : null;
$golonganDarah       = isset($_POST['golonganDarah']) ? $_POST['golonganDarah'] : null;
$riwayatKelahiran    = isset($_POST['riwayatKelahiran']) ? $_POST['riwayatKelahiran'] : null;
$fotoProfile         = isset($_POST['fotoProfile']) ? $_POST['fotoProfile'] : null;
$statusAktif         = isset($_POST['statusAktif']) ? $_POST['statusAktif'] : null;

// Validasi jenisKelamin jika diisi
if ($jenisKelamin !== null && !in_array($jenisKelamin, ['L', 'P'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Jenis kelamin harus L atau P"
    ]);
    exit;
}

// Validasi statusAktif jika diisi
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

// Bangun query dinamis berdasarkan field yang diisi
$fields = [];
$params = [];
$paramTypes = '';

if ($orangtuaID !== null) {
    $fields[] = "orangtuaID = ?";
    $params[] = $orangtuaID;
    $paramTypes .= 'i';
}

if ($namaLengkap !== null) {
    $fields[] = "namaLengkap = ?";
    $params[] = $namaLengkap;
    $paramTypes .= 's';
}

if ($jenisKelamin !== null) {
    $fields[] = "jenisKelamin = ?";
    $params[] = $jenisKelamin;
    $paramTypes .= 's';
}

if ($tanggalLahir !== null) {
    $fields[] = "tanggalLahir = ?";
    $params[] = $tanggalLahir;
    $paramTypes .= 's';
}

if ($tempatLahir !== null) {
    $fields[] = "tempatLahir = ?";
    $params[] = $tempatLahir;
    $paramTypes .= 's';
}

if ($beratBadanLahir !== null) {
    $fields[] = "beratBadanLahir = ?";
    $params[] = $beratBadanLahir;
    $paramTypes .= 'd';
}

if ($tinggiLahir !== null) {
    $fields[] = "tinggiLahir = ?";
    $params[] = $tinggiLahir;
    $paramTypes .= 'd';
}

if ($golonganDarah !== null) {
    $fields[] = "golonganDarah = ?";
    $params[] = $golonganDarah;
    $paramTypes .= 's';
}

if ($riwayatKelahiran !== null) {
    $fields[] = "riwayatKelahiran = ?";
    $params[] = $riwayatKelahiran;
    $paramTypes .= 's';
}

if ($fotoProfile !== null) {
    $fields[] = "fotoProfile = ?";
    $params[] = $fotoProfile;
    $paramTypes .= 's';
}

if ($statusAktif !== null) {
    $fields[] = "statusAktif = ?";
    $params[] = $statusAktif;
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
$sql = "UPDATE anak SET {$fieldsStr} WHERE anakID = ?";
$params[] = $anakID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Anak berhasil diperbarui",
            "data" => [
                "anakID" => $anakID,
                "namaLengkap" => $namaLengkap,
                "jenisKelamin" => $jenisKelamin
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Anak tidak ditemukan"
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