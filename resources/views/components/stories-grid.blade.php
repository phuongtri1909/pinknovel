<div class="story-card bg-white rounded">
    <div class="story-thumbnail">
        <a href="{{ route('show.page.story', $story->slug) }}">
            <img src="{{ $story->cover ? Storage::url($story->cover) : asset('assets/images/story_default.jpg') }}"
                alt="{{ $story->title }}" class="img-fluid">
        </a>
    </div>
    <div class="story-info px-2 text-sm text-gray-600 fw-semibold">
        <div>
            @if ($story->completed === 1)
                <span class="badge rounded-pill bg-danger text-white"> Full
                </span>
            @else
                <span class="badge rounded-pill bg-ffe371 text-dark">
                    Waiting
                </span>
            @endif

            <h5 class="story-title mb-0 text-sm fw-semibold lh-base">
                <a href="{{ route('show.page.story', $story->slug) }}" class="text-decoration-none text-dark">
                    {{ $story->title }}
                </a>
            </h5>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-1">
            <p class="mb-0">{{ $story->chapters_count }} chương</p>

            <span class="rating-stars d-none d-md-block" title="{{ number_format($story->average_rating, 1) }} sao">
                @php
                    $rating = $story->average_rating ?? 0;

                    $displayRating = round($rating * 2) / 2;
                @endphp
                @for ($i = 1; $i <= 5; $i++)
                    @if ($displayRating >= $i)
                        <i class="fas fa-star"></i>
                    @elseif ($displayRating >= $i - 0.5)
                        <i class="fas fa-star-half-alt"></i>
                    @else
                        <i class="far fa-star"></i>
                    @endif
                @endfor
            </span>
            <span class="d-flex align-items-center d-block d-md-none">
                <i class="fas fa-star star cl-ffe371 "></i>
                {{ number_format($story->average_rating, 1) }}
            </span>
        </div>
        <div class="story-stats-container mb-2 mt-1">
            <div class="d-flex justify-content-between">
                <span>
                    <i class="fas fa-eye eye"></i>
                    {{ format_number_short($story->total_views) }}
                </span>
                <span title="{{ $story->latestPublishedChapter?->created_at?->format('d/m/Y H:i') }}">
                    {{ time_elapsed_string($story->latestChapter?->created_at) }}
                </span>

            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
.story-thumbnail {
            position: relative;
            padding-top: 110%;
            overflow: hidden;
            border-top-left-radius: inherit;
            border-top-right-radius: inherit;
        }

        .story-thumbnail img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }


        .hover-content {
            color: white;
            text-align: center;
            transform: translateY(10px);
            transition: transform 0.3s ease;
        }

        .story-card {
            overflow: hidden;
            /* Keep this */
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            /* Ensure position is relative for z-index */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1;
            /* Default z-index */
        }

        .story-card:hover {
            /* Add hover state for the card */
            z-index: 10;
            /* Bring hovered card to the front */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            /* Optional: enhance shadow on hover */
        }

        .story-card:hover .story-thumbnail img {
            transform: scale(1.05);
        }

        .story-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            justify-content: center;
            margin-top: 8px;
        }

        .story-title {
            height: 3em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .category-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            backdrop-filter: blur(4px);
            transition: all 0.3s ease;
        }

        .category-badge:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
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
