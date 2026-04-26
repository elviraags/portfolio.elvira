<?php
// ============================================
// upload.php — Admin Upload Foto ke Database
// ============================================
require_once 'config.php';

$pesan = '';
$error = '';

$dokumentasi_list = $pdo->query("SELECT id, keterangan FROM dokumentasi WHERE profil_id = 1 ORDER BY urutan")->fetchAll();
$sertifikat_list  = $pdo->query("SELECT id, judul FROM sertifikat WHERE profil_id = 1 ORDER BY urutan")->fetchAll();
$profil           = $pdo->query("SELECT id, nama FROM profil WHERE id = 1")->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tabel = $_POST['tabel'] ?? '';
    $id    = (int)($_POST['id'] ?? 0);
    $file  = $_FILES['foto'] ?? null;

    $allowed_tabel = ['profil', 'dokumentasi', 'sertifikat'];
    $allowed_mime  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (!in_array($tabel, $allowed_tabel) || $id <= 0) {
        $error = 'Target tidak valid.';
    } elseif (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Gagal upload file. Coba lagi.';
    } elseif (!in_array($file['type'], $allowed_mime)) {
        $error = 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP.';
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $error = 'Ukuran file maksimal 5MB.';
    } else {
        $foto_blob = file_get_contents($file['tmp_name']);
        $foto_mime = $file['type'];

        if ($tabel === 'profil') {
            $stmt = $pdo->prepare("UPDATE profil SET foto_profil = ?, foto_profil_mime = ? WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE $tabel SET foto = ?, foto_mime = ? WHERE id = ?");
        }

        $stmt->bindParam(1, $foto_blob, PDO::PARAM_LOB);
        $stmt->bindParam(2, $foto_mime);
        $stmt->bindParam(3, $id, PDO::PARAM_INT);
        $stmt->execute();

        $pesan = 'Foto berhasil diupload ke database.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto — Admin Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --ivory: #f7f4ef;
            --ink:   #1a1814;
            --gold:  #c9a84c;
            --gold-lt: #e8d5a3;
            --muted: #8a8070;
            --border: #ddd5c8;
            --surface: #f0ebe3;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--ivory);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        .upload-wrap {
            width: 100%;
            max-width: 520px;
        }

        .page-header {
            margin-bottom: 2.5rem;
        }

        .page-eyebrow {
            font-size: 0.68rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--gold);
            font-weight: 500;
            display: block;
            margin-bottom: 0.4rem;
        }

        .page-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 300;
            color: var(--ink);
            line-height: 1.15;
        }

        .page-line {
            width: 32px;
            height: 1.5px;
            background: var(--gold);
            margin: 0.8rem 0 0.6rem;
        }

        .page-sub {
            font-size: 0.82rem;
            color: var(--muted);
        }

        /* Alert */
        .alert-ok, .alert-err {
            padding: 0.85rem 1.1rem;
            border-radius: 0;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            border-left: 3px solid;
        }
        .alert-ok  { background: #f0faf3; border-color: #5cb85c; color: #2d6a30; }
        .alert-err { background: #fdf3f3; border-color: #d9534f; color: #7a2020; }

        /* Card */
        .upload-card {
            background: #fff;
            border: 1px solid var(--border);
            padding: 2rem 2.2rem;
        }

        /* Form labels */
        .field { margin-bottom: 1.5rem; }

        .field label {
            display: block;
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 0.5rem;
        }

        .field select,
        .field input[type=file] {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 0;
            padding: 0.6rem 0.85rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            color: var(--ink);
            background: var(--ivory);
            transition: border-color 0.25s;
            outline: none;
            appearance: none;
        }

        .field select:focus,
        .field input[type=file]:focus {
            border-color: var(--gold);
        }

        .field select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%238a8070' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.85rem center;
            padding-right: 2.2rem;
        }

        .field .hint {
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 0.35rem;
        }

        /* Preview */
        .preview-wrap {
            margin-bottom: 1.5rem;
        }

        .preview-wrap img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border: 1px solid var(--border);
            display: none;
        }

        /* Submit */
        .btn-submit {
            width: 100%;
            background: var(--ink);
            color: var(--ivory);
            border: none;
            padding: 0.85rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.25s;
        }

        .btn-submit:hover {
            background: var(--gold);
            color: var(--ink);
        }

        /* Back link */
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.78rem;
            color: var(--muted);
            text-decoration: none;
            letter-spacing: 0.08em;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--ink); }
    </style>
</head>
<body>

<div class="upload-wrap">

    <div class="page-header">
        <span class="page-eyebrow">Admin Panel</span>
        <h1 class="page-title">Upload Foto</h1>
        <div class="page-line"></div>
        <p class="page-sub">Foto disimpan sebagai BLOB langsung di MySQL</p>
    </div>

    <?php if ($pesan): ?>
        <div class="alert-ok">✓ <?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert-err">✕ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="upload-card">
        <form method="POST" enctype="multipart/form-data">

            <div class="field">
                <label>Upload ke mana?</label>
                <select name="tabel" id="selectTabel" required onchange="updateTarget(this.value)">
                    <option value="">— Pilih target —</option>
                    <option value="profil">Foto Profil</option>
                    <option value="dokumentasi">Dokumentasi Kegiatan</option>
                    <option value="sertifikat">Sertifikat</option>
                </select>
            </div>

            <div class="field" id="wrapId" style="display:none;">
                <label>Pilih Item</label>
                <select name="id" id="selectId" required>
                    <option value="">— Pilih dulu di atas —</option>
                </select>
            </div>

            <div class="field">
                <label>File Foto</label>
                <input type="file" name="foto" accept="image/*" required onchange="previewFoto(this)">
                <p class="hint">Format: JPG, PNG, GIF, WEBP &nbsp;·&nbsp; Maks 5 MB</p>
            </div>

            <div class="preview-wrap">
                <img id="previewImg" src="#" alt="Preview">
            </div>

            <button type="submit" class="btn-submit">Upload Sekarang</button>
        </form>
    </div>

    <a href="index.php" class="back-link">← Kembali ke Portfolio</a>

</div>

<script>
const data = {
    profil: [
        { id: <?= $profil['id'] ?>, label: "<?= htmlspecialchars($profil['nama']) ?>" }
    ],
    dokumentasi: [
        <?php foreach ($dokumentasi_list as $d): ?>
        { id: <?= $d['id'] ?>, label: "<?= htmlspecialchars($d['keterangan']) ?>" },
        <?php endforeach; ?>
    ],
    sertifikat: [
        <?php foreach ($sertifikat_list as $s): ?>
        { id: <?= $s['id'] ?>, label: "<?= htmlspecialchars($s['judul']) ?>" },
        <?php endforeach; ?>
    ]
};

function updateTarget(tabel) {
    const wrap   = document.getElementById('wrapId');
    const select = document.getElementById('selectId');
    select.innerHTML = '';

    if (!tabel || !data[tabel]) { wrap.style.display = 'none'; return; }

    data[tabel].forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.label;
        select.appendChild(opt);
    });

    wrap.style.display = 'block';
}

function previewFoto(input) {
    const img = document.getElementById('previewImg');
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        img.style.display = 'block';
    }
}
</script>

</body>
</html>
