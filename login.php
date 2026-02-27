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
        <label>Login Sebagai Tipe:</label>
        <select name="specific_role" required>
            <option value="" disabled selected>-- Pilih Tipe Akun Anda --</option>
            <option value="user_air">Pelanggan Air</option>
            <option value="user_kos">Penghuni Kos</option>
        </select>
        <small class="helper-text">
            *Pilihan harus sesuai dengan saat Anda mendaftar.
        </small>
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
    
</body>

</html>