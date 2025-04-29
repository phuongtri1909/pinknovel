<footer id="donate" class="mt-80">
    <div class="border-top-custom-2">
        <div class="container text-center py-4">
            <div class="social-icons mb-3 py-3">
                <a href="https://facebook.com" target="_blank" class="social-icon" aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com" target="_blank" class="social-icon" aria-label="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://instagram.com" target="_blank" class="social-icon" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://youtube.com" target="_blank" class="social-icon" aria-label="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
                <a href="https://discord.com" target="_blank" class="social-icon" aria-label="Discord">
                    <i class="fab fa-discord"></i>
                </a>
            </div>
            <div class="footer-links">
                <a href="{{ route('home') }}" class="text-decoration-none">Trang Chủ</a>
                <a href="" class="text-decoration-none">Truyện Full</a>
                <a href="" class="text-decoration-none">Truyện Đề Cử</a>
            </div>

            <div class="py-3">
                <span class="copyright text-dark text-sm text-gray-600">
                   ©{{ date('Y') }} - {{ env('APP_NAME') }} Bảo Lưu Mọi Quyền
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