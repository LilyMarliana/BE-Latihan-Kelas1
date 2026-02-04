<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST dengan pengecekan
$kontenID           = isset($_POST['kontenID']) ? $_POST['kontenID'] : null;
$userID             = isset($_POST['userID']) ? $_POST['userID'] : null;
$tipeInteraksi      = isset($_POST['tipeInteraksi']) ? $_POST['tipeInteraksi'] : null;
$durasiView         = isset($_POST['durasiView']) ? $_POST['durasiView'] : null;

// Validasi input wajib
if (empty($kontenID) || empty($userID) || empty($tipeInteraksi)) {
    echo json_encode([
        "status" => "error",
        "message" => "kontenID, userID, dan tipeInteraksi wajib diisi"
    ]);
    exit;
}

// Validasi tipeInteraksi
$validTipe = ['view', 'like', 'share', 'bookmark', 'complete'];
if (!in_array($tipeInteraksi, $validTipe)) {
    echo json_encode([
        "status" => "error",
        "message" => "Tipe interaksi tidak valid. Pilihan yang valid: " . implode(', ', $validTipe)
    ]);
    exit;
}

// Cek apakah kombinasi userID, kontenID, dan tipeInteraksi sudah ada
$checkSql = "SELECT interaksiID FROM interaksikonten WHERE userID = ? AND kontenID = ? AND tipeInteraksi = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("iii", $userID, $kontenID, $tipeInteraksi);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    // Jika sudah ada, lakukan update durasiView jika diberikan
    if ($durasiView !== null) {
        $updateSql = "UPDATE interaksikonten SET durasiView = ?, tanggalInteraksi = CURRENT_TIMESTAMP WHERE userID = ? AND kontenID = ? AND tipeInteraksi = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("iiii", $durasiView, $userID, $kontenID, $tipeInteraksi);

        if ($updateStmt->execute()) {
            echo json_encode([
                "status"  => "success",
                "message" => "Data interaksi konten berhasil diperbarui",
                "data" => [
                    "kontenID" => $kontenID,
                    "userID" => $userID,
                    "tipeInteraksi" => $tipeInteraksi,
                    "durasiView" => $durasiView
                ]
            ]);
        } else {
            echo json_encode([
                "status"  => "error",
                "message" => $updateStmt->error
            ]);
        }
        $updateStmt->close();
    } else {
        echo json_encode([
            "status"  => "success",
            "message" => "Interaksi sudah tercatat sebelumnya",
            "data" => [
                "kontenID" => $kontenID,
                "userID" => $userID,
                "tipeInteraksi" => $tipeInteraksi
            ]
        ]);
    }
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// Validasi bahwa kontenID dan userID yang disediakan benar-benar ada
$checkKontenSql = "SELECT kontenID FROM kontenedukasi WHERE kontenID = ?";
$checkKontenStmt = $conn->prepare($checkKontenSql);
$checkKontenStmt->bind_param("i", $kontenID);
$checkKontenStmt->execute();
$checkKontenResult = $checkKontenStmt->get_result();

if ($checkKontenResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Konten dengan ID {$kontenID} tidak ditemukan"
    ]);
    $checkKontenStmt->close();
    $conn->close();
    exit;
}
$checkKontenStmt->close();

$checkUserSql = "SELECT userID FROM user WHERE userID = ?";
$checkUserStmt = $conn->prepare($checkUserSql);
$checkUserStmt->bind_param("i", $userID);
$checkUserStmt->execute();
$checkUserResult = $checkUserStmt->get_result();

if ($checkUserResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User dengan ID {$userID} tidak ditemukan"
    ]);
    $checkUserStmt->close();
    $conn->close();
    exit;
}
$checkUserStmt->close();

// Jika belum ada, lakukan insert
$stmt = $conn->prepare("
    INSERT INTO interaksikonten
    (kontenID, userID, tipeInteraksi, durasiView)
    VALUES (?, ?, ?, ?)
");

// s = string, i = integer
$stmt->bind_param(
    "iisi",
    $kontenID,
    $userID,
    $tipeInteraksi,
    $durasiView
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data interaksi konten berhasil ditambahkan",
        "data" => [
            "interaksiID" => $last_id,
            "kontenID" => $kontenID,
            "userID" => $userID,
            "tipeInteraksi" => $tipeInteraksi,
            "durasiView" => $durasiView
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