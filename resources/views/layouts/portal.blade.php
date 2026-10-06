<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#171821">
    <title>{{ $pageTitle ?? 'HR portal' }} · Demo HRMS</title>
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body data-role="{{ auth()->user()->role }}" @hasSection('hide_sidebar') class="no-sidebar" @endif>
<div class="portal-shell">
    @hasSection('hide_sidebar')
    @else
        @include('components.sidebar')
    @endif
    <main class="main-area" @hasSection('hide_sidebar') style="margin-left:0" @endif>
        <header class="topbar">
            @hasSection('hide_sidebar')
            @else
                <button class="mobile-menu" type="button" data-menu-toggle aria-label="Open navigation">☰</button>
            @endif
            <div class="breadcrumbs">
                <span>Demo HRMS</span>
                <span class="breadcrumb-separator">/</span>
                <strong>{{ $pageTitle ?? 'Dashboard' }}</strong>
            </div>
            <div class="topbar-actions">
                <span class="location-pill"><i></i>PUNE OFFICE</span>
                <span class="top-avatar">{{ collect(explode(' ', auth()->user()->name))->map(function ($part) { return mb_substr($part, 0, 1); })->take(2)->implode('') }}</span>
            </div>
        </header>
        <div class="page-content" id="portal-content">
            @if (session('status'))
                <div class="notice notice-success" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice notice-error" role="alert">{{ $errors->first() }}</div>
            @endif
            @yield('content')
            <footer class="page-footer">
                <span>© {{ now()->year }} Demo HRMS</span>
                <span>People workspace</span>
            </footer>
        </div>
    </main>
</div>
<div class="toast" id="portal-toast" role="status" aria-live="polite"></div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js" crossorigin="anonymous" defer></script>
<script src="{{ asset('js/portal.js') }}" defer></script>
@stack('scripts')
</body>
</html>
