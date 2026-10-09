<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>

<body>

    <div class="container">
        <div class="row">
            <div class="col-12"></div>
            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div>
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="form-control">
                </div>

                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required class="form-control">
                </div>

                <div>
                    <label for="role">Role:</label>
                    <select id="role" name="role" required class="form-control">
                        <option value="s_admin">Super Admin</option>
                        <option value="o_admin">Order Admin</option>
                        <option value="rep">Rep</option>
                        <option value="shop">Shop</option>
                    </select>
                </div>

                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required class="form-control">
                </div>

                <div>
                    <label for="password_confirmation">Confirm Password:</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required class="form-control">
                </div>

                <div>
                    <input type="submit" value="Register" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>

</body>

</html>