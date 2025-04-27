@php
    // Check if the request is from a mobile device
    $isMobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', request()->header('User-Agent'));
@endphp
<div class="banner-home-container">
    <!-- Swiper slider -->
    <div class="swiper banner-slider">
        <div class="swiper-wrapper">
            @foreach ($banners as $banner)
                <div class="swiper-slide">
                    <a href="{{ route('banner.click',$banner) }}" class="stretched-link"></a>
                    @php
                        // Generate the correct image path based on device
                        $imagePath = '';
                        if ($banner->image) {
                            $directory = pathinfo($banner->image, PATHINFO_DIRNAME);
                            $filename = pathinfo($banner->image, PATHINFO_FILENAME);
                            $extension = pathinfo($banner->image, PATHINFO_EXTENSION);
                            
                            $prefix = $isMobile ? 'mobile_' : 'desktop_';
                            $imagePath = Storage::url($directory . '/' . $prefix . $filename . '.' . $extension);
                        } else {
                            $imagePath = asset('images/curved6.jpg');
                        }
                    @endphp
                    
                    <div class="banner-slide" style="background-image: url('{{ $imagePath }}')">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="banner-content">
                                        @if ($banner->story)
                                            <h2 class="banner-title fs-2">{{ $banner->story->title }}</h2>
                                            <div class="banner-meta">
                                                @foreach($banner->story->categories as $category)
                                                    <a class="category-tag text-decoration-none" href="{{ route('categories.show', $category) }}">
                                                        {{ $category->name }}
                                                    </a>
                                                @endforeach
                                                <span class="text-white">-</span>
                                                <span class="text-white">
                                                    @if($banner->story->status == 'completed')
                                                        <span class="badge bg-success bg-gradient rounded-4">Hoàn thành</span>
                                                    @else
                                                        <span class="badge bg-warning bg-gradient rounded-4">Đang viết</span>
                                                    @endif
                                                </span>

                                            </div>

                                            <p class="banner-description">
                                                {!! \Illuminate\Support\Str::limit($banner->story->description, 300) !!}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="swiper-pagination"></div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <style>
        .banner-home-container {
            margin-bottom: 3rem;
        }

        .banner-slider {
            width: 100%;
            height: 400px;
        }

        .banner-slide {
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .banner-slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 50%, rgba(0, 0, 0, 0.1) 100%);
        }

        .banner-content {
            position: relative;
            padding: 3rem 0;
            color: #fff;
        }

        .banner-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .banner-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .banner-description {
            margin-bottom: 1.5rem;
            font-size: 1rem;
            line-height: 1.6;
        }

        .banner-buttons {
            display: flex;
            gap: 1rem;
        }

        /* Swiper custom styles */
        .swiper-button-next,
        .swiper-button-prev {
            color: #fff;
        }

        .swiper-pagination-bullet {
            background: #fff;
        }

        .swiper-pagination-bullet-active {
            background: #0d6efd;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Swiper
            new Swiper('.banner-slider', {
                // Optional parameters
                loop: true,
                effect: 'fade',
                autoplay: {
                    delay: 3000,
                },

                // Navigation arrows
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },

                // Pagination
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
            });
        });
    </script>
@endpush
