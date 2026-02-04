<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$orangtuaID          = isset($_POST['orangtuaID']) ? $_POST['orangtuaID'] : null;
$userID              = isset($_POST['userID']) ? $_POST['userID'] : null;
$namaLengkap         = isset($_POST['namaLengkap']) ? $_POST['namaLengkap'] : null;
$jenisKelamin        = isset($_POST['jenisKelamin']) ? $_POST['jenisKelamin'] : null;
$tanggalLahir        = isset($_POST['tanggalLahir']) ? $_POST['tanggalLahir'] : null;
$alamat              = isset($_POST['alamat']) ? $_POST['alamat'] : null;
$provinsi            = isset($_POST['provinsi']) ? $_POST['provinsi'] : null;
$kota                = isset($_POST['kota']) ? $_POST['kota'] : null;
$kecamatan           = isset($_POST['kecamatan']) ? $_POST['kecamatan'] : null;
$pendidikanTerakhir  = isset($_POST['pendidikanTerakhir']) ? $_POST['pendidikanTerakhir'] : null;
$pekerjaan           = isset($_POST['pekerjaan']) ? $_POST['pekerjaan'] : null;
$statusPernikahan    = isset($_POST['statusPernikahan']) ? $_POST['statusPernikahan'] : null;

// Validasi input
if (empty($orangtuaID)) {
    echo json_encode([
        "status" => "error",
        "message" => "OrangTuaID wajib diisi"
    ]);
    exit;
}

// Validasi jenisKelamin jika diisi
if ($jenisKelamin !== null && !in_array($jenisKelamin, ['L', 'P'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Jenis kelamin harus L atau P"
    ]);
    exit;
}

// Validasi statusPernikahan jika diisi
if ($statusPernikahan !== null) {
    $validStatus = ['menikah', 'single', 'cerai'];
    if (!in_array($statusPernikahan, $validStatus)) {
        echo json_encode([
            "status" => "error",
            "message" => "Status pernikahan tidak valid"
        ]);
        exit;
    }
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

if ($alamat !== null) {
    $fields[] = "alamat = ?";
    $params[] = $alamat;
    $paramTypes .= 's';
}

if ($provinsi !== null) {
    $fields[] = "provinsi = ?";
    $params[] = $provinsi;
    $paramTypes .= 's';
}

if ($kota !== null) {
    $fields[] = "kota = ?";
    $params[] = $kota;
    $paramTypes .= 's';
}

if ($kecamatan !== null) {
    $fields[] = "kecamatan = ?";
    $params[] = $kecamatan;
    $paramTypes .= 's';
}

if ($pendidikanTerakhir !== null) {
    $fields[] = "pendidikanTerakhir = ?";
    $params[] = $pendidikanTerakhir;
    $paramTypes .= 's';
}

if ($pekerjaan !== null) {
    $fields[] = "pekerjaan = ?";
    $params[] = $pekerjaan;
    $paramTypes .= 's';
}

if ($statusPernikahan !== null) {
    $fields[] = "statusPernikahan = ?";
    $params[] = $statusPernikahan;
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
$sql = "UPDATE orangtua SET {$fieldsStr} WHERE orangtuaID = ?";
$params[] = $orangtuaID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Orang Tua berhasil diperbarui",
            "data" => [
                "orangtuaID" => $orangtuaID,
                "namaLengkap" => $namaLengkap
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Orang Tua tidak ditemukan"
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