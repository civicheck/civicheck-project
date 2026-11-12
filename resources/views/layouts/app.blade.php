<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CIVICHECK')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        body { background:#f4f7fa; }
        .brand { color:#123b5d; font-weight:800; letter-spacing:.5px; }
        .hero { background:#123b5d; color:white; border-radius:18px; }
        .card { border:0; border-radius:14px; }
        #map { height:360px; border-radius:14px; }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand brand" href="{{ route('reports.index') }}">CIVICHECK</a>

            <div class="d-flex align-items-center gap-3">
                <a class="btn btn-primary btn-sm" href="{{ route('reports.create') }}">+ Laporkan Kerusakan</a>

                @auth
                    <span class="text-secondary small">Halo, {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">Login Petugas</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container pb-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @stack('scripts')
</body>
</html>
