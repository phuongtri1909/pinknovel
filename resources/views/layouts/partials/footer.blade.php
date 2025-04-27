<footer id="donate" class="mt-80">
    <div class="bg-site">
        <div class="container">
            <div class="row py-5 text-dark g-3">
                <!-- Logo and Description Column -->
                <div class="col-12 col-md-4">
                    @php
                        // Get the logo from LogoSite model
                        $logoSite = \App\Models\LogoSite::first();
                        $logoPath =
                            $logoSite && $logoSite->logo
                                ? Storage::url($logoSite->logo)
                                : asset('assets/images/logo/logo_site.webp');
                    @endphp
                    <img height="90" src="{{ $logoPath }}" alt="{{ config('app.name') }} logo">
                    @if ($donate)
                        <p class="mt-2" style="text-align: justify;">
                            {!! $donate->about_us !!}
                        </p>
                    @endif
                </div>

                <!-- Categories Column -->
                <div class="col-12 col-md-4">
                    <div class="footer-section">
                        <div class="d-flex align-items-baseline">
                            <i class="fa-regular fa-rectangle-list fa-xl me-2"></i>
                            <h5 class="text-dark mb-3 fw-bold">Thể Loại Truyện</h5>
                        </div>
                        <div class="footer-categories">
                            @foreach ($topCategories as $category)
                                <a href="{{ route('categories.story.show', $category->slug) }}"
                                    class="footer-category text-dark">{{ $category->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Donate Section Column -->
                <div class="col-12 col-md-4">
                    @if ($donate)
                        <div class="footer-section donate-section">
                            <div class="d-flex align-items-baseline">
                                <i class="fa-solid fa-hand-holding-heart fa-xl me-2"></i>
                                <h5 class="text-dark mb-3 fw-bold">{{ $donate->title ?? 'Ủng hộ tác giả' }}</h5>
                            </div>

                            <div class="row flex-column ">
                                @if ($donate->image_qr)
                                    <div class="col-md-5">
                                        <div class="qr-container text-center">
                                            <img src="{{ Storage::url($donate->image_qr) }}" alt="QR Code"
                                                class="img-fluid qr-code-footer">

                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-7">
                                    <div class="donate-description">
                                        @if ($donate->description)
                                            <p>{!! $donate->description !!}
                                            </p>
                                        @else
                                            <p>Ủng hộ tác giả để có thêm nhiều truyện hay.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="py-3 border-top">
                <span class="copyright text-dark">
                    Copyright © {{ date('Y') }} {{ request()->getHost() }}
                </span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/js/script.js') }}"></script>
@stack('scripts')

</body>

</html>
