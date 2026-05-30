<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Server - CBT MTsN 11 Majalengka</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-emerald: #10b981;
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
        }

        body {
            background-color: var(--bg-dark);
            color: white;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px 15px;
        }

        .status-container {
            max-width: 480px;
            width: 100%;
            text-align: center;
            background: var(--card-bg);
            padding: 32px 24px;
            border-radius: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            gap: 13px;
        }

        .maintenance-img {
            max-width: 212px;
            margin: 0 auto 5px;
            filter: drop-shadow(0 12px 24px rgba(0, 0, 0, 0.4));
        }

        h2 {
            font-weight: 800;
            color: var(--primary-emerald);
            margin-bottom: 2px;
            font-size: 1.75rem;
            letter-spacing: -0.5px;
        }

        p {
            color: #f1f5f9;
            font-size: 1.08rem;
            margin-bottom: 12px;
            line-height: 1.4;
            font-weight: 500;
        }

        .countdown-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 8px;
        }

        .countdown-item {
            background: rgba(16, 185, 129, 0.08);
            padding: 11px 2px;
            border-radius: 18px;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }

        .countdown-value {
            display: block;
            font-size: 1.58rem;
            font-weight: 800;
            color: var(--primary-emerald);
            line-height: 1.1;
        }

        .countdown-label {
            font-size: 0.69rem;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 600;
        }

        .promo-text {
            font-size: 0.94rem;
            color: #cbd5e1;
            margin-bottom: 5px;
            padding: 0 5px;
            line-height: 1.4;
        }

        .btn-youtube {
            background: #ff0000;
            color: white !important;
            border: none;
            padding: 12px 29px;
            border-radius: 50px;
            font-weight: 800;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(255, 0, 0, 0.2);
        }

        .btn-youtube:hover {
            transform: scale(1.05);
        }

        .social-group {
            margin-top: 10px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-link {
            width: 43px;
            height: 43px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.28rem;
            transition: 0.3s;
            text-decoration: none;
        }

        .social-link.fb:hover {
            background: #1877f2;
            border-color: #1877f2;
            color: white;
        }

        .social-link.ig:hover {
            background: #e4405f;
            border-color: #e4405f;
            color: white;
        }

        .dynamic-wave {
            width: 115px;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--primary-emerald), transparent);
            margin: 5px auto;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }

        .dynamic-wave::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
            animation: wave-flow 2s infinite linear;
        }

        @keyframes wave-flow {
            0%   { left: -100%; }
            100% { left: 100%; }
        }

        .footer a {
            color: var(--primary-emerald);
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
        }

        .footer a:hover {
            text-shadow: 0 0 10px var(--primary-emerald);
            color: white;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.85rem;
            color: #64748b;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 15px;
        }
    </style>
</head>
<body>

    <div class="status-container">
        <div>
            <h2>{{ $statusMsg }}</h2>
            <img src="/assets/maintenance.png" alt="Maintenance" class="maintenance-img">
            <p>{{ $statusDetail }}</p>
        </div>

        @if($targetTime)
            <div class="countdown-grid" id="countdown" data-target="{{ $targetTime }}">
                <div class="countdown-item"><span class="countdown-value" id="days">00</span><span class="countdown-label">Hari</span></div>
                <div class="countdown-item"><span class="countdown-value" id="hours">00</span><span class="countdown-label">Jam</span></div>
                <div class="countdown-item"><span class="countdown-value" id="minutes">00</span><span class="countdown-label">Menit</span></div>
                <div class="countdown-item"><span class="countdown-value" id="seconds">00</span><span class="countdown-label">Detik</span></div>
            </div>
            <div class="dynamic-wave"></div>
        @endif

        <div class="promo-text">
            Sambil nunggu, yuk <strong>subscribe</strong> YouTube MTsN 11 Majalengka dan eksplor media sosial lainnya:
        </div>

        <div>
            <a href="https://www.youtube.com/@mtsn11majalengka?sub_confirmation=1" class="btn-youtube" target="_blank">
                <i class="bi bi-youtube"></i> SUBSCRIBE
            </a>
            <div class="social-group">
                <a href="https://www.facebook.com/p/Mtsnsebelas-Majalengka-100077085986063" class="social-link fb" target="_blank" title="Facebook"><i class="bi bi-facebook"></i></a>
                <a href="https://www.instagram.com/mtsn11majalengka" class="social-link ig" target="_blank" title="Instagram"><i class="bi bi-instagram"></i></a>
            </div>
        </div>

        <div class="footer">
            <p class="mb-1 text-white-50">v{{ \App\Models\Setting::getValue('latest_version', '4.2.4') }} Final Release</p>
            <p class="mb-1">&copy; 2025 MTsN 11 Majalengka</p>
            <p class="mb-0">Developed by <a href="https://www.instagram.com/atadityas_13" target="_blank">A.T. Aditya</a></p>
        </div>
    </div>

    <script>
        const targetStr = document.getElementById('countdown')?.getAttribute('data-target');
        if (targetStr) {
            const targetDate = new Date(targetStr.replace(/-/g, "/")).getTime();

            const timer = setInterval(function () {
                const now      = new Date().getTime();
                const distance = targetDate - now;

                if (distance < 0) {
                    clearInterval(timer);
                    location.reload();
                    return;
                }

                const d = Math.floor(distance / (1000 * 60 * 60 * 24));
                const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById("days").innerText    = d.toString().padStart(2, '0');
                document.getElementById("hours").innerText   = h.toString().padStart(2, '0');
                document.getElementById("minutes").innerText = m.toString().padStart(2, '0');
                document.getElementById("seconds").innerText = s.toString().padStart(2, '0');
            }, 1000);
        }
    </script>

</body>
</html>
