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
<body class="has-sidebar bplo-portal">
    @php
        $user = auth()->user();
        $unread = $user ? \App\Models\Notification::where('user_id', $user->id)->whereNull('read_at')->count() : 0;

        // Role-aware navigation links: [url, label, icon].
        $nav = [];
        if ($user?->isApplicant()) {
            $nav = [
                ['/dashboard', 'Dashboard'],
                ['/applications/create', 'New Application'],
            ];
        } elseif ($user?->isBplo()) {
            $nav = [
                ['/bplo/dashboard', 'Dashboard'],
                ['/bplo/applications', 'Application Review'],
                ['/bplo/permits', 'Permit Release'],
            ];
        } elseif ($user?->isTreasurer()) {
            $nav = [
                ['/treasurer/dashboard', 'Dashboard'],
                ['/treasurer/payments', 'Payments'],
            ];
        } elseif ($user?->isInspector()) {
            $nav = [
                ['/inspector/dashboard', 'Dashboard'],
                ['/inspector/permits', 'Permit Monitoring'],
                ['/verify', 'QR Verification'],
            ];
        } elseif ($user?->isAdmin()) {
            $nav = [
                ['/admin/dashboard', 'Dashboard'],
                ['/admin/users', 'Users'],
                ['/admin/requirement-types', 'Requirements'],
                ['/admin/fee-types', 'Fee Types'],
                ['/admin/fee-rates', 'Fee Rates'],
                ['/admin/audit-logs', 'Audit Logs'],
                ['/admin/settings', 'Settings'],
            ];
        }

        $roleLabel = $user ? match ($user->role) {
            'bplo' => 'BPLO Municipal Personnel',
            'inspector' => 'Municipal Permit Inspector',
            default => ucfirst($user->role),
        } : '';
        $current = '/'.ltrim(request()->path(), '/');
    @endphp

    <input type="checkbox" id="sidebar-toggle" class="sidebar-toggle-cb" hidden>

    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <a href="{{ url('/bplo/dashboard') }}">
                    <span class="sidebar-brand-mark">BP</span>
                    <span class="sidebar-brand-text">
                        {{ config('permit.municipality') }}
                        <small>BUSINESS PERMITS OFFICE</small>
                    </span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <p class="sidebar-section-label">WORKSPACE</p>
                @foreach ($nav as [$url, $label])
                    @php $active = $current === $url || str_starts_with($current, rtrim($url, '/').'/'); @endphp
                    <a href="{{ url($url) }}" class="sidebar-link {{ $active ? 'is-active' : '' }}">
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <span class="sidebar-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <span class="sidebar-user-meta">
                        <strong>{{ $user->name }}</strong>
                        <small>{{ $roleLabel }}</small>
                    </span>
                </div>
                <form method="POST" action="{{ url('/logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-secondary btn-block">Logout</button>
                </form>
            </div>
        </aside>

        {{-- Dim backdrop that closes the sidebar on mobile --}}
        <label for="sidebar-toggle" class="sidebar-backdrop"></label>

        <div class="main-area">
            <header class="topbar">
                <label for="sidebar-toggle" class="hamburger" title="Open navigation" aria-label="Open navigation">&#9776;</label>
                <div class="topbar-heading">
                    <span class="topbar-kicker">BPLO STAFF WORKSPACE</span>
                    <span class="topbar-title">@yield('title', 'Dashboard')</span>
                </div>
                <div class="topbar-right">
                    <x-notification-bell :count="$unread" />
                </div>
            </header>

            <main class="page">
                <div class="container-fluid">
                    <x-flash-messages />
                    @yield('content')
                </div>
            </main>

            <footer class="footer">
                &copy; {{ date('Y') }} {{ config('permit.municipality') }}, {{ config('permit.province') }}.
                Automated Business Permit Application and Status Tracking System.
            </footer>
        </div>
    </div>

    <script>
        (function () {
            // Close the mobile sidebar after navigating.
            document.querySelectorAll('.sidebar-link').forEach(function (a) {
                a.addEventListener('click', function () {
                    var cb = document.getElementById('sidebar-toggle');
                    if (cb) cb.checked = false;
                });
            });
        })();
    </script>
</body>
</html>
