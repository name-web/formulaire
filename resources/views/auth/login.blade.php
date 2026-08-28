<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion — DOMINIUM TECH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --blue: #3b82f6; --blue-light: #60a5fa; --bg-dark: #020617;
            --text-muted: rgba(255,255,255,0.48);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif; color: #fff;
            background: linear-gradient(135deg, #020617 0%, #0c1e3a 40%, #0f2847 70%, #0a1628 100%);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
        }
        .bg-effects { position: fixed; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
        .orb { position: absolute; border-radius: 50%; filter: blur(90px); }
        .orb-1 { width: 400px; height: 400px; top: -100px; right: -80px; background: radial-gradient(circle, rgba(59,130,246,0.2) 0%, transparent 70%); }
        .orb-2 { width: 350px; height: 350px; bottom: -120px; left: 10%; background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%); }

        .login-wrapper { position: relative; z-index: 1; width: 100%; max-width: 420px; padding: 20px; }
        .login-card {
            background: linear-gradient(145deg, rgba(15,40,71,0.85), rgba(5,18,36,0.95));
            border: 1px solid rgba(255,255,255,0.08); border-radius: 24px;
            padding: 48px 40px; backdrop-filter: blur(20px);
            box-shadow: 0 35px 80px rgba(0,0,0,0.4);
        }
        .login-brand { text-align: center; margin-bottom: 36px; }
        .login-brand img { height: 60px; width: auto; filter: drop-shadow(0 0 12px rgba(59,130,246,0.25)); }
        .login-brand h1 { font-size: 16px; font-weight: 800; color: rgba(255,255,255,0.7); letter-spacing: 1px; margin-top: 10px; text-transform: uppercase; }
        .login-brand p { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

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
        .form-input:focus { outline: none; border-color: var(--blue); box-shadow: 0 0 0 3px rgba(59,130,246,0.12); }
        .form-input::placeholder { color: var(--text-muted); }

        .remember-row { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; }
        .remember-row input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--blue); border-radius: 4px; }
        .remember-row label { font-size: 13px; color: rgba(255,255,255,0.55); cursor: pointer; }

        .btn-login {
            width: 100%; height: 48px; border: none; border-radius: 12px;
            background: var(--blue); color: white; font-size: 14px; font-weight: 800;
            cursor: pointer; transition: all 0.25s ease; position: relative; overflow: hidden;
        }
        .btn-login:hover { background: var(--blue-light); transform: translateY(-2px); box-shadow: 0 12px 30px rgba(59,130,246,0.25); }
        .btn-login:active { transform: translateY(0) scale(0.98); }

        .error-box { padding: 12px 16px; border-radius: 12px; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.15); margin-bottom: 20px; }
        .error-box .error-msg { font-size: 13px; color: #fca5a5; display: flex; align-items: center; gap: 8px; }
        .error-box .error-msg i { color: #f87171; }

        .forgot-link { text-align: center; margin-top: 20px; }
        .forgot-link a { font-size: 12px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s; }
        .forgot-link a:hover { color: var(--blue-light); }
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
                <p>Espace administrateur</p>
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

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" class="form-input" placeholder="admin@dominium.tech" value="{{ old('email') }}" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" class="form-input" placeholder="••••••••" required autocomplete="current-password">
                    </div>
                </div>

                <div class="remember-row">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>

                <button type="submit" class="btn-login">
                    Se connecter <i class="fa-solid fa-arrow-right" style="margin-left:6px;"></i>
                </button>
            </form>

            <div class="forgot-link">
                <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
            </div>
        </div>
    </div>
</body>
</html>
