<?php
// ============================================
// foto.php — Menampilkan gambar BLOB dari DB
// Cara pakai: <img src="foto.php?tabel=dokumentasi&id=1">
// ============================================
require_once 'config.php';

$tabel = $_GET['tabel'] ?? '';
$id    = (int)($_GET['id'] ?? 0);

// Whitelist tabel yang boleh diakses
$allowed = ['profil', 'dokumentasi', 'sertifikat'];
if (!in_array($tabel, $allowed) || $id <= 0) {
    http_response_code(400);
    exit('Parameter tidak valid.');
}

// Kolom foto sesuai tabel
$kolom_foto = ($tabel === 'profil') ? 'foto_profil' : 'foto';
$kolom_mime = ($tabel === 'profil') ? 'foto_profil_mime' : 'foto_mime';

$stmt = $pdo->prepare("SELECT $kolom_foto, $kolom_mime FROM $tabel WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row || empty($row[$kolom_foto])) {
    // Tampilkan placeholder jika foto belum ada
    http_response_code(404);
    exit('Foto tidak ditemukan.');
}

header("Content-Type: " . $row[$kolom_mime]);
header("Cache-Control: max-age=86400");
echo $row[$kolom_foto];
?>
