@props(['banners'])

@if ($banners && $banners->count() > 0)
    <section class="banner-carousel-section py-4 container">
        <div class="swiper-container">
            <div class="swiper banner-home-swiper">
                <div class="swiper-wrapper">
                    @foreach ($banners as $banner)
                        <div class="swiper-slide">
                            <div class="slide-content">
                                <a href="{{ $banner->link ?? '#' }}" target="_blank" rel="noopener noreferrer">
                                    <img src="{{ asset('storage/' . $banner->image) ?? asset('assets/images/banner_default.jpg') }}"
                                        alt="{{ $banner->alt_text ?? 'Banner Image' }}" loading="lazy">
                                </a>
                                @if($banner->title)
                                <div class="title">
                                    <span>{{ $banner->title }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </section>

    @once
        @push('styles')
            <style>
                .banner-carousel-section {
                    font-size: 3rem;
                    color: var(--primary);
                    padding: 2rem 0;
                }

                .swiper-container {
                    position: relative;
                }

                .banner-home-swiper {
                    width: 100%;
                    padding: -70px 0;
                }

                .banner-home-swiper .swiper-slide {
                    width: 300px;
                    height: 404px;
                    position: relative;
                    border-radius: 10px;
                }

                .banner-home-swiper .swiper-slide img {
                    width: 300px;
                    height: 404px;
                    border-radius: 15px;
                    object-fit: cover;
                }

                .banner-home-swiper .title {
                    position: absolute;
                    bottom: 5px;
                    left: 50%;
                    transform: translate(-50%, -20%);
                    width: max-content;
                    text-align: center;
                    padding: 10px 15px;
                    background: rgba(46, 39, 39, 0.4);
                    border-radius: 6px;
                    border: 2px solid rgba(165, 117, 44, 0.4);
                    box-shadow: 0 3px 28px rgba(0, 0, 0, 0.2);
                    color: #fff;
                }

                .banner-home-swiper .swiper-slide-active .title {
                    box-shadow: 0 20px 30px 2px rgba(165, 117, 44, 0.4);
                }

                @media (min-width: 760px) {
                    .swiper-button-prev,
                    .swiper-button-next {
                        display: flex;
                    }
                }

                .swiper-pagination-bullet-active {
                    background: #facece;
                }

                .swiper-slide-active .title {
                    display: initial;
                }

                .swiper-button-next,
                .swiper-button-prev {
                    color: #facece !important;
                    transition: all .2s ease;
                }

                .swiper-button-next:hover,
                .swiper-button-prev:hover {
                    color: #900 !important;
                }
            </style>
        @endpush

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const bannerSwiper = new Swiper('.banner-home-swiper', {
                        autoplay: {
                            delay: 2500,
                            disableOnInteraction: false,
                        },
                        effect: 'coverflow',
                        grabCursor: true,
                        centeredSlides: true,
                        loop: true,
                        slidesPerView: 'auto',
                        coverflowEffect: {
                            rotate: 0,
                            stretch: 0,
                            depth: 100,
                            modifier: 2.5,
                            slideShadows: false
                        },
                        pagination: {
                            el: '.swiper-pagination',
                            clickable: true,
                        },
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        }
                    });
                });
            </script>
        @endpush
    @endif
@endonce