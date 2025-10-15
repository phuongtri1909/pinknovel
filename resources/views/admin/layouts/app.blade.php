<!DOCTYPE html>
<html lang={{ app()->getLocale() }}>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        // Get the logo and favicon from LogoSite model
        $logoSite = \App\Models\LogoSite::first();
        $faviconPath = $logoSite && $logoSite->favicon ? Storage::url($logoSite->favicon) : asset('favicon.ico');
    @endphp

    <link rel="icon" type="image/png" href="{{ $faviconPath }}">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet">
    <link id="pagestyle" href="{{ asset('assets/css/dashboard.css') }}" rel="stylesheet" />
    @stack('styles-admin')
    <title>{{ config('app.name') }} - {{ __('dashboard') }}</title>
</head>

<body class="g-sidenav-show bg-gray-100">
    @include('components.sweetalert')

    @auth
        @include('admin.navbars.sidebar')
        <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg">
            @include('admin.navbars.nav')
            <div class="container-fluid py-4">
                @yield('content-auth')
                @include('admin.layouts.partials.footer')
            </div>
        </main>
    @endauth
    @guest
        <main>
            @yield('content-admin')
        </main>
    @endguest

    <footer>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/js/dashboard.min.js') }}"></script>
       
        @stack('scripts-admin')
    </footer>
</body>

</html>