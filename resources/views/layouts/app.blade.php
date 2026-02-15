@include('layouts.partials.header')


<body data-auth="{{ auth()->check() ? 'true' : 'false' }}">
    <div class="mt-88">
        @include('components.sweetalert')

        <div class="container d-md-none" id="mobileSearchContainer" style="display: none;">
            <div class="position-relative">
                <form action="{{ route('searchHeader') }}" method="GET" class="mobile-search-form">
                    <input type="text" name="query" class="form-control mt-3 rounded-4"
                        placeholder="Tìm kiếm truyện..." value="{{ request('query') }}" id="mobileSearchInput">
                    <button type="submit" class="btn search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Validate mobile search form
                const mobileSearchForm = document.querySelector('.mobile-search-form');
                if (mobileSearchForm) {
                    const mobileSearchInput = document.getElementById('mobileSearchInput');
                    if (mobileSearchInput) {
                        mobileSearchForm.addEventListener('submit', function(e) {
                            if (!mobileSearchInput.value || mobileSearchInput.value.trim() === '') {
                                e.preventDefault();
                                e.stopPropagation();
                                mobileSearchInput.focus();
                                return false;
                            }
                        });
                    }
                }
            });
        </script>

        @yield('content')
        @include('components.top_button')
        @include('components.reading_settings')
        @include('components.messenger_button')
    </div>
    <div id="fb-root" class="w-100"></div>
</body>

@include('layouts.partials.footer')
