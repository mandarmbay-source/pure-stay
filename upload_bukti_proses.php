<?php
session_start();
require 'koneksi.php';

// Proteksi: Hanya user yang sudah login yang bisa akses
if (!isset($_SESSION['id'])) { 
    header('Location: index.php');
    exit; 
}

$user_id = $_SESSION['id'];
$status_notif = ""; // Variabel untuk menampung status pesan

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti'])) {
    $no_kamar = mysqli_real_escape_string($koneksi, $_POST['no_kamar']);
    $metode = isset($_POST['metode_bayar']) ? mysqli_real_escape_string($koneksi, $_POST['metode_bayar']) : 'Transfer';
    $jumlah_bayar = 400000; 

    $nama_file = $_FILES['bukti']['name'];
    $tmp_file  = $_FILES['bukti']['tmp_name'];
    $ekstensi  = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    $nama_baru = "bukti_" . $user_id . "_" . time() . "." . $ekstensi;
    
    // Validasi file
    $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
    
    if (in_array($ekstensi, $allowed_ext)) {
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }

        if (move_uploaded_file($tmp_file, "uploads/" . $nama_baru)) {
            // Simpan ke tabel data_kos
            $query = "INSERT INTO data_kos (user_id, no_kamar, jumlah_bayar, metode_bayar, bukti_bayar, status, tanggal_bayar) 
                      VALUES ('$user_id', '$no_kamar', '$jumlah_bayar', '$metode', '$nama_baru', 'proses', NOW())";
            mysqli_query($koneksi, $query);
            
            // Update status di tabel users
            $query_user = "UPDATE users SET status_bayar = 'proses', bukti_bayar = '$nama_baru' WHERE id = '$user_id'";
            mysqli_query($koneksi, $query_user);
            
            $status_notif = "success";
        } else {
            $status_notif = "error_upload";
        }
    } else {
        $status_notif = "error_ext";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Bukti Pembayaran - PureStay</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #4f8cf0;
            --primary-soft: #e0ebff;
            --bg: #f8fafc;
            --dark: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .payment-card {
            background: white;
            width: 100%;
            max-width: 480px;
            padding: 40px;
            border-radius: 28px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--dark);
        }

        .header p {
            color: var(--text-muted);
            margin-top: 8px;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"], select {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #f1f5f9;
            border-radius: 14px;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
            background: #fcfdfe;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-soft);
            background: white;
        }

        /* Custom File Upload */
        .file-drop-area {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 25px;
            border: 2px dashed #e2e8f0;
            border-radius: 16px;
            cursor: pointer;
            transition: 0.3s;
            background: #f8fafc;
        }

        .file-drop-area:hover {
            border-color: var(--primary);
            background: var(--primary-soft);
        }

        .file-drop-area i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .file-drop-area span {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        input[type="file"] {
            position: absolute;
            left: 0; top: 0; opacity: 0;
            width: 100%; height: 100%;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 8px 20px rgba(79, 140, 240, 0.25);
        }

        .btn-submit:hover {
            background: #3b76d6;
            transform: translateY(-2px);
        }

        .btn-back {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-back:hover {
            color: var(--dark);
        }

        @media (max-width: 480px) {
            .payment-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="payment-card">
    <div class="header">
        <h2>Konfirmasi Bayar</h2>
        <p>Silahkan upload bukti transfer Anda</p>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Nomor Kamar</label>
            <input type="text" name="no_kamar" placeholder="Contoh: A01" required>
        </div>

        <div class="form-group">
            <label>Metode Pembayaran</label>
            <select name="metode_bayar">
                <option value="Transfer Bank">Transfer Bank (BCA/BNI/Mandiri)</option>
                <option value="E-Wallet">E-Wallet (Dana/OVO/Gopay)</option>
                <option value="Tunai">Tunai / Titip Pengelola</option>
            </select>
        </div>

        <div class="form-group">
            <label>Unggah Bukti (Gambar/PDF)</label>
            <div class="file-drop-area">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span id="file-name">Klik atau tarik file ke sini</span>
                <input type="file" name="bukti" accept="image/*,.pdf" required onchange="showFileName(this)">
            </div>
        </div>

        <button type="submit" class="btn-submit">
            Kirim Konfirmasi <i class="fa-solid fa-paper-plane" style="margin-left: 8px;"></i>
        </button>

        <a href="user_kos_dashboard.php" class="btn-back">
            <i class="fa-solid fa-arrow-left" style="margin-right: 5px;"></i> Batal dan Kembali
        </a>
    </form>
</div>

<script>
    // Fungsi untuk menampilkan nama file yang dipilih
    function showFileName(input) {
        const fileNameDisplay = document.getElementById('file-name');
        if (input.files && input.files.length > 0) {
            fileNameDisplay.innerText = input.files[0].name;
            fileNameDisplay.style.color = "#1e293b";
        }
    }

    // Bagian Logika Notifikasi Berdasarkan Variabel PHP
    <?php if ($status_notif == "success"): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Terkirim!',
            text: 'Bukti pembayaran Anda sedang diproses oleh admin.',
            confirmButtonColor: '#4f8cf0'
        }).then(() => {
            window.location.href = 'user_kos_dashboard.php';
        });
    <?php elseif ($status_notif == "error_upload"): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat mengunggah file.',
            confirmButtonColor: '#4f8cf0'
        });
    <?php elseif ($status_notif == "error_ext"): ?>
        Swal.fire({
            icon: 'warning',
            title: 'File Tidak Valid',
            text: 'Gunakan format JPG, PNG, atau PDF.',
            confirmButtonColor: '#4f8cf0'
        });
    <?php endif; ?>
</script>

</body>
</html>