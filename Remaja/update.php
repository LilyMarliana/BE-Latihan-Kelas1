<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST) dengan pengecekan
$remajaID           = isset($_POST['remajaID']) ? $_POST['remajaID'] : null;
$userID             = isset($_POST['userID']) ? $_POST['userID'] : null;
$nama               = isset($_POST['nama']) ? $_POST['nama'] : null;
$tanggalLahir       = isset($_POST['tanggalLahir']) ? $_POST['tanggalLahir'] : null;
$jenisKelamin       = isset($_POST['jenisKelamin']) ? $_POST['jenisKelamin'] : null;
$sekolah            = isset($_POST['sekolah']) ? $_POST['sekolah'] : null;
$kelas              = isset($_POST['kelas']) ? $_POST['kelas'] : null;
$tingkatAktivitas   = isset($_POST['tingkatAktivitas']) ? $_POST['tingkatAktivitas'] : null;
$targetKesehatan    = isset($_POST['targetKesehatan']) ? $_POST['targetKesehatan'] : null;
$totalPoin          = isset($_POST['totalPoin']) ? $_POST['totalPoin'] : null;
$level              = isset($_POST['level']) ? $_POST['level'] : null;

// Validasi remajaID wajib diisi
if (empty($remajaID)) {
    echo json_encode([
        "status" => "error",
        "message" => "remajaID wajib diisi"
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

// Validasi tingkatAktivitas jika diisi
if ($tingkatAktivitas !== null) {
    $validAktivitas = ['rendah', 'sedang', 'tinggi'];
    if (!in_array($tingkatAktivitas, $validAktivitas)) {
        echo json_encode([
            "status" => "error",
            "message" => "Tingkat aktivitas harus rendah, sedang, atau tinggi"
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

if ($nama !== null) {
    $fields[] = "nama = ?";
    $params[] = $nama;
    $paramTypes .= 's';
}

if ($tanggalLahir !== null) {
    $fields[] = "tanggalLahir = ?";
    $params[] = $tanggalLahir;
    $paramTypes .= 's';
}

if ($jenisKelamin !== null) {
    $fields[] = "jenisKelamin = ?";
    $params[] = $jenisKelamin;
    $paramTypes .= 's';
}

if ($sekolah !== null) {
    $fields[] = "sekolah = ?";
    $params[] = $sekolah;
    $paramTypes .= 's';
}

if ($kelas !== null) {
    $fields[] = "kelas = ?";
    $params[] = $kelas;
    $paramTypes .= 's';
}

if ($tingkatAktivitas !== null) {
    $fields[] = "tingkatAktivitas = ?";
    $params[] = $tingkatAktivitas;
    $paramTypes .= 's';
}

if ($targetKesehatan !== null) {
    $fields[] = "targetKesehatan = ?";
    $params[] = $targetKesehatan;
    $paramTypes .= 's';
}

if ($totalPoin !== null) {
    $fields[] = "totalPoin = ?";
    $params[] = $totalPoin;
    $paramTypes .= 'i';
}

if ($level !== null) {
    $fields[] = "level = ?";
    $params[] = $level;
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
$sql = "UPDATE remaja SET {$fieldsStr} WHERE remajaID = ?";
$params[] = $remajaID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Data remaja berhasil diperbarui",
            "data" => [
                "remajaID" => $remajaID,
                "userID" => $userID,
                "nama" => $nama,
                "tanggalLahir" => $tanggalLahir,
                "jenisKelamin" => $jenisKelamin,
                "sekolah" => $sekolah,
                "kelas" => $kelas,
                "tingkatAktivitas" => $tingkatAktivitas,
                "targetKesehatan" => $targetKesehatan,
                "totalPoin" => $totalPoin,
                "level" => $level
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Remaja tidak ditemukan"
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