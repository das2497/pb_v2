<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin</title>
</head>
<body>

<h1>Super Admin</h1>

<form action="{{route('logout')}}" method="POST">
    @csrf
    <button type="submit">Logout</button>
</form>
    
</body>
</html>