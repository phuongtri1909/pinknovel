<!-- filepath: /d:/full_truyen/resources/views/pages/chapter.blade.php -->
@extends('layouts.app')

@section('title', " Truyện {$story->title} | Chương {$chapter->number}: {$chapter->title} | " . config('app.name'))
@section('description', Str::limit(strip_tags($chapter->content), 160))
@section('keyword', "chương {$chapter->number}, {$chapter->title}")

@section('content')
    <section id="chapter" class="mt-80 mb-5">
        <div class="container-md">
            <div class="row">
                <!-- Main Content -->
                <div class="col-12">
                    <!-- Chapter Header -->
                    <div class="chapter-header text-center mb-4 animate__animated animate__fadeIn">
                        <h1 class="chapter-title h3 fw-bold">
                            Chương {{ $chapter->number }}: {{ $chapter->title }}
                        </h1>
                        <div class="chapter-meta d-flex justify-content-center align-items-center flex-wrap gap-3 mt-2">
                            <span class="badge bg-light text-dark p-2">
                                <i class="fa-regular fa-file-word me-1"></i> {{ $chapter->word_count }} Chữ
                            </span>
                            <span class="badge bg-light text-dark p-2">
                                <i class="fa-regular fa-clock me-1"></i> {{ $chapter->created_at->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>

                    <!-- Reading Controls Panel -->
                    <div class="reading-controls-wrapper mb-4 animate__animated animate__fadeIn animate__delay-1s">
                        <div class="card shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="search-wrapper position-relative w-100">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0">
                                                <i class="fas fa-search"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" id="search-chapter"
                                                placeholder="Tìm kiếm chương, tên chương, nội dung...">
                                        </div>
                                        <div id="search-results" class="position-absolute w-100 mt-1 d-none">
                                            <div class="card shadow">
                                                <div
                                                    class="card-header d-flex justify-content-between align-items-center py-2">
                                                    <span><i class="fas fa-list-ul me-2"></i>Kết quả tìm kiếm</span>
                                                    <button type="button" class="btn-close" id="close-search"></button>
                                                </div>
                                                <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                                                    <div class="list-group list-group-flush" id="results-list"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="reading-controls d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="navigation-controls d-flex gap-2">
                                        @if ($prevChapter)
                                            <a href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $prevChapter->slug]) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-arrow-left"></i> Chương trước
                                            </a>
                                        @else
                                            <button disabled class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-chevron-left"></i> Chương trước
                                            </button>
                                        @endif

                                        @if ($nextChapter)
                                            <a href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $nextChapter->slug]) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                Chương tiếp <i class="fas fa-arrow-right"></i>
                                            </a>
                                        @else
                                            <button disabled class="btn btn-outline-secondary btn-sm">
                                                Chương tiếp <i class="fas fa-chevron-right"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="appearance-controls d-flex align-items-center gap-3">
                                        <div class="font-size-control btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-secondary" id="font-decrease">
                                                <i class="fas fa-font"></i><i class="fas fa-minus fs-8"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" id="font-increase">
                                                <i class="fas fa-font"></i><i class="fas fa-plus fs-8"></i>
                                            </button>
                                        </div>

                                        <div class="theme-control btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-secondary theme-btn" data-theme="light"
                                                title="Chế độ sáng">
                                                <i class="fas fa-sun"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary theme-btn" data-theme="sepia"
                                                title="Chế độ giấy cũ">
                                                <i class="fas fa-scroll"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary theme-btn" data-theme="dark"
                                                title="Chế độ tối">
                                                <i class="fas fa-moon"></i>
                                            </button>
                                        </div>

                                        <button class="btn btn-sm btn-outline-secondary" id="fullscreen-toggle"
                                            title="Toàn màn hình">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reading Progress Bar -->
                    <div class="progress reading-progress mb-3 animate__animated animate__fadeIn animate__delay-1s"
                        style="height: 4px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" id="reading-progress-bar">
                        </div>
                    </div>

                    <!-- Chapter Content -->
                    <div id="chapter-content"
                        class="chapter-content mb-4 animate__animated animate__fadeIn animate__delay-1s">
                        {!! $chapter->content !!}
                    </div>

                    <!-- Chapter Navigation Bottom -->
                    <div
                        class="chapter-nav d-flex justify-content-between align-items-center my-4 animate__animated animate__fadeIn">
                        @if ($prevChapter)
                            <a href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $prevChapter->slug]) }}"
                                class="btn btn-primary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Chương trước
                            </a>
                        @else
                            <button disabled class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-chevron-left me-1"></i> Chương trước
                            </button>
                        @endif

                        <a href="{{ route('show.page.story', $story->slug) }}"
                            class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-book me-1"></i> Mục lục
                        </a>

                        @if ($nextChapter)
                            <a href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $nextChapter->slug]) }}"
                                class="btn btn-primary btn-sm">
                                Chương tiếp <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        @else
                            <button disabled class="btn btn-outline-secondary btn-sm">
                                Chương tiếp <i class="fas fa-chevron-right ms-1"></i>
                            </button>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

    <script>
        // DOM elements - Fix: No redeclaration
        const contentElement = document.getElementById('chapter-content');
        const chapterSection = document.getElementById('chapter');
        const progressBar = document.getElementById('reading-progress-bar');
        const fullscreenToggle = document.getElementById('fullscreen-toggle');
        const fontDecreaseBtn = document.getElementById('font-decrease');
        const fontIncreaseBtn = document.getElementById('font-increase');

        let fontSize = localStorage.getItem('fontSize') || 18;
        let theme = localStorage.getItem('theme') || 'light';
        let isFullscreen = false;

        // Font size controls
        function changeFontSize(delta) {
            fontSize = Math.max(14, Math.min(24, parseInt(fontSize) + delta));
            contentElement.style.fontSize = `${fontSize}px`;
            localStorage.setItem('fontSize', fontSize);

            // Animation for font size change
            anime({
                targets: '#chapter-content',
                scale: [0.98, 1],
                opacity: [0.8, 1],
                duration: 300,
                easing: 'easeOutQuad'
            });
        }

        fontDecreaseBtn.addEventListener('click', () => changeFontSize(-1));
        fontIncreaseBtn.addEventListener('click', () => changeFontSize(1));

        // Theme controls
        document.querySelectorAll('.theme-btn').forEach(button => {
            button.addEventListener('click', () => {
                const newTheme = button.dataset.theme;

                // Don't reapply same theme
                if (theme === newTheme) return;

                theme = newTheme;
                applyTheme(theme);
                localStorage.setItem('theme', theme);

                // Animation for theme change
                anime({
                    targets: '#chapter-content',
                    opacity: [0.5, 1],
                    duration: 400,
                    easing: 'easeInOutQuad'
                });

                // Update active state on buttons
                updateActiveThemeButton();
            });
        });

        function applyTheme(theme) {
            // Remove existing theme classes
            contentElement.classList.remove('theme-light', 'theme-sepia', 'theme-dark');
            document.body.classList.remove('theme-light', 'theme-sepia', 'theme-dark');
            chapterSection.classList.remove('theme-light', 'theme-sepia', 'theme-dark');

            // Add new theme class
            contentElement.classList.add(`theme-${theme}`);
            document.body.classList.add(`theme-${theme}`);
            chapterSection.classList.add(`theme-${theme}`);
        }

        function updateActiveThemeButton() {
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.classList.remove('active', 'btn-primary');
                btn.classList.add('btn-outline-secondary');

                if (btn.dataset.theme === theme) {
                    btn.classList.add('active', 'btn-primary');
                    btn.classList.remove('btn-outline-secondary');
                }
            });
        }

        // Fullscreen toggle
        fullscreenToggle.addEventListener('click', () => {
            if (!isFullscreen) {
                // Enter fullscreen
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen();
                } else if (document.documentElement.msRequestFullscreen) {
                    document.documentElement.msRequestFullscreen();
                }
                fullscreenToggle.innerHTML = '<i class="fas fa-compress"></i>';
            } else {
                // Exit fullscreen
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
                fullscreenToggle.innerHTML = '<i class="fas fa-expand"></i>';
            }
            isFullscreen = !isFullscreen;
        });

        // New: Add scroll tracking for progress bar
        window.addEventListener('scroll', function() {
            // Update reading progress
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            const scrollTop = window.scrollY;
            const progress = (scrollTop / (documentHeight - windowHeight)) * 100;
            progressBar.style.width = `${progress}%`;
        });

        // Initialize settings
        window.addEventListener('DOMContentLoaded', () => {
            contentElement.style.fontSize = `${fontSize}px`;
            applyTheme(theme);
            updateActiveThemeButton();

            // Add animation to chapter content on load
            setTimeout(() => {
                document.querySelectorAll('.animate__animated').forEach(el => {
                    el.style.visibility = 'visible';
                });
            }, 100);
        });

        // Search functionality
        let searchTimeout;

        $('#search-chapter').on('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = $(this).val().trim();

            if (searchTerm.length < 2) {
                $('#search-results').addClass('d-none');
                return;
            }

            searchTimeout = setTimeout(() => {
                // Show loading indicator
                $('#results-list').html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="ms-2">Đang tìm kiếm...</span></div>');
                $('#search-results').removeClass('d-none');
                
                $.ajax({
                    url: '{{ route('chapters.search') }}',
                    data: {
                        search: searchTerm,
                        story_id: '{{ $story->id }}'
                    },
                    success: function(response) {
                        $('#results-list').html(response.html);
                        
                        // Add animation to results
                        anime({
                            targets: '#results-list .list-group-item',
                            opacity: [0, 1],
                            translateY: [10, 0],
                            delay: anime.stagger(50)
                        });
                    },
                    error: function() {
                        $('#results-list').html('<div class="text-center p-3">Có lỗi xảy ra khi tìm kiếm</div>');
                    }
                });
            }, 300);
        });

        $('#close-search').click(function() {
            $('#search-results').addClass('d-none');
            $('#search-chapter').val('');
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-wrapper').length) {
                $('#search-results').addClass('d-none');
            }
        });

        // Enhance sticky behavior for mobile
        if (window.innerWidth <= 768) {
            let lastScrollTop = 0;
            const controlsWrapper = document.querySelector('.reading-controls-wrapper');
            
            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                // Kiểm tra hướng cuộn
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    // Cuộn xuống & đã cuộn quá 100px - ẩn thanh điều khiển
                    controlsWrapper.style.transform = 'translateY(-100%)';
                    controlsWrapper.style.opacity = '0';
                    controlsWrapper.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
                } else {
                    // Cuộn lên hoặc ở gần đỉnh trang - hiển thị thanh điều khiển
                    controlsWrapper.style.transform = 'translateY(0)';
                    controlsWrapper.style.opacity = '1';
                }
                
                lastScrollTop = scrollTop;
            });
            
            // Hiển thị lại thanh điều khiển khi tap vào màn hình
            document.addEventListener('click', function() {
                controlsWrapper.style.transform = 'translateY(0)';
                controlsWrapper.style.opacity = '1';
            });
        }

        // Tracking reading progress
        let lastSavedProgress = 0;
        const MIN_PROGRESS_CHANGE = 5; // Chỉ lưu khi tiến độ thay đổi ít nhất 5%

        function trackReadingProgress() {
            const content = document.getElementById('chapter-content');
            if (!content) return;
            
            const contentHeight = content.scrollHeight;
            const viewportHeight = window.innerHeight;
            const scrollPosition = window.scrollY;
            
            // Tính toán phần trăm đã đọc
            const scrolled = scrollPosition + viewportHeight;
            const maxScrollable = contentHeight + content.offsetTop;
            let progressPercent = Math.min(100, Math.round((scrolled / maxScrollable) * 100));
            
            // Nếu đã cuộn quá 90%, coi như đã đọc hết
            if (progressPercent > 90) {
                progressPercent = 100;
            }
            
            // Cập nhật thanh tiến độ đọc
            const progressBar = document.getElementById('reading-progress-bar');
            if (progressBar) {
                progressBar.style.width = `${progressPercent}%`;
            }
            
            // Chỉ lưu khi có thay đổi đáng kể
            if (Math.abs(progressPercent - lastSavedProgress) >= MIN_PROGRESS_CHANGE) {
                if (window.progressUpdateTimeout) {
                    clearTimeout(window.progressUpdateTimeout);
                }
                
                window.progressUpdateTimeout = setTimeout(() => {
                    saveReadingProgress(progressPercent);
                    lastSavedProgress = progressPercent;
                }, 2000); // Đợi 2 giây sau khi dừng cuộn
            }
        }

        function saveReadingProgress(progressPercent) {
            // Sử dụng fetch API để gửi tiến độ đến server
            fetch('{{ route("reading.save-progress") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    story_id: {{ $story->id }},
                    chapter_id: {{ $chapter->id }},
                    progress_percent: progressPercent
                })
            }).catch(error => console.error('Error saving reading progress:', error));
        }

        // Lắng nghe sự kiện cuộn trang và thay đổi kích thước cửa sổ
        window.addEventListener('scroll', trackReadingProgress);
        window.addEventListener('resize', trackReadingProgress);

        // Tracking initial progress when page loads
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(trackReadingProgress, 1000); // Đợi 1 giây sau khi trang đã tải
        });

        // Lưu tiến độ khi rời khỏi trang
        window.addEventListener('beforeunload', () => {
            // Lưu tiến độ hiện tại ngay lập tức không cần đợi
            const content = document.getElementById('chapter-content');
            if (content) {
                const contentHeight = content.scrollHeight;
                const viewportHeight = window.innerHeight;
                const scrollPosition = window.scrollY;
                const scrolled = scrollPosition + viewportHeight;
                const maxScrollable = contentHeight + content.offsetTop;
                let progressPercent = Math.min(100, Math.round((scrolled / maxScrollable) * 100));
                
                if (progressPercent > 90) progressPercent = 100;
                
                // Sử dụng Beacon API để gửi dữ liệu mà không chặn trang
                const data = new FormData();
                data.append('story_id', {{ $story->id }});
                data.append('chapter_id', {{ $chapter->id }});
                data.append('progress_percent', progressPercent);
                data.append('_token', '{{ csrf_token() }}');
                
                navigator.sendBeacon('{{ route("reading.save-progress") }}', data);
            }
        });

        // Thêm vào phần script của trang đọc chương
        $(document).ready(function() {
            // Biến để lưu vị trí cuộn
            let lastScrollPosition = 0;
            let isScrollingTimer;
            let contentHeight = $('.chapter-content').height();
            let windowHeight = $(window).height();
            
            // Theo dõi vị trí cuộn trong khi đọc
            $(window).scroll(function() {
                clearTimeout(isScrollingTimer);
                
                // Tính toán phần trăm đã đọc
                lastScrollPosition = window.scrollY;
                let totalScrollHeight = $(document).height() - windowHeight;
                let progressPercent = Math.min(Math.round((lastScrollPosition / totalScrollHeight) * 100), 100);
                
                // Chờ người dùng dừng cuộn rồi mới cập nhật
                isScrollingTimer = setTimeout(function() {
                    saveReadingProgress(progressPercent);
                }, 1000);
            });
            
            // Lưu tiến độ đọc khi người dùng rời khỏi trang
            $(window).on('beforeunload', function() {
                let totalScrollHeight = $(document).height() - windowHeight;
                let progressPercent = Math.min(Math.round((lastScrollPosition / totalScrollHeight) * 100), 100);
                saveReadingProgress(progressPercent);
            });
            
            // Hàm lưu tiến độ đọc
            function saveReadingProgress(progressPercent) {
                $.ajax({
                    url: "{{ route('reading.save-progress') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        story_id: {{ $story->id }},
                        chapter_id: {{ $chapter->id }},
                        progress_percent: progressPercent
                    },
                    success: function(response) {
                        console.log("Đã lưu tiến độ đọc: " + progressPercent + "%");
                    },
                    error: function(xhr) {
                        console.error("Lỗi khi lưu tiến độ đọc");
                    }
                });
            }
            
            // Khôi phục vị trí cuộn nếu quay lại chương đã đọc dở
            function restoreScrollPosition() {
                @if(isset($userReading) && $userReading)
                    let savedPosition = {{ $userReading->progress_percent }};
                    if (savedPosition > 0) {
                        let totalScrollHeight = $(document).height() - windowHeight;
                        let scrollToPosition = (savedPosition / 100) * totalScrollHeight;
                        window.scrollTo(0, scrollToPosition);
                    }
                @endif
            }
            
            // Khôi phục vị trí khi tải trang
            restoreScrollPosition();
        });
    </script>
