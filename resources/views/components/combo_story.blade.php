@if (isset($story) && $story->has_combo)
    <div class="combo-wrapper animate__animated animate__fadeIn">
        <div class="combo-card">
            <div class="combo-badge">
                @php
                    $totalChapterPrice = $story->chapters->where('is_free', 0)->sum('price');

                    if ($totalChapterPrice > 0) {
                        $savingPercent = round((($totalChapterPrice - $story->combo_price) / $totalChapterPrice) * 100);
                        $savingAmount = $totalChapterPrice - $story->combo_price;
                    } else {
                        $savingPercent = 0;
                        $savingAmount = 0;
                    }
                @endphp

                @if ($savingPercent > 0)
                    <div class="combo-discount-badge">
                        <span class="discount-text">-{{ $savingPercent }}%</span>
                    </div>
                @endif
            </div>

            <div class="combo-content">


                <div class="combo-body">
                    <div class="combo-price-section">
                        <div class="price-comparison">
                            @if ($totalChapterPrice > 0)
                                <div class="old-price">{{ number_format($totalChapterPrice) }} Coin</div>
                            @endif
                            <div class="new-price">{{ number_format($story->combo_price) }} Coin</div>
                        </div>
                        <div class="savings-info">
                            @if ($totalChapterPrice > 0 && $savingAmount > 0)
                                <div class="savings-tag">
                                    <i class="fas fa-tags"></i> Tiết kiệm {{ number_format($savingAmount) }} Coin
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="combo-description">
                        <p>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Mua combo truyện <strong>"{{ $story->title }}"</strong> để được đọc tất cả
                            <strong>{{ $story->chapters->where('is_free', 0)->count() }}</strong> chương VIP
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Combo <strong>không bị giới hạn thời gian</strong>, đọc mọi lúc mọi nơi
                        </p>
                    </div>
                </div>

                <div class="combo-action">
                    <button id="buyComboBtn" class="btn buy-combo-btn text-dark"
                        onclick="buyCombo({{ $story->id }})">
                        <i class="fas fa-shopping-cart me-2"></i> Mua Ngay
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
        <style>
            /* Combo Wrapper */
            .combo-wrapper {
                margin: 1.5rem 0;
            }

            /* Main Card */
            .combo-card {
                position: relative;
                border: none;
                border-radius: 16px;
                background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
                box-shadow: 0 10px 30px var(--primary-color-1);
                overflow: hidden;
                padding: 0;
                transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            }

            .combo-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 35px var(--primary-color-2);
            }

            .combo-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 6px;
                background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-color-2) 100%);
            }

            /* Badge */
            .combo-discount-badge {
                position: absolute;
                top: 20px;
                right: -30px;
                background: #ff3860;
                color: white;
                transform: rotate(45deg);
                padding: 5px 40px;
                font-weight: bold;
                z-index: 100;
                box-shadow: 0 2px 10px rgba(255, 56, 96, 0.3);
                animation: pulse-red 2s infinite;
            }

            /* Content */
            .combo-content {
                padding: 1.5rem;
            }

            .combo-header {
                display: flex;
                align-items: center;
                margin-bottom: 1.25rem;
                padding-bottom: 0.75rem;
                border-bottom: 1px dashed rgba(0, 0, 0, 0.1);
            }

            .combo-icon {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: var(--primary-color);
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 1rem;
            }

            .combo-icon i {
                font-size: 1.5rem;
                color: white;
            }

            .combo-title {
                margin: 0;
                font-size: 1.5rem;
                font-weight: 700;
                color: #333;
            }

            .combo-body {
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
                margin-bottom: 1.5rem;
            }

            /* Price Section */
            .combo-price-section {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 1rem;
            }

            .price-comparison {
                display: flex;
                flex-direction: column;
            }

            .old-price {
                font-size: 1rem;
                color: #888;
                text-decoration: line-through;
                margin-bottom: 0.25rem;
            }

            .new-price {
                font-size: 1.75rem;
                font-weight: bold;
                color: var(--primary-color-3);
            }

            .savings-tag {
                display: inline-block;
                padding: 5px 12px;
                background-color: rgba(76, 175, 80, 0.1);
                color: #4CAF50;
                border-radius: 50px;
                font-weight: 500;
                font-size: 0.9rem;
            }

            /* Description */
            .combo-description {
                padding: 1rem;
                background-color: rgba(67, 80, 255, 0.05);
                border-left: 4px solid var(--primary-color);
                border-radius: 0 8px 8px 0;
            }

            .combo-description p {
                margin-bottom: 0.75rem;
                font-size: 1rem;
                color: #444;
            }

            /* Action Button */
            .combo-action {
                display: flex;
                justify-content: center;
            }

            .buy-combo-btn {
                padding: 0.75rem 2.5rem;
                font-size: 1.1rem;
                font-weight: 600;
                color: white;
                background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-color-2) 100%);
                border: none;
                border-radius: 50px;
                box-shadow: 0 5px 15px var(--primary-color-2);
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }

            .buy-combo-btn::after {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, var(--primary-color-3), transparent);
                transition: 0.5s;
            }

            .buy-combo-btn:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px var(--primary-color-3);
            }

            .buy-combo-btn:hover::after {
                left: 100%;
            }

            .buy-combo-btn:active {
                transform: translateY(0);
            }

            /* Animations */
            .pulse-animation {
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(67, 80, 255, 0.6);
                }

                70% {
                    box-shadow: 0 0 0 10px rgba(67, 80, 255, 0);
                }

                100% {
                    box-shadow: 0 0 0 0 rgba(67, 80, 255, 0);
                }
            }

            @keyframes pulse-red {
                0% {
                    box-shadow: 0 0 0 0 rgba(255, 56, 96, 0.6);
                }

                70% {
                    box-shadow: 0 0 0 7px rgba(255, 56, 96, 0);
                }

                100% {
                    box-shadow: 0 0 0 0 rgba(255, 56, 96, 0);
                }
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .combo-price-section {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .combo-action {
                    width: 100%;
                }

                .buy-combo-btn {
                    width: 100%;
                }

                .combo-title {
                    font-size: 1.3rem;
                }

                .new-price {
                    font-size: 1.5rem;
                }
            }

             /* Styles cho SweetAlert */
            .animated-popup {
                animation: zoomIn 0.3s;
            }

            @keyframes zoomIn {
                from {
                    opacity: 0;
                    transform: scale(0.8);
                }

                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            .success-animation,
            .error-animation {
                text-align: center;
                padding: 20px 0;
            }

            .success-animation i {
                animation: bounceIn 1s;
            }

            .error-animation i {
                animation: headShake 1s;
            }

            @keyframes bounceIn {

                0%,
                20%,
                40%,
                60%,
                80%,
                to {
                    animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);
                }

                0% {
                    opacity: 0;
                    transform: scale3d(0.3, 0.3, 0.3);
                }

                20% {
                    transform: scale3d(1.1, 1.1, 1.1);
                }

                40% {
                    transform: scale3d(0.9, 0.9, 0.9);
                }

                60% {
                    opacity: 1;
                    transform: scale3d(1.03, 1.03, 1.03);
                }

                80% {
                    transform: scale3d(0.97, 0.97, 0.97);
                }

                to {
                    opacity: 1;
                    transform: scaleX(1);
                }
            }

            @keyframes headShake {
                0% {
                    transform: translateX(0);
                }

                6.5% {
                    transform: translateX(-6px) rotateY(-9deg);
                }

                18.5% {
                    transform: translateX(5px) rotateY(7deg);
                }

                31.5% {
                    transform: translateX(-3px) rotateY(-5deg);
                }

                43.5% {
                    transform: translateX(2px) rotateY(3deg);
                }

                50% {
                    transform: translateX(0);
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
        <script>
            // Thêm hiệu ứng lấp lánh cho nút mua
            document.addEventListener('DOMContentLoaded', function() {
                setInterval(function() {
                    const buyBtn = document.getElementById('buyComboBtn');
                    if (buyBtn) {
                        buyBtn.classList.add('animate__animated', 'animate__pulse');

                        setTimeout(function() {
                            buyBtn.classList.remove('animate__animated', 'animate__pulse');
                        }, 1000);
                    }
                }, 5000); // Pulse every 5 seconds
            });

            // Hàm mua combo
            function buyCombo(storyId) {
                @if (Auth::check())
                    Swal.fire({
                        title: 'Xác nhận mua combo?',
                        html: 'Bạn có muốn mua combo truyện <strong>"{{ $story->title }}"</strong> với giá <strong>{{ number_format($story->combo_price) }} Coin</strong>?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: '<i class="fas fa-shopping-cart me-2"></i> Xác nhận mua',
                        cancelButtonText: '<i class="fas fa-times me-2"></i> Hủy',
                        customClass: {
                            popup: 'animated-popup'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) { // Chỉ thực hiện khi nút "Xác nhận" được click
                            // Hiển thị loading
                            Swal.fire({
                                title: 'Đang xử lý...',
                                html: '<div class="spinner-border text-primary" role="status"></div><p class="mt-3">Vui lòng chờ trong giây lát</p>',
                                allowOutsideClick: false,
                                showConfirmButton: false
                            });

                            // Gửi request mua combo
                            $.ajax({
                                url: '',
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    // Hiệu ứng confetti khi thành công
                                    const duration = 3000;
                                    const animationEnd = Date.now() + duration;
                                    const colors = ['#4350ff', '#43ff64', '#ff43ed', '#ff9543'];

                                    (function frame() {
                                        confetti({
                                            particleCount: 2,
                                            angle: 60,
                                            spread: 55,
                                            origin: {
                                                x: 0
                                            },
                                            colors: colors
                                        });
                                        confetti({
                                            particleCount: 2,
                                            angle: 120,
                                            spread: 55,
                                            origin: {
                                                x: 1
                                            },
                                            colors: colors
                                        });

                                        if (Date.now() < animationEnd) {
                                            requestAnimationFrame(frame);
                                        }
                                    }());

                                    Swal.fire({
                                        title: 'Mua thành công!',
                                        html: `<div class="success-animation">
                                                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                                                <p class="mb-2">Bạn đã mua thành công combo truyện!</p>
                                                <p class="text-muted small">Số dư còn lại: <strong>${response.newBalance} Coin</strong></p>
                                               </div>`,
                                        icon: false,
                                        confirmButtonText: 'Đọc ngay <i class="fas fa-book-reader ms-1"></i>',
                                        confirmButtonColor: '#4350ff'
                                    }).then(() => {
                                        // Reload trang hoặc cập nhật UI
                                        window.location.reload();
                                    });
                                },
                                error: function(xhr) {
                                    let message = 'Đã xảy ra lỗi khi xử lý giao dịch';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        message = xhr.responseJSON.message;
                                    }

                                    Swal.fire({
                                        title: 'Không thể mua combo',
                                        html: `<div class="error-animation">
                                                <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                                                <p>${message}</p>
                                               </div>`,
                                        icon: false,
                                        confirmButtonText: 'Đóng',
                                        confirmButtonColor: '#4350ff'
                                    });
                                }
                            });
                        }
                        // Không cần thêm else vì SweetAlert2 tự động đóng khi nhấn nút Hủy
                    });
                @else
                    const currentUrl = window.location.href;
                    window.location.href = '{{ route('login') }}?redirect=' + encodeURIComponent(currentUrl);
                @endif
            }
        </script>
    @endpush
@endonce
