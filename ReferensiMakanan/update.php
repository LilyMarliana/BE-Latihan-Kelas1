<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST) dengan pengecekan
$makananID      = isset($_POST['makananID']) ? $_POST['makananID'] : null;
$namaMakanan    = isset($_POST['namaMakanan']) ? $_POST['namaMakanan'] : null;
$kategori       = isset($_POST['kategori']) ? $_POST['kategori'] : null;
$jenisLokal     = isset($_POST['jenisLokal']) ? ($_POST['jenisLokal'] === '1' || $_POST['jenisLokal'] === 'true' ? 1 : 0) : null;
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

// Validasi makananID wajib diisi
if (empty($makananID)) {
    echo json_encode([
        "status" => "error",
        "message" => "makananID wajib diisi"
    ]);
    exit;
}

// Bangun query dinamis berdasarkan field yang diisi
$fields = [];
$params = [];
$paramTypes = '';

if ($namaMakanan !== null) {
    $fields[] = "namaMakanan = ?";
    $params[] = $namaMakanan;
    $paramTypes .= 's';
}

if ($kategori !== null) {
    $fields[] = "kategori = ?";
    $params[] = $kategori;
    $paramTypes .= 's';
}

if ($jenisLokal !== null) {
    $fields[] = "jenisLokal = ?";
    $params[] = $jenisLokal;
    $paramTypes .= 'i';
}

if ($porsiStandar !== null) {
    $fields[] = "porsiStandar = ?";
    $params[] = floatval($porsiStandar);
    $paramTypes .= 'd';
}

if ($satuanStandar !== null) {
    $fields[] = "satuanStandar = ?";
    $params[] = $satuanStandar;
    $paramTypes .= 's';
}

if ($kalori !== null) {
    $fields[] = "kalori = ?";
    $params[] = floatval($kalori);
    $paramTypes .= 'd';
}

if ($protein !== null) {
    $fields[] = "protein = ?";
    $params[] = floatval($protein);
    $paramTypes .= 'd';
}

if ($karbohidrat !== null) {
    $fields[] = "karbohidrat = ?";
    $params[] = floatval($karbohidrat);
    $paramTypes .= 'd';
}

if ($lemak !== null) {
    $fields[] = "lemak = ?";
    $params[] = floatval($lemak);
    $paramTypes .= 'd';
}

if ($serat !== null) {
    $fields[] = "serat = ?";
    $params[] = floatval($serat);
    $paramTypes .= 'd';
}

if ($vitamin !== null) {
    $fields[] = "vitamin = ?";
    $params[] = $vitamin;
    $paramTypes .= 's';
}

if ($mineral !== null) {
    $fields[] = "mineral = ?";
    $params[] = $mineral;
    $paramTypes .= 's';
}

if ($sumberData !== null) {
    $fields[] = "sumberData = ?";
    $params[] = $sumberData;
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
$sql = "UPDATE referensimakanan SET {$fieldsStr} WHERE makananID = ?";
$params[] = $makananID;
$paramTypes .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// eksekusi
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Data referensi makanan berhasil diperbarui",
            "data" => [
                "makananID" => $makananID,
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
            "status" => "error",
            "message" => "Referensi makanan tidak ditemukan"
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