<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - Gunakan CBT App</title>
    
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
            --danger: #ef4444;
            --danger-glow: rgba(239, 68, 68, 0.12);
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

        /* Ambient background glow */
        body::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--danger-glow) 0%, transparent 70%);
            top: 15%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
        }

        .blocked-container {
            width: 100%;
            max-width: 440px;
            z-index: 10;
            position: relative;
        }

        .blocked-card {
            background-color: rgba(15, 22, 36, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 40px 28px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .blocked-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--danger);
        }

        .warning-badge {
            display: inline-flex;
            width: 68px;
            height: 68px;
            background-color: var(--danger-glow);
            border: 1.5px solid rgba(239, 68, 68, 0.3);
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            box-shadow: 0 0 25px rgba(239, 68, 68, 0.15);
            animation: pulseWarning 2s infinite ease-in-out;
        }

        @keyframes pulseWarning {
            0%, 100% { transform: scale(1); box-shadow: 0 0 25px rgba(239, 68, 68, 0.15); }
            50% { transform: scale(1.04); box-shadow: 0 0 35px rgba(239, 68, 68, 0.25); }
        }

        .warning-badge i {
            font-size: 32px;
            color: var(--danger);
        }

        .blocked-title {
            font-size: 20px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 12px;
        }

        .blocked-desc {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .btn-download {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            background-color: var(--danger);
            color: #ffffff;
            font-weight: 700;
            padding: 16px;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-size: 14px;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
        }

        .btn-download:hover {
            background-color: #f87171;
            box-shadow: 0 0 25px rgba(239, 68, 68, 0.45);
            transform: translateY(-1px);
        }

        .note-box {
            background-color: rgba(7, 11, 19, 0.5);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-top: 24px;
            font-size: 12.5px;
            color: var(--text-secondary);
            text-align: left;
            line-height: 1.5;
        }

        .note-box h4 {
            color: #fff;
            margin-bottom: 6px;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <div class="blocked-container">
        <div class="blocked-card">
            
            <div class="warning-badge">
                <i class="bi bi-shield-slash-fill"></i>
            </div>
            
            <h1 class="blocked-title">Lingkungan Tidak Aman!</h1>
            <p class="blocked-desc">
                Maaf, Anda tidak dapat mengakses halaman ujian dari browser standar. Halaman ini hanya boleh diakses melalui <strong>Aplikasi Resmi CBT App MTsN 11 Majalengka</strong> untuk menjaga integritas ujian.
            </p>

            <a href="{{ $downloadLink }}" class="btn-download">
                <i class="bi bi-download"></i>
                <span>Unduh CBT App Resmi</span>
            </a>

            <div class="note-box">
                <h4>Mengapa Saya Melihat Halaman Ini?</h4>
                <p>
                    Aplikasi CBT App mematikan multi-window, mencegah screen capture, menonaktifkan tombol navigasi melayang, dan mengunci browser dari kecurangan. Silakan instal aplikasi lalu buka tautan ujian kembali.
                </p>
            </div>

        </div>
    </div>

</body>
</html>
