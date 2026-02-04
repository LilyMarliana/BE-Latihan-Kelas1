<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$userID                 = $_POST['userID'];
$notifPemantauan        = $_POST['notifPemantauan'];
$notifKonsultasi        = $_POST['notifKonsultasi'];
$notifEdukasi           = $_POST['notifEdukasi'];
$notifGamifikasi        = $_POST['notifGamifikasi'];
$bahasaPreferensi       = $_POST['bahasaPreferensi'];
$temaPilihan            = $_POST['temaPilihan'];
$profilPublik           = $_POST['profilPublik'];
$bagikanDataAgregat     = $_POST['bagikanDataAgregat'];
$settingJSON            = $_POST['settingJSON'];

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO userpreferensi
    (userID, notifPemantauan, notifKonsultasi, notifEdukasi, notifGamifikasi, bahasaPreferensi, temaPilihan, profilPublik, bagikanDataAgregat, settingJSON)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// s = string, i = integer, b = boolean
$stmt->bind_param(
    "iiiiisiiis",
    $userID,
    $notifPemantauan,
    $notifKonsultasi,
    $notifEdukasi,
    $notifGamifikasi,
    $bahasaPreferensi,
    $temaPilihan,
    $profilPublik,
    $bagikanDataAgregat,
    $settingJSON
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data user preferensi berhasil ditambahkan",
        "data" => [
            "preferensiID" => $last_id,
            "userID" => $userID,
            "notifPemantauan" => $notifPemantauan,
            "notifKonsultasi" => $notifKonsultasi,
            "notifEdukasi" => $notifEdukasi,
            "notifGamifikasi" => $notifGamifikasi,
            "bahasaPreferensi" => $bahasaPreferensi,
            "temaPilihan" => $temaPilihan,
            "profilPublik" => $profilPublik,
            "bagikanDataAgregat" => $bagikanDataAgregat,
            "settingJSON" => $settingJSON
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