<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun User</title>
    <link rel="stylesheet" href="register.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="glass-card">
        <a href="index.php" class="btn-back">⬅ Kembali</a>
        <h2>Daftar Akun Baru</h2>
        
        <form action="proses_register.php" method="POST">
            <div class="input-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" placeholder="Nama sesuai KTP" required>
            </div>
            
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Buat username unik" required>
            </div>
            
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

                <div class="input-group">
            <label>Nomor WhatsApp (Aktif)</label>
            <input 
                type="tel" 
                name="no_hp" 
                id="no_hp"
                placeholder="Contoh: 628123456789" 
                required
                pattern="^(62|0)8[1-9][0-9]{7,11}$"
                title="Nomor harus diawali 62 atau 08 dan berjumlah 10-14 digit.">
            <small class="helper-text">*Gunakan format 628xxx atau 08xxx</small>
        </div>

            <div class="input-group">
                <label>Jenis Layanan</label>
                <details class="custom-accordion">
                    <summary class="accordion-header">
                        <span id="selected-label">Pilih Jenis Layanan</span>
                        <div class="chevron"></div>
                    </summary>
                    <div class="accordion-content">
                        <div class="option-btn" onclick="selectRole('user_air', '💧 Pelanggan Air', this)"><br>
                            <span class="icon">💧</span>
                            <span class="text">Pelanggan Air</span>
                            <input type="radio" name="role" id="role_air" value="user_air" required style="display:none;">
                        </div>
                        <div class="option-btn" onclick="selectRole('user_kos', '🏠 Penghuni Kos', this)"><br>
                            <span class="icon">🏠</span>
                            <span class="text">Penghuni Kos</span>
                            <input type="radio" name="role" id="role_kos" value="user_kos" required style="display:none;">
                        </div>
                    </div>
                </details>
            </div>

            <button type="submit" class="btn-modern">Daftar Akun Sekarang</button>
        </form>
    </div>

    <script>
        function selectRole(val, label, element) {
            document.getElementById('selected-label').innerHTML = label;
            const radio = element.querySelector('input[type="radio"]');
            radio.checked = true;
            
            document.querySelectorAll('.option-btn').forEach(btn => btn.classList.remove('active'));
            element.classList.add('active');
            
            setTimeout(() => {
                document.querySelector('.custom-accordion').removeAttribute('open');
            }, 200);
        }
    </script>
</body>
</html>