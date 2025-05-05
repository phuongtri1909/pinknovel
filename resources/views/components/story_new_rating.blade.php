<div class="d-flex align-items-start mb-2 text-gray-600">
    <img src="{{ Storage::url($story->cover) }}" class="story-image me-3 rounded-start" alt="{{ $story->title }}">
    <div class="flex-grow-1">
        <h2 class="fs-6 mb-1">
            <a href="{{ route('show.page.story', $story->slug) }}"
                class="text-dark text-decoration-none fw-semibold hover-color-3">{{ $story->title }}</a>
        </h2>
        
        <span class="rating-stars text-sm" title="{{ number_format($story->average_rating, 1) }} sao">
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
            {{ $rating }}
        </span>

        <div><i class="fa-solid fa-user"></i> hihihi</div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-wrap gap-1 my-2 text-sm">
                @foreach ($story->categories->take(2) as $category)
                    <span class="badge bg-1 text-white small rounded-pill d-flex align-items-center">{{ $category->name }}</span>
                @endforeach
                <p class="mb-0">{{ $story->chapters_count }} chương</p>
            </div>
    
            <div class="text-muted text-sm">
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
        </style>
    @endpush
@endonce
