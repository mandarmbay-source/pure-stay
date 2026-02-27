<?php
// Pastikan session_start adalah baris paling pertama
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mengambil role dari URL (admin/user)
$role_target = isset($_GET['role']) ? $_GET['role'] : 'user';

// Keamanan: Pastikan role hanya 'admin' atau 'user'
if (!in_array($role_target, ['admin', 'user'])) {
    $role_target = 'user';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PureStay - Login <?php echo ucfirst($role_target); ?></title>
    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
</head>

<body class="landing-page">
    <div class="main-wrapper">
        <div class="glass-card">
          
            
            <h2 style="margin-top: 20px;">Masuk sebagai <?php echo $role_target == 'admin' ? 'Admin' : 'User'; ?></h2>
            <p class="subtitle">Silakan masukkan akun Anda</p>

          <form action="login_proses.php" method="POST">
    <input type="hidden" name="login_type" value="<?php echo $role_target; ?>"> 
    
    <div class="input-group">
        <label>Username <?php echo ucfirst($role_target); ?></label>
        <input type="text" name="username" placeholder="Masukkan Username..." required>
    </div>
    
    <div class="input-group">
        <label>Password <?php echo ucfirst($role_target); ?></label>
        <input type="password" name="password" placeholder="••••••••" required>
    </div>

 <?php if($role_target == 'user'): ?>
<div class="input-group">
    <label>Tipe Layanan</label>
    <details class="custom-accordion">
        <summary class="accordion-header">
            <span id="selected-label">Pilih Layanan Anda</span>
            <div class="chevron"></div>
        </summary>
        
        <div class="accordion-content">
            <div class="option-btn" onclick="selectRole('user_air', '💧 Pembeli Air', this)"><br>
                <span class="icon">💧</span>
                <span class="text">Pembeli Air</span>
                <input type="radio" name="specific_role" id="role_air" value="user_air" style="display:none;" required>
            </div>

            <div class="option-btn" onclick="selectRole('user_kos', '🏠 Penghuni Kos', this)"><br>
                <span class="icon">🏠</span>
                <span class="text">Penghuni Kos</span>
                <input type="radio" name="specific_role" id="role_kos" value="user_kos" style="display:none;" required>
            </div>
        </div>
    </details>
</div>
<?php endif; ?>

    <button type="submit" class="btn-modern btn-primary">
        LOGIN <?php echo strtoupper($role_target); ?>
    </button>
</form>

            <div style="margin-top: 25px;">
                <?php if($role_target == 'user'): ?>
                    <p style="font-size: 14px; color: #64748b;">
                        Belum punya akun? <a href="register.php" style="color: var(--beach-deep); font-weight: 800; text-decoration: none;">DAFTAR AKUN</a>
                    </p>
                <?php else: ?>
                    <p style="font-size: 12px; color: #94a3b8; font-weight: 600; letter-spacing: 1px;">
                        MODUL ADMINISTRATOR TERBATAS
                    </p><br>
                <?php endif; ?>
            </div><br>
              <a href="index.php" class="btn-back">⬅ KEMBALI KE BERANDA</a><br>
        </div>
    </div>
    <script>
function selectRole(val, label, element) {
    // 1. Ubah teks di header accordion
    document.getElementById('selected-label').innerHTML = label;
    
    // 2. Cari input radio di dalam div yang diklik, lalu centang
    const radio = element.querySelector('input[type="radio"]');
    radio.checked = true;
    
    // 3. Tambahkan warna biru (active) pada pilihan yang diklik
    document.querySelectorAll('.option-btn').forEach(btn => btn.classList.remove('active'));
    element.classList.add('active');
    
    // 4. Tutup "laci" otomatis setelah 200ms agar terasa smooth
    setTimeout(() => {
        document.querySelector('.custom-accordion').removeAttribute('open');
    }, 200);
}
</script>
</body>

</html>