<section id="info-book-home">
    <div class=" mt-3">
        <div class="info-card-home h-100">
            <div class="p-2 row">
                <div class="col-12 col-md-3 col-xl-2 col-xxl-2 d-flex flex-column mb-3 mb-md-0 ">
                    <div class="rounded-4 shadow">
                        <img src="{{ Storage::url($story->cover) }}" alt="{{ $story->title }}" class="img-fluid img-book">
                    </div>
                    <div class="story-categories mb-3">
                        <p class="mb-2 text-start">Thể loại:</p>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($storyCategories as $category)
                                <a href="{{ route('categories.story.show', $category['slug']) }}"
                                    class="category-tag fs-9">
                                    {{ $category['name'] }}

                                </a>
                            @endforeach
                        </div>
                    </div>
                    <div class="stat-item text-dark mt-2 d-flex">
                        <p class="text-start mb-0 me-2">Trạng thái:</p>
                        @if ($status->status == 'done')
                            <span class="text-success fw-bold">Hoàn Thành</span>
                        @else
                            <span class="text-primary fw-bold">Đang viết</span>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-9 col-xl-10 col-xxl-8">
                    <div class="mb-3 text-start">
                        <h2 class="fw-semibold border-bottom">{{ $story->title }}</h2>
                    </div>

                    <div class="d-flex justify-content-start gap-3">
                        <div class="stat-item text-dark">
                            <i class="fas fa-book-open me-1 cl-8ed7ff"></i>
                            <span class="counter" data-target="{{ $stats['total_chapters'] }}">0</span>
                            <span>Chương</span>
                        </div>
                        <div class="stat-item text-dark">
                            <i class="fas fa-eye eye text-primary"></i>
                            <span class="counter" data-target="{{ $stats['total_views'] }}">0</span>
                            <span>Lượt Xem</span>
                        </div>
                        <div class="stat-item text-dark">
                            <i class="fas fa-star star cl-ffe371"></i>
                            <span class="counter" data-target="{{ $stats['ratings']['count'] }}">0</span>
                            <span>đánh giá</span>
                        </div>

                    </div>
                    <div>
                        <div class="description-container">
                            <div class="description-content text-muted mt-4 mb-0 text-justify"
                                id="description-content-{{ $story->id }}">
                                {!! $story->description !!}
                            </div>
                            <div class="description-toggle-btn mt-2 text-center d-none">
                                <button class="btn btn-sm btn-link show-more-btn">Xem thêm <i
                                        class="fas fa-chevron-down"></i></button>
                                <button class="btn btn-sm btn-link show-less-btn d-none">Thu gọn <i
                                        class="fas fa-chevron-up"></i></button>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-12 col-md-12 mt-3 col-xl-12 col-xxl-2 mt-lg-0 ">
                    <div class="info-card bg-white p-3 shadow">
                        <h6 class="info-title text-dark">ĐÁNH GIÁ</h6>
                        <div class="rating">
                            @php
                                // Get user's rating for this story if they're logged in
                                $userRating = 0;
                                if (auth()->check()) {
                                    $existingRating = \App\Models\Rating::where('user_id', auth()->id())
                                        ->where('story_id', $story->id)
                                        ->first();
                                    $userRating = $existingRating ? $existingRating->rating : 0;
                                }
                                $fullStars = floor($userRating);
                            @endphp

                            <div class="stars-container">
                                <div class="stars" id="rating-stars" data-story-id="{{ $story->id }}">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star rating-star {{ $i <= $fullStars ? 'full' : 'empty' }}"
                                            data-rating="{{ $i }}"></i>
                                    @endfor
                                </div>
                                <div id="rating-message">

                                </div>

                                @if (!auth()->check())
                                    <div class="rating-login-message mt-2 text-muted small">
                                        <a href="{{ route('login') }}">Đăng nhập</a> để đánh giá truyện!
                                    </div>
                                @endif
                            </div>

                            <hr class="my-2">

                            <div class="rating-stats">
                                <div class="mt-1">
                                    <span class="rating-number">Tổng: </span>
                                    <span
                                        id="average-rating">{{ number_format($stats['ratings']['average'], 1) }}</span>/5
                                    (<span id="ratings-count">{{ $stats['ratings']['count'] }}</span> đánh giá)
                                </div>
                                @if (auth()->check() && $userRating > 0)
                                    <div class="mt-1 small text-muted">
                                        Đánh giá của bạn: <span id="user-rating">{{ $userRating }}</span>/5
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
</section>
@push('styles')
    <style>
        .story-categories {
            margin: 1rem 0;
        }

        .category-tag {
            display: inline-block;
            padding: 0.2rem 0.2rem;
            background: rgba(67, 80, 255, 0.1);
            color: #e26dfa;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .category-tag:hover {
            background: #f5a3ff;
            color: white;
            transform: translateY(-2px);
        }

        .category-count {
            font-size: 0.75rem;
            opacity: 0.8;
            margin-left: 4px;
        }

        .category-tag:hover .category-count {
            opacity: 1;
        }

        /*  */
        .info-card-home {
            background: #dcdcdc;
            border-radius: 1rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .text-justify {
            text-align: justify;
            text-justify: inter-word;
            word-break: normal;
            line-height: 1.8;
            margin-bottom: 1rem;
            hyphens: auto;
        }

        .img-book {
            transition: transform 0.3s ease;
            width: 100%;
            height: 100%;
            object-fit: cover;

        }

        .img-book:hover {
            transform: scale(1.2);
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .rounded-4 {
                max-width: 200px;
                /* Smaller width for tablets */
            }
        }

        @media (max-width: 576px) {
            .rounded-4 {
                max-width: 150px;
                /* Even smaller for mobile */
            }
        }

        .shadow {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }

        .stars {
            display: flex;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .rating-star {
            margin: 0 2px;
            transition: all 0.2s ease;
        }

        .rating-star.empty {
            color: #e0e0e0;
        }

        .rating-star.full {
            color: #ffe371;
        }

        .rating-star.hover {
            color: #ffe371;
            transform: scale(1.2);
        }

        .rating-loading {
            font-size: 0.8rem;
            margin-top: 8px;
            color: #6c757d;
        }

        .rating-success,
        .rating-error {
            animation: fadeIn 0.3s ease;
        }

        .stars-container {
            position: relative;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .rating-stats {
            font-size: 0.9rem;
        }

        #average-rating {
            font-weight: bold;
            color: #ffe371;
        }

        .description-content {
            max-height: 180px;
            /* Approx height for 10 lines - adjust as needed */
            overflow: hidden;
            position: relative;
            transition: max-height 0.5s ease;
        }

        .description-content.expanded {
            max-height: 5000px;
            /* Large enough to contain any description */
        }

        .description-content:not(.expanded)::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 50px;
            background: linear-gradient(transparent, #dcdcdc);
            pointer-events: none;
        }

        .description-toggle-btn .btn-link {
            color: #4350ff;
            text-decoration: none;
            padding: 5px 15px;
            border-radius: 15px;
            background-color: rgba(67, 80, 255, 0.1);
            transition: all 0.3s ease;
        }

        .description-toggle-btn .btn-link:hover {
            background-color: rgba(67, 80, 255, 0.2);
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Description show more/less functionality
        function initDescriptionToggle() {
            const descriptionContent = document.getElementById('description-content-{{ $story->id }}');
            const toggleBtnContainer = document.querySelector('.description-toggle-btn');
            const showMoreBtn = document.querySelector('.show-more-btn');
            const showLessBtn = document.querySelector('.show-less-btn');

            if (descriptionContent && toggleBtnContainer) {
                // Check if content height exceeds the max-height
                if (descriptionContent.scrollHeight > descriptionContent.offsetHeight) {
                    // Content is taller than the container, show the toggle button
                    toggleBtnContainer.classList.remove('d-none');

                    showMoreBtn.addEventListener('click', function() {
                        descriptionContent.classList.add('expanded');
                        showMoreBtn.classList.add('d-none');
                        showLessBtn.classList.remove('d-none');
                    });

                    showLessBtn.addEventListener('click', function() {
                        descriptionContent.classList.remove('expanded');
                        showLessBtn.classList.add('d-none');
                        showMoreBtn.classList.remove('d-none');

                        // Scroll back to start of description
                        descriptionContent.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    });
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Add to your existing DOMContentLoaded code
            initDescriptionToggle();

            // Your existing code continues below...
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ratingStars = document.querySelectorAll('.rating-star');
            const starsContainer = document.getElementById('rating-stars');
            const storyId = starsContainer ? starsContainer.getAttribute('data-story-id') : null;
            const ratingMessage = document.getElementById('rating-message');

            // Only set up rating functionality if we have stars and a story ID
            if (ratingStars.length > 0 && storyId) {
                // Star hover effect
                ratingStars.forEach(star => {
                    star.addEventListener('mouseover', function() {
                        const rating = parseInt(this.getAttribute('data-rating'));
                        highlightStars(rating);
                    });

                    star.addEventListener('click', function() {
                        @if (auth()->check())
                            const rating = parseInt(this.getAttribute('data-rating'));
                            submitRating(rating);
                        @else
                            window.location.href = "{{ route('login') }}";
                        @endif
                    });
                });

                // Reset stars on mouse out of the container
                starsContainer.addEventListener('mouseout', function() {
                    resetStars();
                });

                // Function to highlight stars up to a certain rating
                function highlightStars(rating) {
                    ratingStars.forEach(star => {
                        const starRating = parseInt(star.getAttribute('data-rating'));
                        if (starRating <= rating) {
                            star.classList.add('hover');
                            star.classList.remove('empty');
                        } else {
                            star.classList.remove('hover');
                            star.classList.remove('full');
                            star.classList.add('empty');
                        }
                    });
                }

                // Function to reset stars to their original state
                function resetStars() {
                    const userRating = {{ $userRating ?? 0 }};
                    ratingStars.forEach(star => {
                        star.classList.remove('hover');
                        const starRating = parseInt(star.getAttribute('data-rating'));
                        if (starRating <= userRating) {
                            star.classList.add('full');
                            star.classList.remove('empty');
                        } else {
                            star.classList.remove('full');
                            star.classList.add('empty');
                        }
                    });
                }

                // Function to submit the rating via AJAX
                function submitRating(rating) {
                    // Remove any existing loading indicator first (to avoid duplicates)
                    const existingIndicator = ratingMessage.querySelector('.rating-loading');
                    if (existingIndicator) {
                        ratingMessage.removeChild(existingIndicator);
                    }

                    // Create a loading indicator
                    const loadingIndicator = document.createElement('div');
                    loadingIndicator.className = 'rating-loading';
                    loadingIndicator.textContent = 'Đang gửi...';
                    ratingMessage.appendChild(loadingIndicator);

                    // Disable stars during submission
                    ratingStars.forEach(star => {
                        star.style.pointerEvents = 'none';
                    });

                    // Send the AJAX request
                    fetch("{{ route('ratings.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                story_id: storyId,
                                rating: rating
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Remove loading indicator
                            ratingMessage.removeChild(loadingIndicator);

                            // Re-enable stars
                            ratingStars.forEach(star => {
                                star.style.pointerEvents = 'auto';
                            });

                            if (data.success) {
                                // Update UI with new values
                                document.getElementById('average-rating').textContent = data.average;
                                document.getElementById('ratings-count').textContent = data.count;

                                // Update user rating display
                                let userRatingElement = document.getElementById('user-rating');
                                if (!userRatingElement) {
                                    // Create the user rating element if it doesn't exist
                                    const ratingStats = document.querySelector('.rating-stats');
                                    const userRatingDiv = document.createElement('div');
                                    userRatingDiv.className = 'mt-1 small text-muted';
                                    userRatingDiv.innerHTML = 'Đánh giá của bạn: <span id="user-rating">' + data
                                        .user_rating + '</span>/5';
                                    ratingStats.appendChild(userRatingDiv);
                                } else {
                                    userRatingElement.textContent = data.user_rating;
                                }

                                // Show success message using showToast
                                showToast(data.message, 'success');

                                // Update the active stars
                                ratingStars.forEach(star => {
                                    const starRating = parseInt(star.getAttribute('data-rating'));
                                    if (starRating <= data.user_rating) {
                                        star.classList.add('full');
                                        star.classList.remove('empty');
                                    } else {
                                        star.classList.remove('full');
                                        star.classList.add('empty');
                                    }
                                });
                            } else {
                                // Show error message using showToast
                                showToast(data.message || 'Có lỗi xảy ra', 'error');
                            }
                        })
                        .catch(error => {
                            // Remove loading indicator
                            ratingMessage.removeChild(loadingIndicator);

                            // Re-enable stars
                            ratingStars.forEach(star => {
                                star.style.pointerEvents = 'auto';
                            });

                            // Show error message using showToast
                            showToast('Đã xảy ra lỗi khi gửi đánh giá', 'error');

                            console.error('Error submitting rating:', error);
                        });
                }
            }
        });
    </script>
@endpush
