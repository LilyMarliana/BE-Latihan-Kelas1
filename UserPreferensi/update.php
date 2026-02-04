<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$preferensiID           = $_POST['preferensiID'];
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

// query update
$stmt = $conn->prepare("
    UPDATE userpreferensi
    SET
        userID = ?,
        notifPemantauan = ?,
        notifKonsultasi = ?,
        notifEdukasi = ?,
        notifGamifikasi = ?,
        bahasaPreferensi = ?,
        temaPilihan = ?,
        profilPublik = ?,
        bagikanDataAgregat = ?,
        settingJSON = ?
    WHERE preferensiID = ?
");

// s = string, i = integer, b = boolean
$stmt->bind_param(
    "iiiiisiiisi",
    $userID,
    $notifPemantauan,
    $notifKonsultasi,
    $notifEdukasi,
    $notifGamifikasi,
    $bahasaPreferensi,
    $temaPilihan,
    $profilPublik,
    $bagikanDataAgregat,
    $settingJSON,
    $preferensiID
);

// eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data user preferensi berhasil diperbarui",
        "data" => [
            "preferensiID" => $preferensiID,
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
        "status" => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>