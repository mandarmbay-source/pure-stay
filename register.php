<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar Akun User</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="glass-card">
        <a href="index.php" class="btn-back">⬅ Kembali</a>
        <h2>Daftar Akun Baru</h2>
        <form action="proses_register.php" method="POST">
            <div class="input-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required>
            </div>
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="input-group">
             <label>Nomor WhatsApp (Aktif)</label>
             <input type="number" name="no_hp" placeholder="Contoh: 08123456789" required>
            </div>

            <div class="input-group">
                <label>Jenis Penghuni</label>
                <select name="role">
                    <option value="user_air">Pelanggan Air Minum</option>
                    <option value="user_kos">Penghuni Kos</option>
                </select>
            </div>
            <button type="submit" class="btn-modern">Daftar Akun</button>
        </form>
    </div>
</body>
</html>