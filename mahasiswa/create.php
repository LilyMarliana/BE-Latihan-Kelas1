<?php
include_once '../db.php';

header('Content-Type: application/json');

$nim     = $_POST['nim'];
$nama    = $_POST['nama'];
$alamat  = $_POST['alamat'];
$no_telp = $_POST['no_telp'];

$stmt = $conn->prepare("
    INSERT INTO tb_mahasiswa (nim, nama, alamat, no_telp)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("ssss", $nim, $nama, $alamat, $no_telp);

if ($stmt->execute()) {

    // Ambil ID terakhir
    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data berhasil ditambahkan",
        "data"    => [
            "id"     => $last_id,
            "nim"     => $nim,
            "nama"    => $nama,
            "alamat"  => $alamat,
            "no_telp" => $no_telp
        ]
    ]);

} else {

    echo json_encode([
        "status"  => "error",
        "message" => $stmt->error
    ]);

}

$stmt->close();
$conn->close();
?>
