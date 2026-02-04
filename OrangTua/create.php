<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$userID              = $_POST['userID'];
$namaLengkap         = $_POST['namaLengkap'];
$jenisKelamin        = $_POST['jenisKelamin'];
$tanggalLahir        = $_POST['tanggalLahir'];
$alamat              = isset($_POST['alamat']) ? $_POST['alamat'] : null;
$provinsi            = isset($_POST['provinsi']) ? $_POST['provinsi'] : null;
$kota                = isset($_POST['kota']) ? $_POST['kota'] : null;
$kecamatan           = isset($_POST['kecamatan']) ? $_POST['kecamatan'] : null;
$pendidikanTerakhir  = isset($_POST['pendidikanTerakhir']) ? $_POST['pendidikanTerakhir'] : null;
$pekerjaan           = isset($_POST['pekerjaan']) ? $_POST['pekerjaan'] : null;
$statusPernikahan    = isset($_POST['statusPernikahan']) ? $_POST['statusPernikahan'] : 'menikah';

// Validasi input
if (empty($userID) || empty($namaLengkap) || empty($jenisKelamin) || empty($tanggalLahir)) {
    echo json_encode([
        "status" => "error",
        "message" => "UserID, namaLengkap, jenisKelamin, dan tanggalLahir wajib diisi"
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

// Validasi statusPernikahan
$validStatus = ['menikah', 'single', 'cerai'];
if (!in_array($statusPernikahan, $validStatus)) {
    echo json_encode([
        "status" => "error",
        "message" => "Status pernikahan tidak valid"
    ]);
    exit;
}

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO orangtua
    (userID, namaLengkap, jenisKelamin, tanggalLahir, alamat, provinsi, kota, kecamatan, pendidikanTerakhir, pekerjaan, statusPernikahan)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// i = integer, s = string
$stmt->bind_param(
    "issssssssss",
    $userID,
    $namaLengkap,
    $jenisKelamin,
    $tanggalLahir,
    $alamat,
    $provinsi,
    $kota,
    $kecamatan,
    $pendidikanTerakhir,
    $pekerjaan,
    $statusPernikahan
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Orang Tua berhasil ditambahkan",
        "data" => [
            "orangtuaID" => $last_id,
            "userID" => $userID,
            "namaLengkap" => $namaLengkap,
            "jenisKelamin" => $jenisKelamin,
            "tanggalLahir" => $tanggalLahir
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