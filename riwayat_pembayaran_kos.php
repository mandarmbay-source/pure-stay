<?php
session_start();
require 'koneksi.php'; 

// Proteksi Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header('Location: index.php'); 
    exit; 
}

// --- FITUR HAPUS SATUAN ---
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM data_kos WHERE id = '$id_hapus'");
    header("Location: riwayat_pembayaran_kos.php?pesan=terhapus");
    exit;
}

// --- FITUR HAPUS SEMUA ---
if (isset($_POST['hapus_semua'])) {
    mysqli_query($koneksi, "DELETE FROM data_kos");
    header("Location: riwayat_pembayaran_kos.php?pesan=kosong");
    exit;
}

// Ambil parameter pencarian dan filter bulan
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$bulan_filter = isset($_GET['bulan']) ? $_GET['bulan'] : '';

// Query SQL dengan JOIN ke Users
$query_string = "SELECT data_kos.*, users.nama_lengkap 
                 FROM data_kos 
                 JOIN users ON data_kos.user_id = users.id 
                 WHERE 1=1";

if ($search) {
    $query_string .= " AND users.nama_lengkap LIKE '%$search%'";
}

if ($bulan_filter) {
    $query_string .= " AND MONTH(data_kos.tanggal_bayar) = '$bulan_filter'";
}

$query_string .= " ORDER BY data_kos.tanggal_bayar DESC";
$query = mysqli_query($koneksi, $query_string);

function formatTgl($tgl) {
    return date('d M Y | H:i', strtotime($tgl));
}

