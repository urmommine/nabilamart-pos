<!DOCTYPE html>
<html 
    x-data="{ darkMode: localStorage.getItem('pos-theme') !== 'light' }" 
    x-init="$watch('darkMode', val => localStorage.setItem('pos-theme', val ? 'dark' : 'light'))"
    :class="{ 'dark': darkMode }"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden</title>

    <!-- Prevent flash of wrong theme -->
    <script>
        if (localStorage.getItem('pos-theme') === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Google Fonts: Spline Sans -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Spline+Sans:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex flex-col items-center justify-center p-4 antialiased selection:bg-primary selection:text-white">

    <div class="max-w-md w-full text-center">
        <!-- Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-red-100 dark:bg-red-900/30 text-primary">
                <span class="material-symbols-outlined text-6xl">
                    lock
                </span>
            </div>
        </div>

        <!-- Error Code -->
        <h1 class="text-6xl font-bold mb-4 tracking-tight">403</h1>

        <!-- Title -->
        <h2 class="text-2xl font-semibold mb-4 text-slate-800 dark:text-white">
            Akses Ditolak
        </h2>

        <!-- Message -->
        <p class="text-text-muted-light dark:text-text-muted-dark text-lg mb-8 leading-relaxed px-4">
            @if($exception->getMessage())
                {{ $exception->getMessage() }}
            @else
                Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Silakan hubungi administrator Anda jika Anda yakin ini adalah kesalahan.
            @endif
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button onclick="history.back()" class="w-full sm:w-auto px-6 py-2.5 rounded-lg border border-border-light dark:border-border-dark text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-surface-dark transition-colors font-medium">
                Kembali
            </button>
            <a href="{{ url('/') }}" class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-primary text-white hover:bg-red-600 transition-colors shadow-lg shadow-red-500/20 font-medium">
                POS Dashboard
            </a>
        </div>
    </div>

</body>
</html>
