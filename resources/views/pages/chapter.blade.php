@extends('layouts.app')

@section('title', " Truyện {$story->title} | Chương {$chapter->number}: {$chapter->title} | " . config('app.name'))
@section('description', Str::limit(strip_tags($chapter->content), 160))
@section('keyword', "chương {$chapter->number}, {$chapter->title}")

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <section id="chapter" class="mt-80 mb-5">
        <div class="container-md">
            <div>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="d-flex justify-content-center">
                    <nav aria-label="breadcrumb " class="pt-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-decoration-none color-3"
                                    href="{{ route('home') }}">Trang chủ</a></li>
                            <li class="breadcrumb-item story-title-breadcrumb text-truncate"><a
                                    class="text-decoration-none color-3 "
                                    href="{{ route('show.page.story', $story->slug) }}">{{ $story->title }}</a></li>
                            <li class="breadcrumb-item active color-3" aria-current="page">Chương {{ $chapter->number }}:
                                {{ $chapter->title }}</li>
                        </ol>
                    </nav>
                </div>

                <div class="chapter-header text-center mb-4 animate__animated animate__fadeIn">
                    <h1 class="chapter-title h3 fw-bold">
                        Chương {{ $chapter->number }}: {{ $chapter->title }}
                    </h1>
                    <div class="chapter-meta d-flex justify-content-center align-items-center flex-wrap gap-2 mt-2">

                        <span class="badge text-dark p-2">
                            <i class="fa-regular fa-clock me-1 color-3"></i>
                            Đăng lúc
                            @if ($chapter->schedule_publish_at)
                                {{ $chapter->schedule_publish_at->format('H:i d/m/Y') }}
                            @else
                                {{ $chapter->created_at->format('H:i d/m/Y') }}
                            @endif
                        </span>
                        <span class="badge text-dark p-2">
                            <i class="fa-regular fa-eye color-pp-1"></i> {{ $chapter->views }}
                        </span>
                        <span class="badge text-dark p-2">
                            <a href="#comments" class="text-decoration-none text-dark"><i
                                    class="fa-regular fa-comments color-success-custom"></i>
                                {{ $chapter->comments_count }}</a>

                        </span>
                    </div>
                </div>

                <div
                    class="chapter-nav d-flex justify-content-center align-items-center my-4 animate__animated animate__fadeIn animate__delay-1s">
                    @if ($prevChapter)
                        <a href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $prevChapter->slug]) }}"
                            class="btn bg-1 rounded-5 btn-prev me-2 text-white d-sm-flex align-items-center">
                            <i class="fas fa-arrow-left me-1 h-100"></i> <span class="d-none d-sm-block">Chương
                                trước</span>
                        </a>
                    @else
                        <button disabled
                            class="btn btn-outline-secondary rounded-5 btn-prev me-2 d-sm-flex align-items-center">
                            <i class="fas fa-arrow-left me-1"></i> <span class="d-none d-sm-block">Chương trước</span>
                        </button>
                    @endif

                    <div class="dropdown chapter-list-dropdown">
                        <button class="btn dropdown-toggle rounded-0 bg-1 text-white" type="button"
                            id="chapterListDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-bars me-2"></i>Chương {{ $chapter->number }}
                        </button>
                        <div class="dropdown-menu chapter-dropdown-menu" aria-labelledby="chapterListDropdown">
                            <div class="chapter-dropdown-header">
                                <h6>Danh sách chương</h6>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div class="chapter-dropdown-body">
                                @foreach ($story->chapters->sortBy('number') as $chap)
                                    <a class="dropdown-item {{ $chap->id === $chapter->id ? 'active' : '' }}"
                                        href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chap->slug]) }}">
                                        Chương {{ $chap->number }}: {{ $chap->title }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if ($nextChapter)
                        <a href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $nextChapter->slug]) }}"
                            class="btn bg-1 text-white btn-next rounded-5 ms-2 d-sm-flex align-items-center">
                            <span class="d-none d-sm-block">Chương tiếp</span> <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    @else
                        <button disabled
                            class="btn btn-outline-secondary btn-next rounded-5 ms-2 d-sm-flex align-items-center">
                            <span class="d-none d-sm-block ">Chương tiếp</span> <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    @endif
                </div>

                <!-- Chapter Content -->
                <div id="chapter-content" class="rounded-4 chapter-content mb-4">
                    @if (isset($hasAccess) && $hasAccess)
                        {!! nl2br(e($chapter->content)) !!}
                    @else
                        <div class="chapter-preview">
                            <!-- Hiển thị thông báo mua chương -->
                            <div class="purchase-notice bg-light p-4 rounded-3 text-center my-4">
                                <div class="mb-3">
                                    <i class="fas fa-lock fa-3x text-warning mb-3"></i>
                                    <h4 class="fw-bold">Nội dung này yêu cầu mua để đọc</h4>
                                    <p class="text-muted">Bạn cần mua chương này hoặc toàn bộ truyện để tiếp tục đọc</p>
                                </div>

                                <div class="purchase-options d-flex flex-column flex-md-row justify-content-center gap-3">
                                    @if ($chapter->price > 0)
                                        <div class="chapter-purchase-option p-3 border rounded">
                                            <h5 class="fw-bold">Mua chương này</h5>
                                            <p class="price mb-2"><i class="fas fa-coins text-warning"></i>
                                                {{ number_format($chapter->price) }} xu</p>
                                            @guest
                                                <a href="{{ route('login') }}" class="btn btn-primary">Đăng nhập để mua</a>
                                            @else
                                                <form action="{{ route('purchase.chapter') }}" method="POST"
                                                    class="purchase-form" id="purchase-chapter-form">
                                                    @csrf
                                                    <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                                                    <button type="button" class="btn btn-primary purchase-chapter-btn"
                                                        onclick="showPurchaseModal('chapter', {{ $chapter->id }}, 'Chương {{ $chapter->number }}: {{ $chapter->title }}', {{ $chapter->price }})">
                                                        <i class="fas fa-shopping-cart me-1"></i> Mua ngay
                                                    </button>
                                                </form>
                                            @endguest
                                        </div>
                                    @endif

                                    @if ($story->combo_price > 0)
                                        <div class="story-purchase-option p-3 border rounded bg-light">
                                            <h5 class="fw-bold">Mua trọn bộ truyện</h5>
                                            <p class="price mb-2"><i class="fas fa-coins text-warning"></i>
                                                {{ number_format($story->combo_price) }} xu</p>
                                            <p class="text-success small">

                                                <i class="fas fa-check-circle"></i> Truy cập tất cả
                                                {{ $story->chapters->count() ?? 0 }} chương
                                            </p>
                                            @guest
                                                <a href="{{ route('login') }}" class="btn btn-success">Đăng nhập để mua</a>
                                            @else
                                                <form action="{{ route('purchase.story.combo') }}" method="POST"
                                                    class="purchase-form" id="purchase-story-form">
                                                    @csrf
                                                    <input type="hidden" name="story_id" value="{{ $story->id }}">
                                                    <button type="button" class="btn btn-success purchase-story-btn"
                                                        onclick="showPurchaseModal('story', {{ $story->id }}, '{{ $story->title }}', {{ $story->combo_price }})">
                                                        <i class="fas fa-shopping-cart me-1"></i> Mua trọn bộ
                                                    </button>
                                                </form>
                                            @endguest
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Chapter Navigation Bottom -->
                <div
                    class="chapter-nav d-flex justify-content-center align-items-center my-4 animate__animated animate__fadeIn animate__delay-1s">
                    @if ($prevChapter)
                        <a href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $prevChapter->slug]) }}"
                            class="btn bg-1 rounded-5 btn-prev me-2 text-white d-sm-flex align-items-center">
                            <i class="fas fa-arrow-left me-1 h-100"></i> <span class="d-none d-sm-block">Chương
                                trước</span>
                        </a>
                    @else
                        <button disabled
                            class="btn btn-outline-secondary rounded-5 btn-prev me-2 d-sm-flex align-items-center">
                            <i class="fas fa-arrow-left me-1"></i> <span class="d-none d-sm-block">Chương trước</span>
                        </button>
                    @endif

                    <div class="dropdown chapter-list-dropdown">
                        <button class="btn dropdown-toggle rounded-0 bg-1 text-white" type="button"
                            id="chapterListDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-bars me-2"></i>Chương {{ $chapter->number }}
                        </button>
                        <div class="dropdown-menu chapter-dropdown-menu" aria-labelledby="chapterListDropdown">
                            <div class="chapter-dropdown-header">
                                <h6>Danh sách chương</h6>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div class="chapter-dropdown-body">
                                @foreach ($story->chapters->sortByDesc('number') as $chap)
                                    <a class="dropdown-item {{ $chap->id === $chapter->id ? 'active' : '' }}"
                                        href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chap->slug]) }}">
                                        Chương {{ $chap->number }}: {{ $chap->title }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if ($nextChapter)
                        <a href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $nextChapter->slug]) }}"
                            class="btn bg-1 text-white btn-next rounded-5 ms-2 d-sm-flex align-items-center">
                            <span class="d-none d-sm-block">Chương tiếp</span> <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    @else
                        <button disabled
                            class="btn btn-outline-secondary btn-next rounded-5 ms-2 d-sm-flex align-items-center">
                            <span class="d-none d-sm-block ">Chương tiếp</span> <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Reading Settings Floating Button -->
    <div class="reading-settings-container">
        <div class="reading-settings-menu">
            <button class="reading-setting-btn fullscreen-btn" title="Toàn màn hình">
                <i class="fas fa-expand"></i>
            </button>
            <button class="reading-setting-btn bookmark-btn" title="Đánh dấu trang">
                <i class="fas fa-bookmark"></i>
            </button>
            <button class="reading-setting-btn theme-btn" title="Chế độ tối/sáng">
                <i class="fas fa-moon"></i>
            </button>
            <button class="reading-setting-btn book-mode-btn" title="Chế độ sách">
                <i class="fas fa-book-open"></i>
            </button>
            <button class="reading-setting-btn font-increase-btn" title="Tăng cỡ chữ">
                <i class="fas fa-plus"></i>
            </button>
            <button class="reading-setting-btn font-decrease-btn" title="Giảm cỡ chữ">
                <i class="fas fa-minus"></i>
            </button>
            <button class="reading-setting-btn font-family-btn" title="Đổi font chữ">
                <i class="fas fa-font"></i>
            </button>
        </div>
        <button class="reading-settings-toggle">
            <i class="fas fa-cog"></i>
        </button>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-8">
                @if (!Auth()->check() || (Auth()->check() && Auth()->user()->ban_comment == false))
                    @include('components.comment', [
                        'pinnedComments' => $pinnedComments,
                        'regularComments' => $regularComments,
                    ])
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-sad-tear fa-4x text-muted mb-3 animate__animated animate__shakeX"></i>
                        <h5 class="text-danger">Bạn đã bị cấm bình luận!</h5>
                    </div>
                @endif

            </div>
            <div class="col-12 col-lg-4 mt-3 mt-sm-0">
                <div class="mt-4">
                    {{-- hot stories --}}
                    @include('components.hot_stories')
                </div>
            </div>
        </div>
    </div>

    {{-- @include('components.list_story_de_xuat', ['newStories' => $newStories]) --}}

    @auth
        @include('components.modals.chapter-purchase-modal')
    @endauth
