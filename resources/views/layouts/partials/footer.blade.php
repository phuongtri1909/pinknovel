<footer id="donate" class="mt-80">
    <div class="border-top-custom-2">
        <div class="container text-center py-4">

            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="social-icons mb-3 py-3">
                        @forelse($socials as $social)
                            <a href="{{ $social->url }}" target="_blank" class="social-icon"
                                aria-label="{{ $social->name }}">
                                @if (strpos($social->icon, 'custom-') === 0)
                                    <span class="{{ $social->icon }}"></span>
                                @else
                                    <i class="{{ $social->icon }}"></i>
                                @endif
                            </a>
                        @empty
                            <a href="https://facebook.com" target="_blank" class="social-icon" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="mailto:contact@pinknovel.com" target="_blank" class="social-icon"
                                aria-label="Email">
                                <i class="fas fa-envelope"></i>
                            </a>
                        @endforelse
                    </div>
                    <div class="footer-links">
                        <a href="{{ route('home') }}">Trang Chủ</a>
                        <a href="{{ route('contact') }}">Liên hệ</a>
                        <a href="{{ route('privacy-policy') }}">Quyền riêng tư</a>
                        <a href="{{ route('terms') }}">Điều khoản sử dụng</a>
                        <a href="{{ route('content-rules') }}">Quy tắc nội dung</a>
                        <a href="{{ route('confidental') }}">Bảo mật thông tin</a>
                        <a href="{{ route('guide.show') }}">Hướng Dẫn</a>
                    </div>

                    <div class="py-3">
                        <span class="copyright text-dark text-sm text-gray-600">
                            ©{{ date('Y') }} - {{ env('APP_NAME') }} Bảo Lưu Mọi Quyền
                        </span>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <!-- Facebook Page Plugin -->
                    <div class="mt-4">
                        <div class="w-100">
                            <div class="fb-page" data-href="https://www.facebook.com/pinknovel"
                                data-small-header="false" data-adapt-container-width="true" data-hide-cover="false"
                                data-show-facepile="true">
                            </div>
                        </div>
                    </div>
                </div>

            </div>


        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/js/script.js') }}"></script>
@stack('scripts')
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v17.0"
    nonce="random_nonce"></script>
</body>

</html>
