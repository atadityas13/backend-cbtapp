<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Panel CBT MTsN 11 Majalengka</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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
            --danger: #ef4444;
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
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }

        /* Abstract glowing particles in background */
        body::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            top: 10%;
            right: 15%;
            z-index: 1;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%);
            bottom: 10%;
            left: 10%;
            z-index: 1;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            z-index: 10;
            position: relative;
        }

        .login-card {
            background-color: rgba(15, 22, 36, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 40px 32px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5), 0 0 40px 0 rgba(16, 185, 129, 0.03);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), #3b82f6);
        }

        .brand-section {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-logo {
            display: inline-flex;
            width: 56px;
            height: 56px;
            background-color: var(--primary-glow);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.15);
        }

        .brand-logo i {
            font-size: 26px;
            color: var(--primary);
            text-shadow: 0 0 10px var(--primary);
        }

        .brand-title {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #ffffff 0%, var(--text-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 6px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 16px;
            transition: var(--transition);
        }

        .form-control {
            width: 100%;
            background-color: var(--bg-base);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 14px 16px 14px 46px;
            border-radius: var(--radius-md);
            font-family: var(--font-main);
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .form-control:focus + i {
            color: var(--primary);
        }

        .options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            font-size: 13px;
        }

        .remember-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: var(--text-secondary);
        }

        .remember-checkbox input {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
        }

        .btn-submit {
            width: 100%;
            background-color: var(--primary);
            color: #000;
            font-family: var(--font-main);
            font-size: 14px;
            font-weight: 700;
            padding: 14px;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Error/Alert box */
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--danger);
            padding: 12px 16px;
            border-radius: var(--radius-md);
            font-size: 13px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            
            <div class="brand-section">
                <div class="brand-logo">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h1 class="brand-title">CBT PANEL</h1>
                <p class="brand-subtitle">Silakan masuk menggunakan akun Proktor atau Admin Anda.</p>
            </div>

            <!-- Display Validation Errors -->
            @if ($errors->any())
                <div class="alert-error">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                    <div>Username atau password salah.</div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="username" class="form-label">Username atau Email</label>
                    <div class="input-wrapper">
                        <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username/email" required autocomplete="username" autofocus>
                        <i class="bi bi-person-fill"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required autocomplete="current-password">
                        <i class="bi bi-key-fill"></i>
                    </div>
                </div>

                <div class="options-row">
                    <label class="remember-checkbox">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    <span>Masuk</span>
                    <i class="bi bi-arrow-right-short"></i>
                </button>
            </form>
            
        </div>
    </div>

</body>
</html>