@endpush

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        .animate__animated {
            visibility: hidden;
            --animate-duration: 0.8s;
        }

        #chapter {
            min-height: 100vh;
            transition: background-color 0.5s ease, color 0.5s ease;
            position: relative;
        }

        .chapter-content {
            padding: 30px;
            font-size: 18px;
            line-height: 1.8;
            text-align: justify;
            border-radius: 12px;
            scroll-behavior: smooth;
            transition: all 0.3s ease;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }

        /* Typography improvements */
        /* Progress bar */
        .reading-progress {
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
            background-color: #eeeeee;
            border-radius: 2px;
        }

        /* Enhanced search results styling */
        #search-results {
            z-index: 1050;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        #search-results .card {
            border-radius: 8px;
            overflow: hidden;
        }

        #search-results .list-group-item {
            padding: 12px 16px;
            border-left: 0;
            border-right: 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #search-results .list-group-item:hover {
            background-color: rgba(13, 110, 253, 0.08);
            transform: translateX(4px);
        }

        .theme-dark #search-results .card {
            background-color: #2d2d2d;
            border-color: #444;
        }

        .theme-dark #search-results .list-group-item {
            border-color: #444;
            background-color: #2d2d2d;
            color: #eee;
        }

        .theme-dark #search-results .list-group-item:hover {
            background-color: #3a3a3a;
        }

        .theme-sepia #search-results .card {
            background-color: #f4ecd8;
            border-color: #d8cba7;
        }

        .theme-sepia #search-results .list-group-item {
            border-color: #d8cba7;
            background-color: #f4ecd8;
            color: #5b4636;
        }

        .theme-sepia #search-results .list-group-item:hover {
            background-color: #e8dcc0;
        }

        /* Loading spinner animation */
        @keyframes pulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }

        #results-list .spinner-border {
            animation: pulse 1.5s infinite ease-in-out;
        }

        /* Customize scrollbar */
        .chapter-content::-webkit-scrollbar {
            width: 8px;
        }

        .chapter-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .chapter-content::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .chapter-content::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Themes with improved colors */
        .theme-light {
            background-color: #ffffff;
            color: #333333;
        }

        .theme-light .chapter-content {
            background-color: #ffffff;
            color: #333333;
            border: 1px solid #eaeaea;
        }

        .theme-sepia {
            background-color: #f8f0e0;
            color: #5b4636;
        }

        .theme-sepia .chapter-content {
            background-color: #f4ecd8;
            color: #5b4636;
            border: 1px solid #e8dcbd;
        }

        .theme-dark {
            background-color: #222222;
            color: #dddddd;
        }

        .theme-dark .chapter-content {
            background-color: #2d2d2d;
            color: #cccccc;
            border: 1px solid #3a3a3a;
        }

        .theme-dark .card {
            background-color: #2d2d2d;
            border-color: #3a3a3a;
        }

        .theme-dark .card-header {
            background-color: #333333;
            border-color: #3a3a3a;
            color: #cccccc;
        }

        .theme-dark .btn-outline-secondary {
            color: #aaaaaa;
            border-color: #555555;
        }

        .theme-dark .form-control,
        .theme-dark .input-group-text {
            background-color: #333333;
            border-color: #444444;
            color: #cccccc;
        }

        .theme-dark .badge.bg-light {
            background-color: #333333 !important;
            color: #cccccc !important;
        }

        /* Search results */
        #search-results {
            z-index: 1000;
        }

        #search-results .card {
            border: 1px solid rgba(0, 0, 0, .125);
            box-shadow: 0 3px 15px rgba(0, 0, 0, .15);
        }

        #search-results .list-group-item {
            padding: 0.75rem 1rem;
            border-left: 0;
            border-right: 0;
            transition: all 0.2s ease;
        }

        #search-results .list-group-item:first-child {
            border-top: 0;
        }

        #search-results .list-group-item:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
        }

        .theme-dark #search-results .list-group-item:hover {
            background-color: #333333;
        }

        /* Button styles */
        .btn {
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .theme-btn.active {
            position: relative;
        }

        .reading-controls .btn-group {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .chapter-content {
                padding: 20px 15px;
            }

            .reading-controls-wrapper {
                position: sticky;
                top: 0;
                z-index: 100;
            }

            #search-results {
                position: fixed !important;
                top: 60px;
                left: 0;
                right: 0;
                margin: 0 15px;
            }

            .navigation-controls {
                width: 100%;
                justify-content: space-between;
                margin-bottom: 10px;
            }

            .appearance-controls {
                width: 100%;
                justify-content: space-between;
            }
        }

        /* Animations for theme button */
        .theme-btn i {
            transition: transform 0.3s ease;
        }

        .theme-btn:hover i {
            transform: rotate(15deg);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .chapter-content {
                padding: 20px 15px;
            }

            .reading-controls-wrapper {
                position: sticky !important;
                top: 60px !important;
                z-index: 1020 !important;
                width: 100%;
                margin-top: 10px;
                margin-bottom: 15px !important;
            }

            /* Thêm margin-top cho chapter content để tránh bị che khuất */
            .chapter-content {
                margin-top: 20px;
            }

            /* Đảm bảo reading progress luôn ở trên cùng */
            .reading-progress {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                margin: 0 !important;
                z-index: 1050 !important;
                border-radius: 0;
            }

            #search-results {
                position: fixed !important;
                top: 80px !important;
                left: 0;
                right: 0;
                margin: 0 15px;
                z-index: 1030 !important;
            }

            .navigation-controls {
                width: 100%;
                justify-content: space-between;
                margin-bottom: 10px;
            }

            .appearance-controls {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
@endpush
