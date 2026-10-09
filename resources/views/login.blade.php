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
            --primary: #d32f2f;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e1e2f 0%, #2a2a44 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: #fff;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
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
            width: 200px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 40px;
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
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.2);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .btn-primary {
            background: linear-gradient(135deg, #d32f2f, #f44336);
            border: none;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(211, 47, 47, 0.4);
        }

        #lg_warn {
            min-height: 24px;
            margin-bottom: 1rem;
        }

        .footer-text {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-card mx-auto">
            <!-- Logo -->
            <img src="{{ asset('assets/images/logo.png') }}" alt="Peoples Bakers Logo" class="logo-img">

            <h2>Welcome Back</h2>
            <span class="splash-description">Please sign in to continue</span>

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <input type="email" class="form-control form-control-lg" placeholder="Email Address" id="email"
                        name="email" autocomplete="off" required>
                </div>

                <div class="mb-4">
                    <input type="password" class="form-control form-control-lg" placeholder="Password" id="password"
                        name="password" required>
                </div>

                <h4 class="text-danger text-center mb-3" id="lg_warn"></h4>

                <button type="submit" class="btn btn-primary btn-lg w-100" id="loginbtn">
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
</body>

</html>