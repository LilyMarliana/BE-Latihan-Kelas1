<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$asupanID           = $_POST['asupanID'];
$nutrisiID          = isset($_POST['nutrisiID']) ? $_POST['nutrisiID'] : null;
$waktuMakan         = isset($_POST['waktuMakan']) ? $_POST['waktuMakan'] : null;
$jamMakan           = isset($_POST['jamMakan']) ? $_POST['jamMakan'] : null;
$namaMakanan        = isset($_POST['namaMakanan']) ? $_POST['namaMakanan'] : null;
$porsi              = isset($_POST['porsi']) ? $_POST['porsi'] : null;
$satuanPorsi        = isset($_POST['satuanPorsi']) ? $_POST['satuanPorsi'] : null;
$kalori             = isset($_POST['kalori']) ? $_POST['kalori'] : null;
$protein            = isset($_POST['protein']) ? $_POST['protein'] : null;
$karbohidrat        = isset($_POST['karbohidrat']) ? $_POST['karbohidrat'] : null;
$lemak              = isset($_POST['lemak']) ? $_POST['lemak'] : null;
$serat              = isset($_POST['serat']) ? $_POST['serat'] : null;
$kategoriMakanan    = isset($_POST['kategoriMakanan']) ? $_POST['kategoriMakanan'] : null;
$fotoMakanan        = isset($_POST['fotoMakanan']) ? $_POST['fotoMakanan'] : null;

// Validasi waktuMakan jika diisi
if ($waktuMakan !== null) {
    $validWaktuMakan = ['sarapan', 'makan_siang', 'makan_malam', 'snack'];
    if (!in_array($waktuMakan, $validWaktuMakan)) {
        echo json_encode([
            "status" => "error",
            "message" => "Waktu makan tidak valid"
        ]);
        exit;
    }
}

// Bangun query dinamis berdasarkan field yang diisi
$fields = [];
$params = [];
$paramTypes = '';

if ($nutrisiID !== null) {
    $fields[] = "nutrisiID = ?";
    $params[] = $nutrisiID;
    $paramTypes .= 'i';
}

if ($waktuMakan !== null) {
    $fields[] = "waktuMakan = ?";
    $params[] = $waktuMakan;
    $paramTypes .= 's';
}

if ($jamMakan !== null) {
    $fields[] = "jamMakan = ?";
    $params[] = $jamMakan;
    $paramTypes .= 's';
}

if ($namaMakanan !== null) {
    $fields[] = "namaMakanan = ?";
    $params[] = $namaMakanan;
    $paramTypes .= 's';
}

if ($porsi !== null) {
    $fields[] = "porsi = ?";
    $params[] = $porsi;
    $paramTypes .= 'd';
}

if ($satuanPorsi !== null) {
    $fields[] = "satuanPorsi = ?";
    $params[] = $satuanPorsi;
    $paramTypes .= 's';
}

if ($kalori !== null) {
    $fields[] = "kalori = ?";
    $params[] = $kalori;
    $paramTypes .= 'd';
}

if ($protein !== null) {
    $fields[] = "protein = ?";
    $params[] = $protein;
    $paramTypes .= 'd';
}

if ($karbohidrat !== null) {
    $fields[] = "karbohidrat = ?";
    $params[] = $karbohidrat;
    $paramTypes .= 'd';
}

if ($lemak !== null) {
    $fields[] = "lemak = ?";
    $params[] = $lemak;
    $paramTypes .= 'd';
}

if ($serat !== null) {
    $fields[] = "serat = ?";
    $params[] = $serat;
    $paramTypes .= 'd';
}

if ($kategoriMakanan !== null) {
    $fields[] = "kategoriMakanan = ?";
    $params[] = $kategoriMakanan;
    $paramTypes .= 's';
}

if ($fotoMakanan !== null) {
    $fields[] = "fotoMakanan = ?";
    $params[] = $fotoMakanan;
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
$sql = "UPDATE AsupanMakanan SET {$fieldsStr} WHERE asupanID = ?";
$params[] = $asupanID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Asupan Makanan berhasil diperbarui",
            "data" => [
                "asupanID" => $asupanID,
                "waktuMakan" => $waktuMakan,
                "namaMakanan" => $namaMakanan
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Asupan Makanan tidak ditemukan"
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