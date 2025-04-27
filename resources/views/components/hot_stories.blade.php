<!-- Hot Stories Widget -->

<div class="sidebar-widget recent-reads rounded-4 shadow-sm">
    <div class="widget-header">
        <h2 class="fs-5 m-0 text-dark">
            <i class="fas fa-fire text-danger me-2"></i> Nổi bật
        </h2>
    </div>
    <div class="widget-content">
        <ul class="nav nav-tabs nav-fill" id="hotStoriesTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily"
                    type="button" role="tab">
                    <i class="fas fa-sun me-1"></i> Ngày
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="weekly-tab" data-bs-toggle="tab" data-bs-target="#weekly" type="button"
                    role="tab">
                    <i class="fas fa-calendar-week me-1"></i> Tuần
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthly" type="button"
                    role="tab">
                    <i class="fas fa-calendar-alt me-1"></i> Tháng
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="hotStoriesContent">
            <!-- Daily Hot Stories -->
            <div class="tab-pane fade show active" id="daily" role="tabpanel">
                <div class="hot-stories-list">
                    @foreach ($dailyHotStories as $index => $story)
                        <div class="hot-story-item d-flex p-2 {{ $index < 9 ? 'border-bottom' : '' }}">
                            <div class="story-rank {{ $index < 3 ? 'top-rank top-' . ($index + 1) : '' }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="story-cover me-2">
                                <a class="text-decoration-none" href="{{ route('show.page.story', $story->slug) }}">
                                    <img src="{{ asset('storage/' . $story->cover) }}" alt="{{ $story->title }}"
                                        class="hot-story-thumb">
                                </a>
                            </div>
                            <div class="story-info flex-grow-1">
                                <h4 class="hot-story-title">
                                    <a class="text-decoration-none text-dark" href="{{ route('show.page.story', $story->slug) }}">{{ $story->title }}</a>
                                </h4>
                                <div class=" text-start">
                                    @foreach ($story->categories as $category)
                                        <a  href="{{ route('categories.story.show', $category->slug) }}"
                                            class="category-tag text-decoration-none">{{ $category->name }}</a>
                                    @endforeach
                                </div>
                                @if ($story->latestChapter)
                                    <div class="latest-chapter small mt-1">
                                        <a class="text-decoration-none"
                                            href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $story->latestChapter->slug]) }}">
                                            <i class="fas fa-book-open me-1 cl-8ed7ff"></i>{{ $story->latestChapter->title }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Weekly Hot Stories -->
            <div class="tab-pane fade" id="weekly" role="tabpanel">
                <div class="hot-stories-list">
                    @foreach ($weeklyHotStories as $index => $story)
                        <div class="hot-story-item d-flex p-2 {{ $index < 9 ? 'border-bottom' : '' }}">
                            <div class="story-rank {{ $index < 3 ? 'top-rank top-' . ($index + 1) : '' }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="story-cover me-2">
                                <a class="text-decoration-none" href="{{ route('show.page.story', $story->slug) }}">
                                    <img src="{{ asset('storage/' . $story->cover) }}" alt="{{ $story->title }}"
                                        class="hot-story-thumb">
                                </a>
                            </div>
                            <div class="story-info flex-grow-1">
                                <h4 class="hot-story-title">
                                    <a class="text-decoration-none text-dark" href="{{ route('show.page.story', $story->slug) }}">{{ $story->title }}</a>
                                </h4>
                                <div class="">
                                    @foreach ($story->categories as $category)
                                        <a href="{{ route('categories.story.show', $category->slug) }}"
                                            class="category-tag text-decoration-none">{{ $category->name }}</a>
                                    @endforeach
                                </div>
                                @if ($story->latestChapter)
                                    <div class="latest-chapter small mt-1">
                                        <a
                                            href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $story->latestChapter->slug]) }}">
                                            <i class="fas fa-book-open me-1 cl-8ed7ff"></i>{{ $story->latestChapter->title }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Monthly Hot Stories -->
            <div class="tab-pane fade" id="monthly" role="tabpanel">
                <div class="hot-stories-list">
                    @foreach ($monthlyHotStories as $index => $story)
                        <div class="hot-story-item d-flex p-2 {{ $index < 9 ? 'border-bottom' : '' }}">
                            <div class="story-rank {{ $index < 3 ? 'top-rank top-' . ($index + 1) : '' }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="story-cover me-2">
                                <a href="{{ route('show.page.story', $story->slug) }}">
                                    <img src="{{ asset('storage/' . $story->cover) }}" alt="{{ $story->title }}"
                                        class="hot-story-thumb">
                                </a>
                            </div>
                            <div class="story-info flex-grow-1">
                                <h4 class="hot-story-title">
                                    <a class="text-decoration-none text-dark" href="{{ route('show.page.story', $story->slug) }}">{{ $story->title }}</a>
                                </h4>
                                <div class="">
                                    @foreach ($story->categories as $category)
                                        <a href="{{ route('categories.story.show', $category->slug) }}"
                                            class="category-tag text-decoration-none">{{ $category->name }}</a>
                                    @endforeach
                                </div>
                                @if ($story->latestChapter)
                                    <div class="latest-chapter small mt-1">
                                        <a
                                            href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $story->latestChapter->slug]) }}">
                                            <i class="fas fa-book-open me-1 cl-8ed7ff"></i>{{ $story->latestChapter->title }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>


@once
    @push('styles')
        <style>
            /* Existing styles... */

            /* Hot Stories Styles */
            .hot-stories-list {
                max-height: 600px;
                overflow-y: auto;
            }

            .hot-story-item {
                transition: background-color 0.2s;
            }

            .hot-story-item:hover {
                background-color: rgba(0, 0, 0, 0.03);
            }

            /* Fixed story rank styles */
            .story-rank {
                min-width: 30px;
                min-height: 30px;
                width: 30px;
                height: 30px;
                flex: 0 0 30px; /* Prevents shrinking */
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                border-radius: 4px;
                margin-right: 10px;
                color: #777;
                border: 1px solid #ddd;
                background-color: transparent;
            }

            .top-rank {
                color: white;
                border: none; /* Remove border for top ranks */
            }

            .top-1 {
                background-color: #FFD700;
                /* Gold */
            }

            .top-2 {
                background-color: #C0C0C0;
                /* Silver */
            }

            .top-3 {
                background-color: #CD7F32;
                /* Bronze */
            }

            .hot-story-thumb {
                width: 60px;
                height: 80px;
                object-fit: cover;
                border-radius: 4px;
            }

            .hot-story-title {
                font-size: 0.9rem;
                margin-bottom: 5px;
                line-height: 1.3;
                max-height: 2.6rem;
                overflow: hidden;
            }

            .latest-chapter a {
                color: #666;
                text-decoration: none;
            }

            .latest-chapter a:hover {
                color: #007bff;
            }

            /* Style the tab nav */
            #hotStoriesTabs .nav-link {
                padding: 0.5rem;
                font-size: 0.9rem;
                font-weight: 500;
                color: #555;
                border-top: none;
            }

            #hotStoriesTabs .nav-link.active {
                color: #ff5722;
                border-color: #dee2e6 #dee2e6 #fff;
                position: relative;
            }

            #hotStoriesTabs .nav-link.active::after {
                content: '';
                position: absolute;
                bottom: -1px;
                left: 0;
                right: 0;
                height: 2px;
                background-color: #ff5722;
            }

            /* Existing responsive adjustments... */
        </style>
    @endpush
@endonce
