@props(['banners'])

@if ($banners && $banners->count() > 0)
    <section class="banner-carousel-section py-4 container">
        <div class="swiper banner-home-swiper">
            <div class="swiper-wrapper">
                @foreach ($banners as $banner)
                    <div class="swiper-slide rounded-4">
                        <a href="{{ $banner->link ?? '#' }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ asset('storage/' . $banner->image) ?? asset('assets/images/banner_default.jpg') }}"
                                alt="{{ $banner->alt_text ?? 'Banner Image' }}" class="banner-home-image">
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination banner-home-pagination"></div>
            <div class="swiper-button-prev banner-home-prev"></div>
            <div class="swiper-button-next banner-home-next"></div>
        </div>
    </section>

    @once
        @push('styles')
            <style>
                .banner-carousel-section {
                    overflow: hidden;
                }

                .banner-home-swiper {
                    width: 100%;
                    padding-top: 25px;
                    padding-bottom: 35px;
                }

                .banner-home-swiper .swiper-slide {
                    background-position: center;
                    background-size: cover;
                    width: 23%;              
                    opacity: 1;
                    transform: scale(0.8);
                    transition: transform 0.5s ease;
                    border-radius: 6px;
                    overflow: hidden;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                    margin: 0;
                }

                .banner-home-swiper .swiper-slide img.banner-home-image {
                    display: block;
                    width: 100%;
                    height: auto;
                }

                /* Center slide */
                .banner-home-swiper .swiper-slide-active {
                    transform: scale(1);
                    z-index: 1;
                }

                /* Slide kế tiếp/trước */
                .banner-home-swiper .swiper-slide-next,
                .banner-home-swiper .swiper-slide-prev {
                    transform: scale(0.8);
                    z-index: 0;
                    margin: 0;
                }

                /* Slide thứ hai kế tiếp/trước */
                .banner-home-swiper .swiper-slide-next+.swiper-slide,
                .banner-home-swiper .swiper-slide-prev .swiper-slide-prev {
                    transform: scale(0.8);
                    z-index: -1;
                }

                /* Slide thứ ba */
                .banner-home-swiper .swiper-slide-next+.swiper-slide+.swiper-slide {
                    transform: scale(0.8);
                    z-index: -2;
                }

                .banner-home-pagination .swiper-pagination-bullet {
                    background-color: var(--primary-color-3);
                    opacity: 0.5;
                }

                .banner-home-pagination .swiper-pagination-bullet-active {
                    opacity: 1;
                    width: 12px;
                    height: 8px;
                    border-radius: 4px;
                }

                /* Điều chỉnh cho màn hình nhỏ */
                @media (max-width: 768px) {
                    .banner-home-swiper .swiper-slide {
                        width: 50%;
                    }

                    .banner-home-swiper .swiper-slide-next,
                    .banner-home-swiper .swiper-slide-prev {
                        transform: scale(0.8);
                    }

                    .banner-home-swiper .swiper-slide-next+.swiper-slide,
                    .banner-home-swiper .swiper-slide-prev .swiper-slide-prev {
                        opacity: 0;
                        transform: scale(0.5);
                    }
                }

                .swiper-button-prev,
                .swiper-button-next {
                    color: var(--primary-color-3) !important;
                }

                .swiper-button-prev:after,
                .swiper-button-next:after {
                    font-size: 20px;
                }
            </style>
        @endpush

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const bannerSwiper = new Swiper('.banner-home-swiper', {
                        effect: 'coverflow',
                        grabCursor: true,
                        centeredSlides: true,
                        slidesPerView: 'auto',
                        loop: true,
                        autoplay: {
                            delay: 400000,
                            disableOnInteraction: false,
                        },
                        coverflowEffect: {
                            rotate: 0,
                           
                            stretch: -20,
                        
                            depth: 100,
                            modifier: 1,
                            slideShadows: false,
                        },
                        pagination: {
                            el: '.banner-home-pagination',
                            clickable: true,
                        },
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        },
                        breakpoints: {
                            768: {
                                coverflowEffect: {
                                    stretch: -30,
                                    depth: 120
                                },
                            },
                            1024: {
                                coverflowEffect: {
                                    stretch: -35,
                                    depth: 150,
                                },
                            }
                        }
                    });
                });
            </script>
        @endpush
    @endif
@endonce
