<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'AL-ABROR CBT')</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="cbt-theme">

    <div class="cbt-container">
        @yield('content')
    </div>

    <footer class="cbt-footer">
        <div>&copy; {{ date('Y') }} {{ $setting->nama_sekolah ?? 'SMK IT AL-ABROR' }}. All rights reserved.</div>
        <div>Dzikir &bull; Fikir &bull; Ikhtiar</div>
    </footer>

</body>

</html>