<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ujian CBT MTsN 11 Majalengka</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --bg-base: #070b13;
            --bg-surface: #0f1624;
            --border-color: #1e293b;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --primary: #10b981;
            --primary-hover: #34d399;
            --primary-glow: rgba(16, 185, 129, 0.15);
            --radius-md: 12px;
            --radius-lg: 16px;
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            --font-main: 'Plus Jakarta Sans', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            overflow-x: hidden;
            position: relative;
        }

        /* Abstract glowing particles in background */
        body::before {
            content: '';
            position: absolute;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            top: 5%;
            left: 10%;
            z-index: 1;
        }

        body::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%);
            bottom: 5%;
            right: 10%;
            z-index: 1;
        }

        .landing-container {
            width: 100%;
            max-width: 440px;
            z-index: 10;
            position: relative;
        }

        .landing-card {
            background-color: rgba(15, 22, 36, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 32px 24px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .school-logo {
            display: inline-flex;
            width: 64px;
            height: 64px;
            background-color: var(--primary-glow);
            border: 1.5px solid rgba(16, 185, 129, 0.3);
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        .school-logo i {
            font-size: 30px;
            color: var(--primary);
            text-shadow: 0 0 10px var(--primary);
        }

        .school-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #ffffff 0%, var(--text-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 4px;
        }

        .school-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 24px;
        }

        /* Announcement alert */
        .alert-update {
            background-color: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: #f59e0b;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            font-size: 12.5px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
            line-height: 1.4;
        }

        .alert-update i {
            font-size: 20px;
            flex-shrink: 0;
        }

        .btn-exam {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            background-color: rgba(7, 11, 19, 0.6);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 18px 20px;
            border-radius: var(--radius-md);
            cursor: pointer;
            margin-bottom: 16px;
            transition: var(--transition);
            text-decoration: none;
            text-align: left;
        }

        .btn-exam:hover {
            border-color: var(--primary);
            background-color: var(--primary-glow);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.1);
            transform: translateY(-1px);
        }

        .btn-exam.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            border-color: var(--border-color);
            background-color: rgba(7, 11, 19, 0.2);
            transform: none;
            pointer-events: none;
        }

        .exam-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .exam-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-sm);
            background-color: rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .btn-exam:hover .exam-icon {
            background-color: var(--primary);
            color: #000;
        }

        .exam-details {
            display: flex;
            flex-direction: column;
        }

        .exam-name {
            font-weight: 700;
            font-size: 15px;
            color: #fff;
        }

        .exam-status {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .btn-exam:hover .exam-status {
            color: var(--primary);
        }

        .btn-exam .arrow-icon {
            font-size: 18px;
            color: var(--text-muted);
            transition: var(--transition);
        }

        .btn-exam:hover .arrow-icon {
            color: var(--primary);
            transform: translateX(4px);
        }

        .footer-note {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
    </style>
</head>
<body>

    <div class="landing-container">
        <div class="landing-card">
            
            <div class="school-logo">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            
            <h1 class="school-title">PORTAL CBT ONLINE</h1>
            <p class="school-subtitle">MTs Negeri 11 Majalengka</p>

            <!-- Show Version warning alert if outdated app is used -->
            @if($showAlert)
                <div class="alert-update">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{!! $alertMessage !!}</div>
                </div>
            @endif

            <!-- Button: Asesmen Sumatif -->
            @if($sumatifActive)
                <a href="https://cbt-sumatif.mtsn11majalengka.sch.id" class="btn-exam">
                    <div class="exam-info">
                        <div class="exam-icon">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <div class="exam-details">
                            <span class="exam-name">Asesmen Sumatif</span>
                            <span class="exam-status">Sesi ujian aktif &bull; Masuk sekarang</span>
                        </div>
                    </div>
                    <i class="bi bi-arrow-right arrow-icon"></i>
                </a>
            @else
                <div class="btn-exam disabled">
                    <div class="exam-info">
                        <div class="exam-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="exam-details">
                            <span class="exam-name">Asesmen Sumatif</span>
                            <span class="exam-status" style="color: var(--danger);">Tidak ada sesi aktif saat ini</span>
                        </div>
                    </div>
                    <i class="bi bi-lock-fill arrow-icon"></i>
                </div>
            @endif

            <!-- Button: Asesmen Madrasah -->
            @if($madrasahActive)
                <a href="https://cbt-madrasah.mtsn11majalengka.sch.id" class="btn-exam">
                    <div class="exam-info">
                        <div class="exam-icon">
                            <i class="bi bi-file-earmark-lock2-fill"></i>
                        </div>
                        <div class="exam-details">
                            <span class="exam-name">Asesmen Madrasah</span>
                            <span class="exam-status">Sesi ujian aktif &bull; Masuk sekarang</span>
                        </div>
                    </div>
                    <i class="bi bi-arrow-right arrow-icon"></i>
                </a>
            @else
                <div class="btn-exam disabled">
                    <div class="exam-info">
                        <div class="exam-icon">
                            <i class="bi bi-file-earmark-lock2"></i>
                        </div>
                        <div class="exam-details">
                            <span class="exam-name">Asesmen Madrasah</span>
                            <span class="exam-status" style="color: var(--danger);">Tidak ada sesi aktif saat ini</span>
                        </div>
                    </div>
                    <i class="bi bi-lock-fill arrow-icon"></i>
                </div>
            @endif

            <div class="footer-note">
                <i class="bi bi-shield-check"></i>
                <span>Lingkungan ujian diawasi & terproteksi CBT-App v4.2.3</span>
            </div>

        </div>
    </div>

</body>
</html>
