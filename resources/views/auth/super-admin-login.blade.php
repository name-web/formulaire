<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Connexion Super Admin — DOMINIUM TECH') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --yellow: #FFB900; --yellow-light: #FFC72C; --bg-dark: #071426;
            --text-muted: rgba(255,255,255,0.48); --navy: #071426; --navy-2: #0b1b31;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif; color: #fff;
            background: radial-gradient(circle at 82% 45%, rgba(25, 59, 101, .35), transparent 38%), radial-gradient(circle at 15% 20%, rgba(255,185,0,.04), transparent 30%), linear-gradient(135deg, #071426 0%, #0b1b31 52%, #102b4c 100%);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
        }
        .bg-effects { position: fixed; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
        .orb { position: absolute; border-radius: 50%; filter: blur(90px); }
        .orb-1 { width: 400px; height: 400px; top: -100px; right: -80px; background: radial-gradient(circle, rgba(255,185,0,0.2) 0%, transparent 70%); }
        .orb-2 { width: 350px; height: 350px; bottom: -120px; left: 10%; background: radial-gradient(circle, rgba(255,185,0,0.15) 0%, transparent 70%); }

        .login-wrapper { position: relative; z-index: 1; width: 100%; max-width: 420px; padding: 20px; }
        .login-card {
            background: linear-gradient(145deg, rgba(18,44,77,0.85), rgba(7,20,38,0.95));
            border: 1px solid rgba(255,255,255,0.08); border-radius: 24px;
            padding: 48px 40px; backdrop-filter: blur(20px);
            box-shadow: 0 35px 80px rgba(0,0,0,0.4);
        }
        .login-brand { text-align: center; margin-bottom: 36px; }
        .login-brand img { height: 60px; width: auto; filter: drop-shadow(0 0 12px rgba(255,185,0,0.25)); }
        .login-brand h1 { font-family: 'Montserrat', sans-serif; font-size: 16px; font-weight: 800; color: rgba(255,255,255,0.7); letter-spacing: 1px; margin-top: 10px; text-transform: uppercase; }
        .login-brand p { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
        .login-brand img { max-width: 100%; height: auto; }

        @media (max-width: 420px) {
            .login-card { padding: 32px 20px; }
            .login-brand img { height: 48px; }
        }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 6px; font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.55); text-transform: uppercase; letter-spacing: 0.06em; }
        .input-wrap { position: relative; }
        .input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 13px; pointer-events: none; }
        .form-input {
            width: 100%; height: 48px; padding: 0 16px 0 42px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.09);
            border-radius: 12px; color: white; font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus { outline: none; border-color: var(--yellow); box-shadow: 0 0 0 3px rgba(255,185,0,0.12); }
        .form-input::placeholder { color: var(--text-muted); }

        .remember-row { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; }
        .remember-row input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--yellow); border-radius: 4px; }
        .remember-row label { font-size: 13px; color: rgba(255,255,255,0.55); cursor: pointer; }

        .btn-login {
            width: 100%; height: 48px; border: none; border-radius: 12px;
            background: var(--yellow); color: var(--navy); font-size: 14px; font-weight: 800;
            cursor: pointer; transition: all 0.25s ease; position: relative; overflow: hidden;
        }
        .btn-login:hover { background: var(--yellow-light); transform: translateY(-2px); box-shadow: 0 12px 30px rgba(255,185,0,0.25); }
        .btn-login:active { transform: translateY(0) scale(0.98); }

        .error-box { padding: 12px 16px; border-radius: 12px; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.15); margin-bottom: 20px; }
        .error-box .error-msg { font-size: 13px; color: #fca5a5; display: flex; align-items: center; gap: 8px; }
        .error-box .error-msg i { color: #f87171; }

        .forgot-link { text-align: center; margin-top: 20px; }
        .forgot-link a { font-size: 12px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s; }
        .forgot-link a:hover { color: var(--yellow-light); }
    </style>
</head>
<body>
    <div class="bg-effects">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-brand">
                <img src="{{ asset('IMAGES/Logo dominium.png') }}" alt="DOMINIUM">
                <h1>DOMINIUM TECH</h1>
                <p>{{ __('Espace Super Administrateur') }}</p>
            </div>

            @if(session('error'))
                <div class="error-box">
                    <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="error-box">
                    @foreach($errors->all() as $error)
                        <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('super_admin.login') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">{{ __('Email') }}</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" class="form-input" placeholder="superadmin@dominium.tech" value="{{ old('email') }}" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('Mot de passe') }}</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" class="form-input" placeholder="••••••••" required autocomplete="current-password">
                    </div>
                </div>

                <div class="remember-row">
                    <input type="checkbox" id="remember" name="remember" value="1" checked>
                    <label for="remember">{{ __('Rester connecté') }}</label>
                </div>

                <button type="submit" class="btn-login">
                    {{ __('Se connecter') }} <i class="fa-solid fa-arrow-right" style="margin-left:6px;"></i>
                </button>
            </form>

            <div class="forgot-link">
                <a href="{{ route('password.request') }}">{{ __('Mot de passe oublié ?') }}</a>
            </div>
        </div>
    </div>
</body>
</html>
