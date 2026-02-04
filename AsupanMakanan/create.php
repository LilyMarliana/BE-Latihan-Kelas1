<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$nutrisiID           = $_POST['nutrisiID'];
$waktuMakan          = $_POST['waktuMakan'];
$jamMakan            = isset($_POST['jamMakan']) ? $_POST['jamMakan'] : null;
$namaMakanan         = $_POST['namaMakanan'];
$porsi               = $_POST['porsi'];
$satuanPorsi         = isset($_POST['satuanPorsi']) ? $_POST['satuanPorsi'] : null;
$kalori              = isset($_POST['kalori']) ? $_POST['kalori'] : null;
$protein             = isset($_POST['protein']) ? $_POST['protein'] : null;
$karbohidrat         = isset($_POST['karbohidrat']) ? $_POST['karbohidrat'] : null;
$lemak               = isset($_POST['lemak']) ? $_POST['lemak'] : null;
$serat               = isset($_POST['serat']) ? $_POST['serat'] : null;
$kategoriMakanan     = isset($_POST['kategoriMakanan']) ? $_POST['kategoriMakanan'] : null;
$fotoMakanan         = isset($_POST['fotoMakanan']) ? $_POST['fotoMakanan'] : null;

// Validasi input
if (empty($nutrisiID) || empty($waktuMakan) || empty($namaMakanan) || empty($porsi)) {
    echo json_encode([
        "status" => "error",
        "message" => "NutrisiID, waktuMakan, namaMakanan, dan porsi wajib diisi"
    ]);
    exit;
}

// Validasi waktuMakan
$validWaktuMakan = ['sarapan', 'makan_siang', 'makan_malam', 'snack'];
if (!in_array($waktuMakan, $validWaktuMakan)) {
    echo json_encode([
        "status" => "error",
        "message" => "Waktu makan tidak valid"
    ]);
    exit;
}

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO AsupanMakanan
    (nutrisiID, waktuMakan, jamMakan, namaMakanan, porsi, satuanPorsi, kalori, protein, karbohidrat, lemak, serat, kategoriMakanan, fotoMakanan)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// i = integer, s = string, d = double
$stmt->bind_param(
    "issssdsssssss",
    $nutrisiID,
    $waktuMakan,
    $jamMakan,
    $namaMakanan,
    $porsi,
    $satuanPorsi,
    $kalori,
    $protein,
    $karbohidrat,
    $lemak,
    $serat,
    $kategoriMakanan,
    $fotoMakanan
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Asupan Makanan berhasil ditambahkan",
        "data" => [
            "asupanID" => $last_id,
            "waktuMakan" => $waktuMakan,
            "namaMakanan" => $namaMakanan,
            "porsi" => $porsi
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