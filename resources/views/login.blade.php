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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bakery-brown: #6b4226;
            --bakery-brown-dark: #4a2c17;
            --bakery-brown-light: #8a5a37;
            --bakery-cream: #fff8f0;
            --bakery-cream-soft: #fdf1e2;
            --bakery-orange: #e07b1a;
            --bakery-orange-dark: #c2660f;
            --bakery-gold: #d4a017;
            --bakery-red: #c1443c;
            --bakery-text: #4a2c17;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bakery-brown-dark) 0%, var(--bakery-brown) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 16px;
            color: #fff;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(212, 160, 23, 0.35);
            border-top: 4px solid var(--bakery-gold);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            max-width: 420px;
            width: 100%;
            padding: 2.5rem;
            transition: all 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
        }

        .logo-img {
            width: 160px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        h2 {
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.5rem;
            color: #fff;
        }

        .splash-description {
            text-align: center;
            display: block;
            margin-bottom: 2rem;
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.05rem;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 1.05rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--bakery-gold);
            color: #fff;
            box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.25);
        }

        .form-control.is-invalid {
            border-color: var(--bakery-red);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            cursor: pointer;
            padding: 4px 6px;
        }

        .password-toggle:hover {
            color: #fff;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--bakery-orange), var(--bakery-gold));
            border: none;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            color: #fff;
            transition: all 0.3s ease;
        }

        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(224, 123, 26, 0.4);
            color: #fff;
        }

        .btn-login:disabled {
            opacity: 0.75;
        }

        .lg-warn {
            min-height: 24px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            text-align: center;
            color: #ffb4ae;
        }

        .footer-text {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.5);
        }

        @media (max-width: 400px) {
            .login-card {
                padding: 1.75rem 1.25rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-card mx-auto">
            <!-- Logo -->
            <img src="{{ asset('assets/images/logo.png') }}" alt="Peoples Bakers Logo" class="logo-img">

            <h2>Welcome Back...</h2>
            <span class="splash-description">Please sign in to continue</span>

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
