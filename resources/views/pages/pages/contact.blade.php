@extends('layouts.app')

@section('title', 'Liên hệ - ' . config('app.name'))

@section('description', 'Liên hệ với ' . config('app.name') . ' qua các kênh chính thức. Kết nối với chúng tôi qua YouTube, Facebook, Discord để được hỗ trợ và tham gia cộng đồng.')

@section('keyword', 'liên hệ, contact, ' . config('app.name') . ', youtube, facebook, discord, hỗ trợ, cộng đồng')

@section('meta')
    <meta property="og:type" content="website">
    <meta property="og:title" content="Liên hệ - {{ config('app.name') }}">
    <meta property="og:description" content="Liên hệ với {{ config('app.name') }} qua các kênh chính thức. Kết nối với chúng tôi qua YouTube, Facebook, Discord để được hỗ trợ và tham gia cộng đồng.">
    <meta property="og:image" content="{{ url(asset('assets/images/logo/logo_site.webp')) }}">
    <meta property="og:image:secure_url" content="{{ url(asset('assets/images/logo/logo_site.webp')) }}">
    <meta property="og:image:width" content="600">
    <meta property="og:image:height" content="600">
    <meta property="og:image:alt" content="Logo {{ config('app.name') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="vi_VN">

    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Liên hệ - {{ config('app.name') }}">
    <meta name="twitter:description" content="Liên hệ với {{ config('app.name') }} qua các kênh chính thức. Kết nối với chúng tôi qua YouTube, Facebook, Discord để được hỗ trợ và tham gia cộng đồng.">
    <meta name="twitter:image" content="{{ url(asset('assets/images/logo/logo_site.webp')) }}">
    <meta name="twitter:image:alt" content="Logo {{ config('app.name') }}">
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header Section -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">Liên hệ với chúng tôi</h1>
                <p class="lead text-muted">Kết nối với {{ config('app.name') }} qua các kênh chính thức</p>
            </div>

            <!-- Social Links Section -->
            <div class="contact-social-section">
                <div class="row g-4">
                    <!-- YouTube -->
                    {{-- <div class="col-md-4">
                        <div class="social-card youtube-card">
                            <div class="social-icon-large">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                                    <path d="M22.54 6.42C22.4212 5.94541 22.1793 5.51057 21.8387 5.15941C21.4981 4.80824 21.0708 4.55318 20.6 4.42C18.88 4 12 4 12 4S5.12 4 3.4 4.46C2.92921 4.59318 2.50191 4.84824 2.16131 5.19941C1.82071 5.55057 1.57879 5.98541 1.46 6.46C1.14521 8.20556 0.991197 9.97631 1 11.75C0.988726 13.537 1.13977 15.3213 1.46 17.08C1.59095 17.5398 1.8379 17.9581 2.17774 18.2945C2.51758 18.6308 2.93842 18.8738 3.4 19C5.12 19.46 12 19.46 12 19.46S18.88 19.46 20.6 19C21.0708 18.8668 21.4981 18.6118 21.8387 18.2606C22.1793 17.9094 22.4212 17.4746 22.54 17C22.8524 15.2676 23.0062 13.5103 23 11.75C23.0113 9.96295 22.8602 8.1787 22.54 6.42Z" fill="currentColor"/>
                                    <path d="M9.75 15.02L15.5 11.75L9.75 8.48V15.02Z" fill="white"/>
                                </svg>
                            </div>
                            <h3 class="social-title">YouTube</h3>
                            <p class="social-description">Theo dõi kênh chính thức để xem video mới nhất</p>
                            <a href="https://youtube.com/@AkayTruyen?sub_confirmation=1" target="_blank" class="btn btn-danger btn-md">
                                <i class="fab fa-youtube me-2"></i>
                                Đăng ký kênh
                            </a>
                        </div>
                    </div> --}}

                    <!-- Facebook -->
                    <div class="col-md-4">
                        <div class="social-card facebook-card">
                            <div class="social-icon-large">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                                    <path d="M24 12.073C24 5.403 18.627 0 12 0S0 5.403 0 12.073C0 18.06 4.388 23.02 10.125 23.854V15.442H7.078V12.073h3.047V9.415c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.369h-2.796v8.412C19.612 23.02 24 18.06 24 12.073Z" fill="currentColor"/>
                                </svg>
                            </div>
                            <h3 class="social-title">Facebook</h3>
                            <p class="social-description">Tham gia nhóm cộng đồng để thảo luận</p>
                            <a href="https://www.facebook.com/pinknovel" target="_blank" class="btn btn-primary btn-md">
                                <i class="fab fa-facebook me-2"></i>
                                Tham gia nhóm
                            </a>
                        </div>
                    </div>

                    <!-- Discord -->
                    {{-- <div class="col-md-4">
                        <div class="social-card discord-card">
                            <div class="social-icon-large">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                                    <path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419-.019 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1568 2.4189Z" fill="currentColor"/>
                                </svg>
                            </div>
                            <h3 class="social-title">Discord</h3>
                            <p class="social-description">Chat trực tiếp với cộng đồng</p>
                            <a href="https://discord.gg/Gnjvk8xvex" target="_blank" class="btn btn-dark btn-md">
                                <i class="fab fa-discord me-2"></i>
                                Tham gia Discord
                            </a>
                        </div>
                    </div> --}}
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .contact-social-section {
        margin: 2rem 0;
    }

    .social-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        height: 100%;
        border: 1px solid #f0f0f0;
    }

    .social-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .youtube-card:hover {
        border-color: #ff0000;
    }

    .facebook-card:hover {
        border-color: #1877f2;
    }

    .discord-card:hover {
        border-color: #5865f2;
    }

    .social-icon-large {
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: center;
    }

    .social-icon-large svg {
        color: #14425d;
        transition: all 0.3s ease;
    }

    .youtube-card:hover .social-icon-large svg {
        color: #ff0000;
    }

    .facebook-card:hover .social-icon-large svg {
        color: #1877f2;
    }

    .discord-card:hover .social-icon-large svg {
        color: #5865f2;
    }

    .social-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #2d3748;
    }

    .social-description {
        color: #718096;
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }


    @media (max-width: 768px) {
        .social-card {
            padding: 1.5rem;
        }
    }
</style>
@endpush
