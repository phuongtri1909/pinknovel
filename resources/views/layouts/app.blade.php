@include('layouts.partials.header')

<body data-auth="{{ auth()->check() ? 'true' : 'false' }}">
    <div class="mt-88">
        @include('components.toast')
        @include('components.toast-main')
        
        @yield('content')
        @include('components.top_button')
    </div>

    @stack('scripts')
</body>

@include('layouts.partials.footer')
