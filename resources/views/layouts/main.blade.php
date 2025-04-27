<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @php
        // Get the logo and favicon from LogoSite model
        $logoSite = \App\Models\LogoSite::first();
        $faviconPath =
            $logoSite && $logoSite->favicon
                ? Storage::url($logoSite->favicon)
                : asset('assets/images/logo/favicon.ico');
    @endphp
    <title>@yield('title')</title>
    <meta name="description" content="@yield('description')">
    <meta name="keywords" content="@yield('keywords')">
    <link rel="shortcut icon" href="{{$faviconPath }}" type="image/x-icon">
    @stack('meta')

    <!-- Google Fonts -->
    <!-- Bootstrap CSS -->

    {{-- styles --}}
    <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">

    @stack('styles-main')

    {{-- end styles --}}
</head>

<body style="padding-top:0">

    <div class="">
        @include('components.toast-main')
        @yield('content-main')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/js/script.js') }}"></script>

    <script>
        showSavedToast();
    </script>

    @stack('scripts-main')
</body>

</html>
