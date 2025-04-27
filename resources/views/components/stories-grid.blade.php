<div class="row g-2">
    @forelse ($hotStories as $story)
        <div class="col-6 col-md-3 col-lg-2 story-item bg-none my-0">
            <div class="story-card">
                <div class="story-thumbnail">
                    <a href="{{ route('show.page.story', $story->slug) }}">
                        <img src="{{ $story->cover ? Storage::url($story->cover) : asset('assets/images/story_default.jpg') }}"
                            alt="{{ $story->title }}" class="img-fluid">
                        <div class="story-hover">
                            <div class="hover-content">
                                <p class="mb-2">Số chương: {{ $story->chapters_count }}</p>
                                <div class="story-categories mb-0">
                                    @foreach ($story->categories as $category)
                                        <span class="category-badge">{{ $category->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="story-info">
                    @if ($story->completed === 1)
                        <span class="badge rounded-pill bg-danger text-white">
                            <i class="fas fa-check-circle cl-53e09f"></i> Full
                        </span>
                    @else
                        <span class="badge rounded-pill bg-ffe371 text-dark">
                            <i class="fas fa-circle text-white"></i> Waiting
                        </span>
                    @endif

                    <h3 class="story-title mt-2">
                        <a class="text-dark" href="{{ route('show.page.story', $story->slug) }}"
                            title="{{ $story->title }}">
                            {{ $story->title }}
                        </a>
                    </h3>
                    <div class="story-stats-container mt-2 mb-0">
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-eye eye text-primary"></i>
                                {{ number_format($story->total_views) }}</span>
                            <span><i class="fas fa-star star cl-ffe371"></i>
                                {{ number_format($story->average_rating, 1) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center py-4 mb-4">
                <i class="fas fa-book-open fa-2x mb-3 text-muted"></i>
                <h5 class="mb-1">Không tìm thấy truyện nào</h5>
                <p class="text-muted mb-0">Hiện không có truyện nào trong danh mục này.</p>
            </div>
        </div>
    @endforelse
</div>

@push('styles')
    <style>

    </style>
@endpush
