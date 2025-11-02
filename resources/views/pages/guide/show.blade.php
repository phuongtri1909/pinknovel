@extends('layouts.app')

@section('title', $guide ? $guide->title : 'Hướng dẫn')
@section('description', $guide ? $guide->meta_description : 'Hướng dẫn sử dụng Pink Novel')
@section('keywords', $guide ? $guide->meta_keywords : 'hướng dẫn, pink novel, truyện')

@section('content')
    <div class="container py-5 animate__animated animate__fadeIn">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="mb-4 text-center">
                    <h1 class="h2 mb-3 color-3">{{ $guide->title ?? 'Hướng dẫn' }}</h1>
                </div>

                <div class="guide-content mb-4 animate__animated animate__fadeInUp">
                    @if ($guide)
                        {!! $guide->content !!}
                    @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-circle-info fa-3x mb-3 text-muted"></i>
                            <p class="lead">Thông tin hướng dẫn đang được cập nhật.</p>
                            <p>Vui lòng quay lại sau.</p>
                        </div>
                    @endif
                </div>

                @if ($guide)
                    <div class="d-flex justify-content-between mt-4 animate__animated animate__fadeInUp animate__delay-1s">
                        <a href="{{ route('guide.index') }}" class="btn bg-3 rounded-5 text-white">
                            <i class="fas fa-arrow-left me-2"></i> Danh sách hướng dẫn
                        </a>
                        <a href="{{ route('home') }}" class="btn bg-3 rounded-5 text-white">
                            <i class="fas fa-home me-2"></i> Trang chủ
                        </a>
                    </div>
                @endif

                @if (isset($relatedGuides) && $relatedGuides->count() > 0)
                    <div class="row mt-5">
                        <div class="col-12">
                            <h4 class="mb-4 color-3">Hướng dẫn liên quan</h4>
                            <div class="row">
                                @foreach ($relatedGuides as $index => $relatedGuide)
                                    <div class="col-12 col-md-6 col-lg-4 mb-3">
                                        <a href="{{ route('guide.show', $relatedGuide->slug) }}"
                                            class="guide-item-link text-decoration-none">
                                            <div class="guide-item mb-3 animate__animated animate__fadeInUp"
                                                style="animation-delay: {{ $index * 0.05 }}s">
                                                <div class="d-flex align-items-center">
                                                    <div class="guide-number-icon me-3">
                                                        <span class="guide-number">{{ $index + 1 }}</span>
                                                    </div>
                                                    <div class="guide-item-bar flex-grow-1">
                                                        <span class="guide-item-text">{{ $relatedGuide->title }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
        .guide-content {
            line-height: 1.8;
            color: #333;
        }

        .guide-content h2 {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            color: #2c3e50;
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 0.5rem;
        }

        .guide-content h3 {
            margin-top: 1.2rem;
            color: var(--primary-color-3);
        }

        .guide-content p {
            margin-bottom: 1rem;
        }

        .guide-content ul,
        .guide-content ol {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }

        .guide-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 1.5rem 0;
        }

        .guide-content blockquote {
            background-color: var(--primary-color-6);
            border-left: 4px solid var(--primary-color-3);
            padding: 1rem;
            margin: 1.5rem 0;
            border-radius: 0 8px 8px 0;
        }

        .guide-content a {
            color: var(--primary-color-3);
            text-decoration: none;
            border-bottom: 1px dotted;
            transition: all 0.3s ease;
        }

        .guide-content a:hover {
            color: var(--primary-color-1);
            border-bottom: 1px solid;
        }

        .guide-content table {
            width: 100%;
            margin-bottom: 1.5rem;
            border-collapse: collapse;
        }

        .guide-content table th,
        .guide-content table td {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
        }

        .guide-content table th {
            background-color: var(--primary-color-6);
        }

        .guide-content .table-responsive {
            overflow-x: auto;
            margin-bottom: 1.5rem;
        }

        /* Guide Item */
        .guide-item {
            transition: transform 0.2s ease;
        }

        .guide-item:hover {
            transform: translateX(5px);
        }

        .guide-item-link {
            display: block;
        }

        .guide-item-link:hover {
            text-decoration: none;
        }

        /* Number Icon */
        .guide-number-icon {
            width: 50px;
            height: 50px;
            min-width: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color-3);
            border-radius: 8px;
            transform: rotate(-12deg);
            box-shadow: 0 4px 8px rgba(216, 107, 107, 0.3);
        }

        .guide-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            transform: rotate(12deg);
            line-height: 1;
        }

        /* Item Bar */
        .guide-item-bar {
            background: var(--primary-color-4);
            border-radius: 8px;
            padding: 12px 16px;
            min-height: 50px;
            display: flex;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .guide-item:hover .guide-item-bar {
            background: var(--primary-color-2);
        }

        .guide-item-text {
            color: var(--color-text);
            font-weight: 500;
            font-size: 0.95rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .guide-number-icon {
                width: 45px;
                height: 45px;
                min-width: 45px;
            }

            .guide-number {
                font-size: 1.3rem;
            }

            .guide-item-bar {
                padding: 10px 14px;
                min-height: 45px;
            }

            .guide-item-text {
                font-size: 0.9rem;
            }
        }

        /* Animation classes */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        .fade-in-up {
            animation: fadeInUp 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
</style>
@endpush

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize animations for elements that come into view
            const animateElements = document.querySelectorAll(
                '.guide-content h2, .guide-content img, .guide-content blockquote');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            });

            animateElements.forEach(element => {
                observer.observe(element);
            });

        });
    </script>
@endsection
