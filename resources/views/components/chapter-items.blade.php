<div class="row">
    {{-- Mobile View: Single Column --}}
    <div class="d-block d-md-none">
        <ul class="chapter-list text-muted">
            @foreach ($chapters as $chapter)
                <li class="mt-2">
                    <a href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug]) }}" class="text-muted">
                        <span class="date">
                            <span>{{ $chapter->created_at->format('d') }}</span>
                            <span class="fs-7">{{ $chapter->created_at->format('M') }}</span>
                        </span>
                        <span class="chapter-title ms-2">
                            Chương {{ $chapter->number }}: {{ $chapter->title }}
                            @if ($chapter->created_at->isToday())
                                <span class="new-badge">
                                    New
                                </span>
                            @endif
                        </span>
                    </a>
                    <hr class="my-2 opacity-25">
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Desktop View: Two Columns --}}
    <div class="col-6 d-none d-md-block">
        <ul class="chapter-list text-muted">
            @foreach ($chapters->take(ceil($chapters->count() / 2)) as $chapter)
                <li class="mt-2">
                    <a href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug]) }}" class="text-muted">
                        <span class="date">
                            <span>{{ $chapter->created_at->format('d') }}</span>
                            <span class="fs-7">{{ $chapter->created_at->format('M') }}</span>
                        </span>
                        <span class="chapter-title ms-2">
                            Chương {{ $chapter->number }}: {{ $chapter->title }}
                            @if ($chapter->created_at->isToday())
                                <span class="new-badge">
                                    New
                                </span>
                            @endif
                        </span>
                    </a>
                    <hr class="my-2 opacity-25">
                </li>
            @endforeach
        </ul>
    </div>
    <div class="col-6 d-none d-md-block">
        <ul class="chapter-list text-muted">
            @foreach ($chapters->skip(ceil($chapters->count() / 2)) as $chapter)
                <li class="mt-2">
                    <a href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug]) }}" class="text-muted">
                        <span class="date">
                            <span>{{ $chapter->created_at->format('d') }}</span>
                            <span class="fs-7">{{ $chapter->created_at->format('M') }}</span>
                        </span>
                        <span class="chapter-title ms-2">
                            Chương {{ $chapter->number }}: {{ $chapter->title }}
                            @if ($chapter->created_at->isToday())
                                <span class="new-badge">
                                    New
                                </span>
                            @endif
                        </span>
                    </a>
                    <hr class="my-2 opacity-25">
                </li>
            @endforeach
        </ul>
    </div>
</div>

@push('styles')
    <style>
        .chapter-card {
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .chapter-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .stats-list-chapter {
            display: flex;
            flex-direction: row;
            gap: 0.8rem;
        }

        .counter-chapter {
            font-weight: bold;
            margin-right: 5px;
            transition: all 0.3s ease-out;
        }

        .stat-item-chapter {
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
        }

        .new-badge {
            color: #ff0000;
            font-weight: bold;
            margin-left: 5px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .new-badge {
            animation: pulse 1s ease-in-out infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.7;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
@endpush