@endsection



@push('styles')
    <style>
        .btn-prev {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .btn-next {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        .story-title-breadcrumb {
            max-width: 100%;
        }

        @media (max-width: 576px) {
            .story-title-breadcrumb {
                max-width: 100px;
            }
        }

        .reading-settings-container {
            position: fixed;
            left: 20px;
            bottom: 24px;
            z-index: 1000;
        }

        .reading-settings-toggle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--primary-color-3);
            color: white;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .reading-settings-toggle:hover {
            transform: scale(1.1);
        }

        .reading-settings-toggle i {
            font-size: 20px;
        }

        .reading-settings-menu {
            position: absolute;
            bottom: 60px;
            left: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            transform: translateY(10px);
        }

        .reading-settings-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .reading-setting-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: white;
            color: var(--primary-color-3);
            border: 2px solid var(--primary-color-3);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .reading-setting-btn:hover {
            transform: scale(1.1);
            background-color: var(--primary-color-3);
            color: white;
        }

        .reading-setting-btn.active {
            background-color: var(--primary-color-3);
            color: white;
        }

        /* Dark mode styles */
        body.dark-mode {
            background-color: #222;
            color: #eee;
        }

        body.dark-mode #chapter-content {
            color: #fff;
        }

        body.dark-mode #chapter-content * {
            color: #fff !important;
        }

        body.dark-mode .breadcrumb-item,
        body.dark-mode .breadcrumb-item a,
        body.dark-mode .chapter-title,
        body.dark-mode .badge,
        body.dark-mode .dropdown-menu,
        body.dark-mode .dropdown-item {
            color: #fff !important;
        }

        body.dark-mode .badge {
            background-color: #333;
        }

        body.dark-mode .dropdown-menu {
            background-color: #333;
        }

        body.dark-mode .dropdown-item:hover {
            background-color: #444;
        }

        /* Book mode styles */
        body.book-mode #chapter-content {
            background-color: #f8f5e8;
            padding: 30px;
            color: #333;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Font families */
        body.font-segoe {
            font-family: 'Segoe UI', 'Segoe UI Variable', -apple-system, BlinkMacSystemFont, system-ui, sans-serif !important;
        }

        body.font-roboto {
            font-family: 'Roboto', sans-serif !important;
        }

        body.font-open-sans {
            font-family: 'Open Sans', sans-serif !important;
        }

        body.font-lora {
            font-family: 'Lora', serif !important;
        }

        body.font-merriweather {
            font-family: 'Merriweather', serif !important;
        }

        /* Font family dropdown */
        .font-family-dropdown {
            position: absolute;
            left: 60px;
            bottom: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            padding: 10px;
            display: none;
        }

        .font-family-dropdown.active {
            display: block;
        }

        .font-family-dropdown button {
            display: block;
            width: 100%;
            text-align: left;
            padding: 8px 12px;
            border: none;
            background: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .font-family-dropdown button:hover {
            background-color: #f0f0f0;
        }

        /* Chapter dropdown styles */
        .chapter-dropdown-menu {
            max-height: 350px;
            overflow-y: auto;
            width: 300px;
            z-index: 9999 !important;
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            padding: 0;
            animation: dropdown-fade 0.3s ease;
            position: absolute !important;
            top: calc(100% + 5px) !important;
            left: -75px !important;
            right: auto !important;
            bottom: auto !important;
            transform: none !important;
            margin: 0 !important;
        }

        @keyframes dropdown-fade {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chapter-dropdown-menu.show {
            display: block;
        }

        .chapter-dropdown-header {
            position: sticky;
            top: 0;
            background: var(--primary-color-3);
            color: white;
            padding: 12px 15px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            z-index: 1;
        }

        .chapter-dropdown-header h6 {
            margin: 0;
            font-weight: 600;
            font-size: 16px;
        }

        .chapter-dropdown-body {
            padding: 8px 0;
        }

        .dropdown-item {
            padding: 10px 15px;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: rgba(var(--primary-color-3-rgb), 0.1);
            border-left: 3px solid var(--primary-color-3);
        }

        .dropdown-item.active {
            background-color: rgba(var(--primary-color-3-rgb), 0.2);
            border-left: 3px solid var(--primary-color-3);
            font-weight: 600;
            color: var(--primary-color-3);
        }

        .dropdown-divider {
            margin: 0;
            opacity: 0;
        }

        /* Custom scrollbar for dropdown */
        .chapter-dropdown-body::-webkit-scrollbar {
            width: 6px;
        }

        .chapter-dropdown-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .chapter-dropdown-body::-webkit-scrollbar-thumb {
            background: var(--primary-color-3);
            border-radius: 10px;
        }

        .chapter-dropdown-body::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color-4);
        }

        /* Dark mode dropdown styles */
        body.dark-mode .chapter-dropdown-menu {
            background-color: #333;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
        }

        body.dark-mode .chapter-dropdown-header {
            background: var(--primary-color-1);
        }

        body.dark-mode .dropdown-item {
            color: #fff;
            border-left: 3px solid transparent;
        }

        body.dark-mode .dropdown-item:hover {
            background-color: #444;
            border-left: 3px solid var(--primary-color-1);
        }

        body.dark-mode .dropdown-item.active {
            background-color: #3a3a3a;
            border-left: 3px solid var(--primary-color-1);
        }

        body.dark-mode .chapter-dropdown-body::-webkit-scrollbar-track {
            background: #2a2a2a;
        }

        /* For the bottom navigation dropdown, if it would go off-screen, show it above */
        .chapter-nav:last-of-type .chapter-dropdown-menu {
            top: auto !important;
            bottom: calc(100% + 5px) !important;
        }

        /* Purchase notice styles */
        .purchase-notice {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .purchase-options {
            max-width: 800px;
            margin: 0 auto;
        }

        .chapter-purchase-option,
        .story-purchase-option {
            flex: 1;
            transition: all 0.3s ease;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .chapter-purchase-option:hover,
        .story-purchase-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .story-purchase-option {
            background-color: #f0f8ff !important;
            border-color: #d1e7ff !important;
        }

        .price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #ff9800;
        }

        .preview-content {
            position: relative;
            padding: 1rem;
            background-color: #fff;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-color-3);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .preview-content::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 1) 100%);
        }

        /* Bookmark button styles */
        .bookmark-btn.active {
            background-color: var(--primary-color-3);
            color: white;
        }
        
        .bookmark-btn.active:hover {
            background-color: var(--primary-color-4);
            color: white;
        }
        
        /* Animation for bookmark button */
        @keyframes bookmark-pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .bookmark-btn.active i {
            animation: bookmark-pulse 0.3s ease-in-out;
        }

        /* Bookmark button styles in dark mode */
        body.dark-mode .bookmark-btn.active {
            background-color: var(--primary-color-1);
            color: white;
        }
        
        body.dark-mode .bookmark-btn.active:hover {
            background-color: var(--primary-color-2);
        }
    </style>
@endpush

@push('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script xử lý đánh dấu trang (bookmark) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bookmarkBtn = document.querySelector('.bookmark-btn');
            @auth
                // Kiểm tra trạng thái bookmark khi tải trang
                checkBookmarkStatus();
                
                // Xử lý sự kiện click bookmark
                bookmarkBtn.addEventListener('click', toggleBookmark);
            @else
                // Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
                bookmarkBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Cần đăng nhập',
                        text: 'Bạn cần đăng nhập để sử dụng tính năng đánh dấu',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Đăng nhập',
                        cancelButtonText: 'Hủy'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route("login") }}';
                        }
                    });
                });
            @endauth
            
            function checkBookmarkStatus() {
                @auth
                    fetch('{{ route("user.bookmark.status") }}?story_id={{ $story->id }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.is_bookmarked) {
                            bookmarkBtn.classList.add('active');
                            bookmarkBtn.title = 'Bỏ đánh dấu';

                        } else {
                            bookmarkBtn.classList.remove('active');
                            bookmarkBtn.title = 'Đánh dấu trang';
                        }
                    })
                    .catch(error => console.error('Error checking bookmark status:', error));
                @endauth
            }
            
            function toggleBookmark() {
                @auth
                    // CSRF token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    fetch('{{ route("user.bookmark.toggle") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            story_id: {{ $story->id }},
                            chapter_id: {{ $chapter->id }}
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'added') {
                            bookmarkBtn.classList.add('active');
                            bookmarkBtn.title = 'Bỏ đánh dấu';
                            
                            
                            // Hiển thị thông báo thành công
                            Swal.fire({
                                title: 'Thành công!',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else if (data.status === 'removed') {
                            bookmarkBtn.classList.remove('active');
                            bookmarkBtn.title = 'Đánh dấu trang';
                            
                            
                            // Hiển thị thông báo đã xóa
                            Swal.fire({
                                title: 'Đã xóa!',
                                text: data.message,
                                icon: 'info',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error toggling bookmark:', error);
                        
                        // Hiển thị thông báo lỗi
                        Swal.fire({
                            title: 'Lỗi!',
                            text: 'Đã xảy ra lỗi khi thực hiện đánh dấu truyện',
                            icon: 'error'
                        });
                    });
                @endauth
            }
        });
    </script>

    <!-- Script xử lý cài đặt đọc truyện -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Các chức năng khác của trang chapter
            const toggleBtn = document.querySelector('.reading-settings-toggle');
            const settingsMenu = document.querySelector('.reading-settings-menu');
            const fullscreenBtn = document.querySelector('.fullscreen-btn');
            const themeBtn = document.querySelector('.theme-btn');
            const bookModeBtn = document.querySelector('.book-mode-btn');
            const fontIncreaseBtn = document.querySelector('.font-increase-btn');
            const fontDecreaseBtn = document.querySelector('.font-decrease-btn');
            const fontFamilyBtn = document.querySelector('.font-family-btn');
            const chapterContent = document.getElementById('chapter-content');

            // Create font family dropdown
            const fontFamilyDropdown = document.createElement('div');
            fontFamilyDropdown.className = 'font-family-dropdown';
            fontFamilyDropdown.innerHTML = `
                <button data-font="font-segoe">Segoe UI (Mặc định)</button>
                <button data-font="font-roboto">Roboto</button>
                <button data-font="font-open-sans">Open Sans</button>
                <button data-font="font-lora">Lora</button>
                <button data-font="font-merriweather">Merriweather</button>
            `;
            document.querySelector('.reading-settings-container').appendChild(fontFamilyDropdown);

            // Toggle settings menu
            toggleBtn.addEventListener('click', function() {
                settingsMenu.classList.toggle('active');
                fontFamilyDropdown.classList.remove('active');
            });

            // Fullscreen functionality
            fullscreenBtn.addEventListener('click', function() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => {
                        console.log(`Error attempting to enable fullscreen: ${err.message}`);
                    });
                    fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
                    fullscreenBtn.classList.add('active');
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                        fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
                        fullscreenBtn.classList.remove('active');
                    }
                }
            });

            // Theme toggle (dark/light)
            themeBtn.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                themeBtn.classList.toggle('active');

                if (document.body.classList.contains('dark-mode')) {
                    themeBtn.innerHTML = '<i class="fas fa-sun"></i>';
                } else {
                    themeBtn.innerHTML = '<i class="fas fa-moon"></i>';
                }
            });

            // Book mode toggle
            bookModeBtn.addEventListener('click', function() {
                document.body.classList.toggle('book-mode');
                bookModeBtn.classList.toggle('active');
            });

            // Font size adjustment
            let currentFontSize = parseInt(window.getComputedStyle(chapterContent).fontSize);

            fontIncreaseBtn.addEventListener('click', function() {
                if (currentFontSize < 24) {
                    currentFontSize += 1;
                    chapterContent.style.fontSize = currentFontSize + 'px';
                    localStorage.setItem('chapter-font-size', currentFontSize);
                }
            });

            fontDecreaseBtn.addEventListener('click', function() {
                if (currentFontSize > 12) {
                    currentFontSize -= 1;
                    chapterContent.style.fontSize = currentFontSize + 'px';
                    localStorage.setItem('chapter-font-size', currentFontSize);
                }
            });

            // Font family toggle
            fontFamilyBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                fontFamilyDropdown.classList.toggle('active');
            });

            // Font family selection
            fontFamilyDropdown.querySelectorAll('button').forEach(button => {
                button.addEventListener('click', function() {
                    const fontClass = this.getAttribute('data-font');

                    // Remove all font classes from body
                    document.body.classList.remove('font-segoe', 'font-roboto', 'font-open-sans',
                        'font-lora', 'font-merriweather');

                    // Add selected font class to body
                    document.body.classList.add(fontClass);

                    // Save preference
                    localStorage.setItem('chapter-font-family', fontClass);

                    // Close dropdown
                    fontFamilyDropdown.classList.remove('active');
                });
            });

            // Close menus when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.reading-settings-container')) {
                    fontFamilyDropdown.classList.remove('active');
                }
            });

            // Load saved preferences
            function loadSavedPreferences() {
                // Load font size
                const savedFontSize = localStorage.getItem('chapter-font-size');
                if (savedFontSize) {
                    currentFontSize = parseInt(savedFontSize);
                    chapterContent.style.fontSize = currentFontSize + 'px';
                }

                // Load font family
                const savedFontFamily = localStorage.getItem('chapter-font-family');
                if (savedFontFamily) {
                    document.body.classList.add(savedFontFamily);
                }

                // Load theme
                if (localStorage.getItem('dark-mode') === 'true') {
                    document.body.classList.add('dark-mode');
                    themeBtn.innerHTML = '<i class="fas fa-sun"></i>';
                    themeBtn.classList.add('active');
                }

                // Load book mode
                if (localStorage.getItem('book-mode') === 'true') {
                    document.body.classList.add('book-mode');
                    bookModeBtn.classList.add('active');
                }
            }

            // Save theme preference when changed
            themeBtn.addEventListener('click', function() {
                localStorage.setItem('dark-mode', document.body.classList.contains('dark-mode'));
            });

            // Save book mode preference when changed
            bookModeBtn.addEventListener('click', function() {
                localStorage.setItem('book-mode', document.body.classList.contains('book-mode'));
            });

            // Load preferences on page load
            loadSavedPreferences();

            // Fix dropdown positioning
            const dropdownButtons = document.querySelectorAll('.chapter-list-dropdown .btn');
            dropdownButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Đợi Bootstrap mở dropdown
                    setTimeout(() => {
                        const dropdown = this.nextElementSibling;
                        if (dropdown && dropdown.classList.contains('show')) {
                            // Kiểm tra xem dropdown có bị tràn ra khỏi viewport không
                            const rect = dropdown.getBoundingClientRect();
                            const windowHeight = window.innerHeight;

                            // Nếu dropdown sẽ tràn ra khỏi màn hình
                            if (rect.bottom > windowHeight) {
                                dropdown.style.top = 'auto';
                                dropdown.style.bottom = 'calc(100% + 5px)';
                            } else {
                                dropdown.style.top = 'calc(100% + 5px)';
                                dropdown.style.bottom = 'auto';
                            }

                            // Đảm bảo dropdown không bị che khuất bởi nội dung
                            dropdown.style.zIndex = '9999';
                        }
                    }, 0);
                });
            });
        });
    </script>
@endpush
