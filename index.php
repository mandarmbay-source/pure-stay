<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PureStay - Premium Management</title>
    <link rel="stylesheet" href="index.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="landing-page">
    <div class="blob-bg"></div>
    <div class="blob-bg secondary"></div>

    <nav class="navbar">
        <a href="#" class="logo-container">
            <div class="logo-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
            </div>
            <span>PureStay</span>
        </a>
    </nav>

    <main class="main-wrapper">
        <div class="glass-card welcome-box">
            <div class="hero-section">
                <div class="badge">Sistem Manajemen Modern</div>
                <h1>Lebih <span class="text-gradient"> Cerdas.</span></h1>
                <p class="description">Solusi manajemen kos dan distribusi air dalam satu platform terintegrasi. Efisien, transparan, dan sangat mudah digunakan.</p>
            </div>
            
            <div class="action-container">
                <div class="row-buttons">
                    <a href="login.php?role=admin" class="btn-modern btn-admin">
                        <div class="btn-icon">👨‍💼</div>
                        <div class="btn-text">
                            <span class="btn-label">Portal</span>
                            <span class="btn-title">Admin</span>
                        </div>
                    </a>
                    <a href="login.php?role=user" class="btn-modern btn-user">
                        <div class="btn-icon">👥</div>
                        <div class="btn-text">
                            <span class="btn-label">Portal</span>
                            <span class="btn-title">Penghuni & Pembeli Air</span>
                        </div>
                    </a>
                </div>
                
                <a href="register.php" class="btn-modern btn-register">
                    <span>Mulai Sekarang</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>