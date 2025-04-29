<!-- filepath: d:\truyen\pinknovel\resources\views\components\hot_stories.blade.php -->
<!-- Hot Stories Widget -->

<div class="sidebar-widget recent-reads rounded-3 shadow-sm">
    <div class="widget-header bg-2">
        <ul class="nav nav-tabs nav-fill" id="hotStoriesTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily"
                    type="button" role="tab">
                   Hôm nay
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="weekly-tab" data-bs-toggle="tab" data-bs-target="#weekly" type="button"
                    role="tab">
                   Tuần này
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthly" type="button"
                    role="tab">
                    Tháng này
                </button>
            </li>
        </ul>
    </div>
    <div class="widget-content">
        

        <!-- Tab Content -->
        <div class="tab-content" id="hotStoriesContent">
            <!-- Daily Hot Stories -->
            <div class="tab-pane fade show active" id="daily" role="tabpanel">
                <div class="hot-stories-list">
                    @foreach ($dailyHotStories as $index => $story)
                        <div class="hot-story-item d-flex p-2 {{ $index < 9 ? 'border-bottom' : '' }}">
                            <div class="story-rank">
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
                                <!-- Latest two chapters -->
                                @php
                                    $latestChapters = $story->chapters()->published()->latest()->take(2)->get();
                                @endphp
                                @foreach($latestChapters as $chapter)
                                    <div class="badge bg-1 small rounded-pill">
                                        <a class="text-decoration-none color-3  fw-normal"
                                            href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug]) }}">
                                            {{ $chapter->title }}
                                        </a>
                                    </div>
                                    <div class="publish-date small text-muted">
                                        <i class="far fa-clock me-1"></i>{{ $chapter->created_at ? $chapter->created_at->format('d/m/Y') : '' }}
                                    </div>
                                @endforeach
                                
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
                            <div class="story-rank">
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
                                <!-- Latest two chapters -->
                                @php
                                    $latestChapters = $story->chapters()->published()->latest()->take(2)->get();
                                @endphp
                                @foreach($latestChapters as $chapter)
                                    <div class="badge bg-1 text-white small rounded-pill">
                                        <a class="text-decoration-none"
                                            href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug]) }}">
                                           {{ $chapter->title }}
                                        </a>
                                    </div>
                                @endforeach
                                <!-- Publishing date -->
                                <div class="publish-date small text-muted">
                                    <i class="far fa-clock me-1"></i>{{ $story->latestChapter ? $story->latestChapter->created_at->format('d/m/Y H:i') : '' }}
                                </div>
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
                            <div class="story-rank">
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
                                <!-- Latest two chapters -->
                                @php
                                    $latestChapters = $story->chapters()->published()->latest()->take(2)->get();
                                @endphp
                                @foreach($latestChapters as $chapter)
                                    <div class="latest-chapter small mb-1">
                                        <a class="text-decoration-none"
                                            href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug]) }}">
                                            <i class="fas fa-book-open me-1 cl-8ed7ff"></i>{{ $chapter->title }}
                                        </a>
                                    </div>
                                @endforeach
                                <!-- Publishing date -->
                                <div class="publish-date small text-muted">
                                    <i class="far fa-clock me-1"></i>{{ $story->latestChapter ? $story->latestChapter->created_at->format('d/m/Y H:i') : '' }}
                                </div>
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
                color: var(--primary-color-3);
                border: 1px solid var(--primary-color-4);
                background-color: transparent;
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

            .publish-date {
                font-size: 0.8rem;
                margin-top: 2px;
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
                color: var(--primary-color-3);
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
                background-color: var(--primary-color-3);
            }

            /* Existing responsive adjustments... */
        </style>
    @endpush
@endonce