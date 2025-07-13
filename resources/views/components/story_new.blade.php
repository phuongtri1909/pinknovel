<div class="mb-2 text-gray-600">
    <div class="story-content-wrapper">
        <span class="new-tag">NEW</span>
        <div class="story-info-section">
            <div class="story-chapter-inline">
                <span class="fw-semibold">
                    <a href="{{ route('show.page.story', $story->slug) }}" class="text-decoration-none color-hover">
                        {{ $story->title }}
                    </a>



                    <span class="chapter-separator">:</span>
                    @if ($story->latestChapter)
                        <a href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $story->latestChapter->slug]) }}"
                            class="text-decoration-none text-muted chapter-link">
                            {{ $story->latestChapter->title }}
                        </a>
                    @else
                        <span class="text-muted">Chưa cập nhật</span>
                    @endif
                </span>
            </div>
        </div>

        <div class="time-info">
            <div class="text-muted text-sm mb-1 fs-8">
                @if ($story->latestChapter)
                    {{ $story->latestChapter->created_at->diffForHumans() }}
                @else
                    Chưa cập nhật
                @endif
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .story-image {
                width: 90px;
                height: 130px;
                object-fit: cover;
                display: block;
                flex-shrink: 0;
            }

            .story-image:hover {
                transform: scale(1.05);
                transition: transform 0.3s ease;
            }

            .story-content-wrapper {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 10px;
            }

            .story-info-section {
                flex: 1;
                min-width: 0;
                /* Cho phép shrink */
            }

            .story-chapter-inline {
                line-height: 1.4;
            }

            .story-title {
                font-size: 1rem;
                margin: 0;
                display: inline;
                word-wrap: break-word;
                hyphens: auto;
                white-space: normal;
            }

            .story-title a {
                display: inline;
            }

            .chapter-separator {
                color: #666;
                margin: 0 3px;
                display: inline;
            }

            .chapter-link {
                word-wrap: break-word;
                hyphens: auto;
                line-height: inherit;
                display: inline;
            }

            .time-info {
                flex-shrink: 0;
                text-align: right;
                min-width: 80px;
            }

            /* NEW Tag với màu đỏ và animation lấp lánh */
            .new-tag {
                display: inline-block;
                background: linear-gradient(45deg, #dc2626, #ef4444, #f87171, #dc2626);
                background-size: 300% 300%;
                color: white;
                padding: 1px 6px;
                border-radius: 8px;
                font-size: 0.65rem;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.3px;
                margin-left: 4px;
                margin-right: 2px;
                position: relative;
                overflow: hidden;
                animation: redShimmer 2s infinite, redPulse 1.5s infinite alternate;
                box-shadow: 0 1px 6px rgba(220, 38, 38, 0.4);
                border: 1px solid rgba(255, 255, 255, 0.3);
                vertical-align: middle;
            }

            .new-tag::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
                animation: shine 3s infinite;
            }

            /* Animation keyframes cho màu đỏ */
            @keyframes redShimmer {
                0% {
                    background-position: 0% 50%;
                }

                50% {
                    background-position: 100% 50%;
                }

                100% {
                    background-position: 0% 50%;
                }
            }

            @keyframes redPulse {
                0% {
                    transform: scale(1);
                    box-shadow: 0 1px 6px rgba(220, 38, 38, 0.4);
                }

                100% {
                    transform: scale(1.05);
                    box-shadow: 0 2px 12px rgba(220, 38, 38, 0.6);
                }
            }

            @keyframes shine {
                0% {
                    left: -100%;
                }

                50% {
                    left: 100%;
                }

                100% {
                    left: 100%;
                }
            }

            /* Responsive */
            @media (max-width: 767.98px) {
                .story-content-wrapper {
                    flex-direction: column;
                    gap: 8px;
                }

                .time-info {
                    text-align: left;
                    min-width: auto;
                }

                .story-title {
                    font-size: 0.9rem;
                }

                .chapter-separator {
                    margin: 0 2px;
                }

                .new-tag {
                    font-size: 0.6rem;
                    padding: 1px 4px;
                    margin-left: 3px;
                    margin-right: 1px;
                }
            }

            @media (max-width: 575.98px) {
                .chapter-separator {
                    margin: 0 1px;
                }

                .new-tag {
                    font-size: 0.55rem;
                    padding: 0px 3px;
                    margin-left: 2px;
                }
            }
        </style>
    @endpush
@endonce
