<div class="row">
    <div class="col-6">
        <a href="{{ route('show.page.story', $story->slug) }}">
            <img src="{{ $story->cover ? Storage::url($story->cover) : asset('assets/images/story_default.jpg') }}"
                alt="{{ $story->title }}" class="img-fluid rounded-3 image-story-full-item">
        </a>
    </div>
    <div class="col-6">
        <div>
            <h5 class="story-title mb-0 text-sm fw-semibold lh-base ">
                <a href="{{ route('show.page.story', $story->slug) }}" class="text-decoration-none color-3">
                    {{ $story->title }}
                </a>

            </h5>
            <p class="text-ssm text-gray-600">{{ $story->latestChapter->created_at->format('d/m/Y') }}</p>
        </div>
        <div class="d-flex align-items-start flex-column">

            @php
                $latestChapters = $story->chapters()->published()->latest()->take(2)->get();
            @endphp
            @foreach ($latestChapters as $chapter)
                <div class="badge-custom-full badge small rounded-pill mt-1 border-1 border">
                    <a class="text-decoration-none color-3  fw-semibold"
                        href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug]) }}">
                        {{ $chapter->title }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('styles')
    <style>
        .image-story-full-item {
            width: 125px;
            height: 180px;
            object-fit: cover;
        }

        .badge-custom-full {
            border-color: #c2c2c2 !important;
            transition: all 0.3s ease;
        }

        .badge-custom-full:hover {
            background-color: var(--primary-color-3);
        }

        .badge-custom-full:hover a {
            color: white !important;
        }
    </style>
@endpush
