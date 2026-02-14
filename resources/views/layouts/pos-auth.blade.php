<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'POS') }} - Login</title>



    <!-- Google Fonts: Spline Sans -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Spline+Sans:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Hide Material Symbols text until font loads */
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-size: 24px;
            visibility: hidden;
        }

        /* Show icons when font is loaded */
        .fonts-loaded .material-symbols-outlined {
            visibility: visible;
        }
    </style>

    <script>
        // Detect when Material Symbols font is loaded
        document.fonts.ready.then(function () {
            document.documentElement.classList.add('fonts-loaded');
        });

        // Fallback: show icons after 1 second even if font detection fails
        setTimeout(function () {
            document.documentElement.classList.add('fonts-loaded');
        }, 1000);
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-slate-900 dark:text-white antialiased">

    {{ $slot }}

    @livewireScripts
</body>

</html>