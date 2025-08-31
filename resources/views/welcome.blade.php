<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - Pojok Billing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Sedikit style tambahan untuk body */
        body {
            height: 100vh;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center bg-light">

    <div class="text-center">
        <img src="{{ asset('img/logo.png') }}" alt="Pojok Billing Logo" class="mb-4" width="150">
        
        <h1 class="display-4 fw-bold">Pojok Billing</h1>
        
        <p class="lead">
            Aplikasi Manajemen ISP Modern Anda.
        </p>
        
        <div class="mt-4">
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Masuk</a>
            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg px-4 ms-2">Daftar</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>