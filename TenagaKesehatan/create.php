<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST dengan pengecekan
$userID           = isset($_POST['userID']) ? $_POST['userID'] : null;
$namaLengkap      = isset($_POST['namaLengkap']) ? $_POST['namaLengkap'] : null;
$jenisKelamin     = isset($_POST['jenisKelamin']) ? $_POST['jenisKelamin'] : null;
$spesialisasi     = isset($_POST['spesialisasi']) ? $_POST['spesialisasi'] : null;
$nomorSTR         = isset($_POST['nomorSTR']) ? $_POST['nomorSTR'] : null;
$instansi         = isset($_POST['instansi']) ? $_POST['instansi'] : null;
$alamatInstansi   = isset($_POST['alamatInstansi']) ? $_POST['alamatInstansi'] : null;
$wilayahKerjaID   = isset($_POST['wilayahKerjaID']) ? $_POST['wilayahKerjaID'] : null;

// Validasi input wajib
if (empty($userID) || empty($namaLengkap) || empty($jenisKelamin) || empty($spesialisasi)) {
    echo json_encode([
        "status" => "error",
        "message" => "userID, namaLengkap, jenisKelamin, dan spesialisasi wajib diisi"
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

// Bangun query dan parameter dinamis berdasarkan nilai yang tidak null
$fields = [];
$values = [];
$paramTypes = '';
$params = [];

$fields[] = 'userID';
$values[] = '?';
$paramTypes .= 'i';
$params[] = $userID;

$fields[] = 'namaLengkap';
$values[] = '?';
$paramTypes .= 's';
$params[] = $namaLengkap;

$fields[] = 'jenisKelamin';
$values[] = '?';
$paramTypes .= 's';
$params[] = $jenisKelamin;

$fields[] = 'spesialisasi';
$values[] = '?';
$paramTypes .= 's';
$params[] = $spesialisasi;

if ($nomorSTR !== null) {
    $fields[] = 'nomorSTR';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $nomorSTR;
}

if ($instansi !== null) {
    $fields[] = 'instansi';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $instansi;
}

if ($alamatInstansi !== null) {
    $fields[] = 'alamatInstansi';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $alamatInstansi;
}

if ($wilayahKerjaID !== null) {
    $fields[] = 'wilayahKerjaID';
    $values[] = '?';
    $paramTypes .= 'i';
    $params[] = $wilayahKerjaID;
}

// Gabungkan field dan value untuk query
$fieldsStr = implode(', ', $fields);
$valuesStr = implode(', ', $values);

$sql = "INSERT INTO tenagakesehatan ({$fieldsStr}) VALUES ({$valuesStr})";
$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data tenaga kesehatan berhasil ditambahkan",
        "data" => [
            "tenagaID" => $last_id,
            "userID" => $userID,
            "namaLengkap" => $namaLengkap,
            "jenisKelamin" => $jenisKelamin,
            "spesialisasi" => $spesialisasi,
            "nomorSTR" => $nomorSTR,
            "instansi" => $instansi,
            "alamatInstansi" => $alamatInstansi,
            "wilayahKerjaID" => $wilayahKerjaID
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