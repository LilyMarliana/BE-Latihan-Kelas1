<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST) dengan pengecekan
$tenagaID         = isset($_POST['tenagaID']) ? $_POST['tenagaID'] : null;
$userID           = isset($_POST['userID']) ? $_POST['userID'] : null;
$namaLengkap      = isset($_POST['namaLengkap']) ? $_POST['namaLengkap'] : null;
$jenisKelamin     = isset($_POST['jenisKelamin']) ? $_POST['jenisKelamin'] : null;
$spesialisasi     = isset($_POST['spesialisasi']) ? $_POST['spesialisasi'] : null;
$nomorSTR         = isset($_POST['nomorSTR']) ? $_POST['nomorSTR'] : null;
$instansi         = isset($_POST['instansi']) ? $_POST['instansi'] : null;
$alamatInstansi   = isset($_POST['alamatInstansi']) ? $_POST['alamatInstansi'] : null;
$wilayahKerjaID   = isset($_POST['wilayahKerjaID']) ? $_POST['wilayahKerjaID'] : null;

// Validasi tenagaID wajib diisi
if (empty($tenagaID)) {
    echo json_encode([
        "status" => "error",
        "message" => "tenagaID wajib diisi"
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

if ($spesialisasi !== null) {
    $fields[] = "spesialisasi = ?";
    $params[] = $spesialisasi;
    $paramTypes .= 's';
}

if ($nomorSTR !== null) {
    $fields[] = "nomorSTR = ?";
    $params[] = $nomorSTR;
    $paramTypes .= 's';
}

if ($instansi !== null) {
    $fields[] = "instansi = ?";
    $params[] = $instansi;
    $paramTypes .= 's';
}

if ($alamatInstansi !== null) {
    $fields[] = "alamatInstansi = ?";
    $params[] = $alamatInstansi;
    $paramTypes .= 's';
}

if ($wilayahKerjaID !== null) {
    $fields[] = "wilayahKerjaID = ?";
    $params[] = $wilayahKerjaID;
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
$sql = "UPDATE tenagakesehatan SET {$fieldsStr} WHERE tenagaID = ?";
$params[] = $tenagaID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Data tenaga kesehatan berhasil diperbarui",
            "data" => [
                "tenagaID" => $tenagaID,
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
            "status" => "error",
            "message" => "Tenaga kesehatan tidak ditemukan"
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