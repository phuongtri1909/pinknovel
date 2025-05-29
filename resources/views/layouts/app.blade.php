@include('layouts.partials.header')

<body data-auth="{{ auth()->check() ? 'true' : 'false' }}">
    <div class="mt-88">
        @include('components.sweetalert')
        
        @yield('content')
        @include('components.top_button')
    </div>
    <div id="fb-root" class="w-100"></div>
</body>

@include('layouts.partials.footer')
