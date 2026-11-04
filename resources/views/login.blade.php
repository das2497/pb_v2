<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PEOPLES BAKERS | Login</title>
    <link rel="icon" href="{{ asset('assets/images/logo.png') }}">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts (Inter - Modern & Clean) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bakery-brown: #6b4226;
            --bakery-brown-dark: #3c2414;
            --bakery-brown-light: #8a5a37;
            --bakery-cream: #fdfaf6;
            --bakery-cream-soft: #f6efe4;
            --bakery-orange: #e07b1a;
            --bakery-orange-dark: #c2660f;
            --bakery-gold: #d4a017;
            --bakery-red: #c1443c;
            --bakery-text: #2a2017;
            --bakery-text-muted: #7a6d5f;
            --bakery-border: #e6ddd0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bakery-cream-soft);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 24px;
            color: var(--bakery-text);
        }

        .login-shell {
            display: grid;
            grid-template-columns: 1fr 1.15fr;
            max-width: 920px;
            width: 100%;
            min-height: 580px;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(42, 32, 23, 0.06), 0 24px 48px -12px rgba(42, 32, 23, 0.18);
        }

        /* ---------- Brand panel ---------- */
        .brand-panel {
            background: linear-gradient(160deg, var(--bakery-brown-dark) 0%, var(--bakery-brown) 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 48px 40px;
            color: #fff;
        }

        .brand-panel::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, var(--bakery-gold), var(--bakery-orange));
        }

        .brand-logo {
            width: 112px;
            height: 112px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
            margin-bottom: 24px;
        }

        .brand-title {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            margin: 0 0 10px;
        }

        .brand-subtitle {
            font-size: 0.95rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.75);
            max-width: 260px;
            margin: 0;
        }

        /* ---------- Form panel ---------- */
        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
        }

        .form-panel-inner {
            width: 100%;
            max-width: 340px;
        }

        .mobile-brand {
            display: none;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }

        .mobile-brand img {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            object-fit: cover;
        }

        .mobile-brand span {
            font-weight: 800;
            font-size: 1.05rem;
            color: var(--bakery-text);
            letter-spacing: 0.02em;
        }

        h2.form-heading {
            font-weight: 800;
            font-size: 1.6rem;
            margin-bottom: 0.3rem;
            color: var(--bakery-text);
        }

        .form-subtitle {
            display: block;
            margin-bottom: 1.75rem;
            color: var(--bakery-text-muted);
            font-size: 0.95rem;
        }

        .form-label {
            color: var(--bakery-text);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .form-control {
            background: var(--bakery-cream);
            border: 1.5px solid var(--bakery-border);
            color: var(--bakery-text);
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 0.97rem;
        }

        .form-control:focus {
            background: #fff;
            border-color: var(--bakery-orange);
            color: var(--bakery-text);
            box-shadow: 0 0 0 3px rgba(224, 123, 26, 0.15);
        }

        .form-control.is-invalid {
            border-color: var(--bakery-red);
        }

        .form-control::placeholder {
            color: #a89a89;
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--bakery-text-muted);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            padding: 4px 6px;
        }

        .password-toggle:hover {
            color: var(--bakery-orange-dark);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--bakery-orange), var(--bakery-gold));
            border: none;
            padding: 13px;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 10px;
            color: #fff;
            transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
        }

        .btn-login:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(224, 123, 26, 0.3);
            color: #fff;
        }

        .btn-login:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
        }

        .lg-warn {
            min-height: 20px;
            margin-bottom: 0.75rem;
            font-size: 0.88rem;
            text-align: left;
            color: var(--bakery-red);
            font-weight: 500;
        }

        .footer-text {
            text-align: center;
            margin-top: 28px;
            font-size: 0.82rem;
            color: var(--bakery-text-muted);
        }

        @media (max-width: 820px) {
            .login-shell {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .brand-panel {
                display: none;
            }

            .mobile-brand {
                display: flex;
            }

            .form-panel {
                padding: 40px 28px;
            }
        }

        @media (max-width: 400px) {
            body {
                padding: 12px;
            }

            .form-panel {
                padding: 32px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="login-shell">
        <div class="brand-panel">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Peoples Bakers Logo" class="brand-logo">
            <h1 class="brand-title">Peoples Bakers</h1>
            <p class="brand-subtitle">Order management portal for shops, representatives and admins.</p>
        </div>

        <div class="form-panel">
            <div class="form-panel-inner">
                <div class="mobile-brand">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Peoples Bakers Logo">
                    <span>Peoples Bakers</span>
                </div>

                <h2 class="form-heading">Welcome back</h2>
                <span class="form-subtitle">Sign in to your account to continue</span>

                @if ($errors->any())
                <p class="lg-warn" id="lg_warn">{{ $errors->first() }}</p>
                @else
                <p class="lg-warn" id="lg_warn"></p>
                @endif

                <form action="{{ route('login') }}" method="POST" id="loginForm">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                            placeholder="you@example.com" id="email" name="email" value="{{ old('email') }}"
                            autocomplete="username" autofocus required>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control form-control-lg" placeholder="Your password"
                                id="password" name="password" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" id="togglePassword">Show</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login btn-lg w-100" id="loginbtn">
                        Sign In
                    </button>
                </form>

                <div class="footer-text">
                    &copy; {{ date('Y') }} Peoples Bakers
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            var password = document.getElementById('password');
            var isHidden = password.type === 'password';
            password.type = isHidden ? 'text' : 'password';
            this.textContent = isHidden ? 'Hide' : 'Show';
        });

        document.getElementById('loginForm').addEventListener('submit', function () {
            var btn = document.getElementById('loginbtn');
            btn.disabled = true;
            btn.textContent = 'Signing in...';
        });
    </script>
</body>

</html>
