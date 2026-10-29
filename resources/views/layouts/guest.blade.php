<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('permit.municipality').' — Business Permit System')</title>
    <meta name="theme-color" content="#536978">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
</head>
<body class="bplo-portal bplo-guest">
    <header class="bplo-guest-header">
        <a href="{{ url('/login') }}" class="bplo-guest-brand">
            <span class="sidebar-brand-mark">BP</span>
            <span>{{ config('permit.municipality') }}<small>Business Permits and Licensing Office</small></span>
        </a>
        <span class="bplo-guest-secure"><span aria-hidden="true"></span> Secure staff portal</span>
    </header>

    <main class="page">
        <div class="container bplo-guest-content">
            <x-flash-messages />
            @yield('content')
        </div>
    </main>

    <footer class="bplo-guest-footer">
        &copy; {{ date('Y') }} {{ config('permit.municipality') }}, {{ config('permit.province') }}
        <span>Official BPLO personnel access</span>
    </footer>
</body>
</html>