function tampilanStatusJatuhTempo($tanggal_bayar) {
    $tgl_jt = date('Y-m-d', strtotime($tanggal_bayar . " +30 days"));
    $jt_timestamp = strtotime($tgl_jt);
    $skrg_timestamp = time();
    
    if ($skrg_timestamp > $jt_timestamp) {
        return '<span style="color: #ef4444; font-weight: 800;">🚨 JATUH TEMPO</span>';
    }
    return '<span style="color: #10b981; font-weight: 800;">✓ AKTIF</span>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Pembayaran Kos - PureStay</title>
    <link rel="stylesheet" href="bulan_kos.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --primary: #4f8cf0; --bg: #f8fafc; --dark: #1e293b; --danger: #ef4444; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; padding: 15px; }
        .container { max-width: 1200px; margin: auto; }
        .glass-card { background: white; padding: 20px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
        
        /* Toolbar Responsif */
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .search-box { display: flex; gap: 10px; flex-wrap: wrap; width: 100%; max-width: 600px; }
        .search-box input { flex: 2; min-width: 150px; }
        .search-box select { flex: 1; min-width: 120px; }
        
        input, select { padding: 10px 15px; border-radius: 10px; border: 1px solid #e2e8f0; outline: none; font-size: 0.9rem; }
        .btn { padding: 10px 20px; border-radius: 10px; border: none; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: 0.2s; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-danger { background: var(--danger); color: white; font-size: 0.8rem; }
        .btn-outline-danger { border: 1.5px solid var(--danger); color: var(--danger); background: transparent; }
        
        /* Tabel Responsif */
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; } /* Memaksa scroll jika layar terlalu kecil */
        th { text-align: left; padding: 15px; background: #f1f5f9; color: #64748b; font-size: 0.75rem; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
        .img-bukti { width: 45px; height: 45px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 1px solid #ddd; }

        /* Header Mobile */
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }

        @media (max-width: 640px) {
            .header-top { flex-direction: column; }
            .header-top div:last-child { width: 100%; display: flex; gap: 5px; }
            .btn { flex: 1; justify-content: center; font-size: 0.8rem; padding: 10px 5px; }
            .search-box { flex-direction: column; }
            .search-box input, .search-box select, .search-box button { width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-top">
        <div>
            <a href="admin_kos.php" style="text-decoration: none; color: var(--primary); font-weight: 600;">← Kembali</a>
            <h2 style="margin: 10px 0 0 0; font-weight: 800;">🏠 Monitoring Pembayaran</h2>
        </div>
        <div style="display: flex; gap: 10px;">
            <form method="POST" onsubmit="return confirmHapusSemua(event)">
                <button type="submit" name="hapus_semua" class="btn btn-outline-danger">🗑️ Kosongkan</button>
            </form>
            <a href="riwayat_kos.php" class="btn" style="background: #1e293b; color: white;">⚙️ Kelola</a>
        </div>
    </div>

   <form class="toolbar" method="GET" id="filterForm">
    <div class="search-box" style="width: 100%; margin-bottom: 15px;">
        <input type="text" name="search" placeholder="Cari nama penghuni..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
        <button type="submit" class="btn btn-primary">Cari</button>
    </div>

    <div style="width: 100%;">
        <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">Filter Bulan:</label>
        <div class="month-chips-wrapper">
            <label>
                <input type="radio" name="bulan" value="" class="month-radio" onchange="this.form.submit()" <?php echo ($bulan_filter == '') ? 'checked' : ''; ?>>
                <span class="month-chip">Semua</span>
            </label>

            <?php
            $blns = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            foreach($blns as $val => $name) {
                $sel = ($bulan_filter == $val) ? 'checked' : '';
                echo "
                <label>
                    <input type='radio' name='bulan' value='$val' class='month-radio' onchange='this.form.submit()' $sel>
                    <span class='month-chip'>$name</span>
                </label>";
            }
            ?>
        </div>
    </div>
    
                </select>
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
            <a href="riwayat_pembayaran_kos.php" style="color: #64748b; font-size: 0.8rem;">Reset Filter</a>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Waktu Bayar</th>
                        <th>Nama Penghuni</th>
                        <th>No. Kamar</th>
                        <th>Bukti</th>
                        <th>Status Sewa</th>
                        <th>Total Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($query) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><b><?php echo formatTgl($row['tanggal_bayar']); ?></b></td>
                            <td><span style="font-weight: 700; color: var(--dark);"><?php echo $row['nama_lengkap']; ?></span></td>
                            <td><span class="btn" style="background:#f1f5f9; padding: 5px 10px; font-size:0.8rem; cursor: default;">Kamar <?php echo $row['no_kamar']; ?></span></td>
                            <td>
                                <?php if(!empty($row['bukti_bayar'])): ?>
                                    <img src="uploads/<?php echo $row['bukti_bayar']; ?>" class="img-bukti" onclick="viewImage('uploads/<?php echo $row['bukti_bayar']; ?>')">
                                <?php else: ?>
                                    <small style="color:#94a3b8;">Tidak ada</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                if(strtolower($row['status']) != 'lunas') {
                                    echo '<span style="color:#f59e0b; font-weight:800;">⏳ PROSES</span>';
                                } else {
                                    echo tampilanStatusJatuhTempo($row['tanggal_bayar']);
                                }
                                ?>
                            </td>
                            <td style="font-weight: 800; color: #10b981;">Rp <?php echo number_format($row['jumlah_bayar'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="javascript:void(0)" onclick="confirmHapus(<?php echo $row['id']; ?>)" class="btn btn-danger" style="padding: 5px 10px;">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 40px; color: #94a3b8;">Data riwayat tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function viewImage(path) {
        Swal.fire({ imageUrl: path, imageAlt: 'Bukti Bayar', confirmButtonColor: '#4f8cf0' });
    }

    function confirmHapus(id) {
        Swal.fire({
            title: 'Hapus riwayat ini?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'riwayat_pembayaran_kos.php?hapus=' + id;
            }
        })
    }

    function confirmHapusSemua(e) {
        e.preventDefault();
        Swal.fire({
            title: 'KOSONGKAN SEMUA DATA?',
            text: "Seluruh riwayat pembayaran akan dihapus permanen!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'YA, HAPUS SEMUA!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) { e.target.submit(); }
        })
    }

    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('pesan')) {
        Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data diperbarui', timer: 1500, showConfirmButton: false });
    }
</script>

</body>
</html>