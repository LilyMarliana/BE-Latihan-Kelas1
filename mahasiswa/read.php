<?php
// Koneksi database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Array penampung data
$data = [];

// Cek parameter GET
if (isset($_GET['nama']) || isset($_GET['id'])) {

    // 🔍 Cari berdasarkan nama
    if (isset($_GET['nama'])) {
        $nama = $_GET['nama'];

        $stmt = $conn->prepare("
            SELECT * FROM pengguna_growwell 
            WHERE nama = ?
        ");
        $stmt->bind_param("s", $nama);

    } else {
        // 🔍 Cari berdasarkan ID
        $id = $_GET['id'];

        $stmt = $conn->prepare("
            SELECT * FROM pengguna_growwell 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
    }

    // Eksekusi
    $stmt->execute();
    $result = $stmt->get_result();

    // Ambil data
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();

} else {

    // 📋 Ambil semua data
    $sql = "SELECT * FROM pengguna_growwell";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Response
echo json_encode([
    "status"  => "success",
    "message" => count($data) > 0 ? "Data ditemukan" : "Data kosong",
    "data"    => $data
]);

$conn->close();
?>
