<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BAFOLDANZ Digital')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @if (file_exists(public_path('build/app.css')))
        <link href="/build/app.css" rel="stylesheet">
    @endif
</head>
<body class="bg-bg text-text font-body antialiased min-h-screen overflow-x-hidden">
    <header class="fixed top-0 z-50 w-full border-b border-border/50 bg-bg/90 pb-[calc(env(safe-area-inset-top)+16px)] pt-[env(safe-area-inset-top)] backdrop-blur-xl" style="min-height:64px">
        <div class="mx-auto flex h-16 w-full max-w-lg items-center justify-between px-4 md:px-6">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="icon-lg material-symbols-outlined text-primary">theater_comedy</span>
                <span class="font-sans text-[15px] font-bold tracking-wide text-text">BAFOLDANZ</span>
            </a>
            <a href="{{ route('students.create') }}" class="rounded-full border border-secondary/40 bg-secondary/10 px-3.5 py-1 text-[11px] font-bold uppercase tracking-wider text-secondary">Inscribirse</a>
        </div>
    </header>

    <main class="relative w-full pt-[calc(80px+env(safe-area-inset-top))] md:pt-[80px]">
        <div class="mx-auto flex max-w-lg flex-col gap-6 px-4 py-6 pb-28 md:max-w-[1080px] md:py-8 md:px-6">
            @if (session('success'))
                <div class="rounded-xl border border-[#00e676]/30 bg-[#00e676]/10 p-4 text-sm text-success" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl border border-danger/30 bg-danger/10 p-4 text-sm text-danger" role="alert">
                    <strong>Revisa los datos:</strong>
                    <ul class="mt-1 list-disc space-y-0.5 pl-4 text-danger">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <nav class="tabs-fixed" aria-label="Navegación principal">
        <a class="tab @if (request()->is('/')) active @endif" href="{{ url('/') }}">
            <span class="material-symbols-outlined icon-md">space_dashboard</span>
            <span>Inicio</span>
        </a>
        <a class="tab @if (request()->is('students*')) active @endif" href="{{ url('/students') }}">
            <span class="material-symbols-outlined icon-md">groups</span>
            <span>Bailarines</span>
        </a>
        <a href="{{ url('/attendance') }}" class="tab @if (request()->is('attendance*')) active @endif">
            <span class="material-symbols-outlined icon-md">fact_check</span>
            <span>Asistencia</span>
        </a>
        <a href="{{ url('/payments') }}" class="tab @if (request()->is('payments*')) active @endif">
            <span class="material-symbols-outlined icon-md">payments</span>
            <span>Pagos</span>
        </a>
        <a href="{{ url('/finance') }}" class="tab @if (request()->is('finance*')) active @endif">
            <span class="material-symbols-outlined icon-md">account_balance_wallet</span>
            <span>Finanzas</span>
        </a>
    </nav>

    @if (session('success') || $errors->any())
        <script>setTimeout(() => { document.querySelectorAll('.toast').forEach(el => el.remove()); }, 2600);</script>
    @endif
</body>
</html>
