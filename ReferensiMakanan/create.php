<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST dengan pengecekan
$namaMakanan    = isset($_POST['namaMakanan']) ? $_POST['namaMakanan'] : null;
$kategori       = isset($_POST['kategori']) ? $_POST['kategori'] : null;
$jenisLokal     = isset($_POST['jenisLokal']) ? ($_POST['jenisLokal'] === '1' || $_POST['jenisLokal'] === 'true' || $_POST['jenisLokal'] === '1' ? 1 : 0) : 0;
$porsiStandar   = isset($_POST['porsiStandar']) ? $_POST['porsiStandar'] : null;
$satuanStandar  = isset($_POST['satuanStandar']) ? $_POST['satuanStandar'] : null;
$kalori         = isset($_POST['kalori']) ? $_POST['kalori'] : null;
$protein        = isset($_POST['protein']) ? $_POST['protein'] : null;
$karbohidrat    = isset($_POST['karbohidrat']) ? $_POST['karbohidrat'] : null;
$lemak          = isset($_POST['lemak']) ? $_POST['lemak'] : null;
$serat          = isset($_POST['serat']) ? $_POST['serat'] : null;
$vitamin        = isset($_POST['vitamin']) ? $_POST['vitamin'] : null;
$mineral        = isset($_POST['mineral']) ? $_POST['mineral'] : null;
$sumberData     = isset($_POST['sumberData']) ? $_POST['sumberData'] : null;
$fotoMakanan    = isset($_POST['fotoMakanan']) ? $_POST['fotoMakanan'] : null;

// Validasi input wajib
if (empty($namaMakanan) || empty($kategori)) {
    echo json_encode([
        "status" => "error",
        "message" => "namaMakanan dan kategori wajib diisi"
    ]);
    exit;
}

// Bangun query dan parameter dinamis berdasarkan nilai yang tidak null
$fields = [];
$values = [];
$paramTypes = '';
$params = [];

$fields[] = 'namaMakanan';
$values[] = '?';
$paramTypes .= 's';
$params[] = $namaMakanan;

$fields[] = 'kategori';
$values[] = '?';
$paramTypes .= 's';
$params[] = $kategori;

$fields[] = 'jenisLokal';
$values[] = '?';
$paramTypes .= 'i';
$params[] = $jenisLokal;

if ($porsiStandar !== null) {
    $fields[] = 'porsiStandar';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($porsiStandar);
}

if ($satuanStandar !== null) {
    $fields[] = 'satuanStandar';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $satuanStandar;
}

if ($kalori !== null) {
    $fields[] = 'kalori';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($kalori);
}

if ($protein !== null) {
    $fields[] = 'protein';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($protein);
}

if ($karbohidrat !== null) {
    $fields[] = 'karbohidrat';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($karbohidrat);
}

if ($lemak !== null) {
    $fields[] = 'lemak';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($lemak);
}

if ($serat !== null) {
    $fields[] = 'serat';
    $values[] = '?';
    $paramTypes .= 'd';
    $params[] = floatval($serat);
}

if ($vitamin !== null) {
    $fields[] = 'vitamin';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $vitamin;
}

if ($mineral !== null) {
    $fields[] = 'mineral';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $mineral;
}

if ($sumberData !== null) {
    $fields[] = 'sumberData';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $sumberData;
}

if ($fotoMakanan !== null) {
    $fields[] = 'fotoMakanan';
    $values[] = '?';
    $paramTypes .= 's';
    $params[] = $fotoMakanan;
}

// Gabungkan field dan value untuk query
$fieldsStr = implode(', ', $fields);
$valuesStr = implode(', ', $values);

$sql = "INSERT INTO referensimakanan ({$fieldsStr}) VALUES ({$valuesStr})";
$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data referensi makanan berhasil ditambahkan",
        "data" => [
            "makananID" => $last_id,
            "namaMakanan" => $namaMakanan,
            "kategori" => $kategori,
            "jenisLokal" => $jenisLokal,
            "porsiStandar" => $porsiStandar,
            "satuanStandar" => $satuanStandar,
            "kalori" => $kalori,
            "protein" => $protein,
            "karbohidrat" => $karbohidrat,
            "lemak" => $lemak,
            "serat" => $serat,
            "vitamin" => $vitamin,
            "mineral" => $mineral,
            "sumberData" => $sumberData,
            "fotoMakanan" => $fotoMakanan
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