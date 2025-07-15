@extends('layouts.app')

@section('title')
    @if (isset($isSearch) && $isSearch)
        @if (isset($searchType))
            @if ($searchType === 'author')
                Tìm truyện của tác giả: {{ $query }}
            @elseif($searchType === 'translator')
                Tìm truyện dịch bởi: {{ $query }}
            @else
                Kết quả tìm kiếm: {{ $query }}
            @endif
        @else
            Kết quả tìm kiếm: {{ $query }}
        @endif
    @elseif(isset($query) && $query === 'new-chapter')
        Truyện mới nhất
    @elseif(isset($query) && $query === 'hot')
        Truyện đề cử
    @elseif(isset($query) && $query === 'rating')
        Truyện được đánh giá cao
    @elseif(isset($query) && $query === 'view')
        Truyện được xem nhiều
    @elseif(isset($query) && $query === 'follow')
        Truyện được theo dõi nhiều
    @elseif(isset($query) && $query === 'completed')
        Truyện đã hoàn thành
    @elseif(isset($query) && $query === 'new')
        Truyện mới
    @else
        {{ $currentCategory->name }}
    @endif
@endsection

@section('description')
    @if (isset($isSearch) && $isSearch)
        @if (isset($searchType))
            @if ($searchType === 'author')
                Danh sách truyện của tác giả "{{ $query }}" tại {{ config('app.name') }}
            @elseif($searchType === 'translator')
                Danh sách truyện được dịch bởi "{{ $query }}" tại {{ config('app.name') }}
            @else
                Kết quả tìm kiếm cho "{{ $query }}" tại {{ config('app.name') }}
            @endif
        @else
            Kết quả tìm kiếm cho "{{ $query }}" tại {{ config('app.name') }}
        @endif
    @elseif(isset($query) && $query === 'new-chapter')
        Truyện mới nhất tại
    @elseif(isset($query) && $query === 'hot')
        Truyện đề cử tại
    @elseif(isset($query) && $query === 'rating')
        Truyện được đánh giá cao tại
    @elseif(isset($query) && $query === 'view')
        Truyện được xem nhiều tại
    @elseif(isset($query) && $query === 'follow')
        Truyện được theo dõi nhiều tại
    @elseif(isset($query) && $query === 'completed')
        Truyện đã hoàn thành tại
    @elseif(isset($query) && $query === 'new')
        Truyện mới tại
    @else
        Truyện thể loại {{ $currentCategory->name }} tại {{ config('app.name') }}
    @endif
@endsection

@section('content')
    <div class="mt-5 container-xl">
        <div class="row">
            <!-- Main content area (8 columns) -->
            <div class="col-12 col-md-7">
                <div class="bg-white p-3 rounded-4 shadow-sm mb-4">
                    <h2 class="h4 mb-3 fw-bold">
                        @if (isset($isSearch) && $isSearch)
                            @if (isset($searchType))
                                @if ($searchType === 'author')
                                    <i class="fa-solid fa-user-pen fa-lg text-primary"></i>
                                    Tác giả: "{{ $query }}"
                                @elseif($searchType === 'translator')
                                    <i class="fa-solid fa-language fa-lg text-success"></i>
                                    Chuyển ngữ: "{{ $query }}"
                                @else
                                    <i class="fa-solid fa-magnifying-glass fa-lg text-warning"></i>
                                    Kết quả tìm kiếm: "{{ $query }}"
                                @endif
                            @else
                                <i class="fa-solid fa-magnifying-glass fa-lg text-warning"></i>
                                Kết quả tìm kiếm: "{{ $query }}"
                            @endif
                        @elseif(isset($query) && $query === 'new-chapter')
                            <i class="fa-solid fa-layer-group fa-lg text-primary"></i>
                            Truyện mới nhất
                        @elseif(isset($query) && $query === 'hot')
                            <i class="fa-solid fa-layer-group fa-lg text-primary"></i>
                            Truyện đề cử
                        @elseif(isset($query) && $query === 'rating')
                            <i class="fa-solid fa-layer-group fa-lg text-primary"></i>
                            Truyện được đánh giá cao
                        @elseif(isset($query) && $query === 'view')
                            <i class="fa-solid fa-layer-group fa-lg text-primary"></i>
                            Truyện được xem nhiều
                        @elseif(isset($query) && $query === 'follow')
                            <i class="fa-solid fa-layer-group fa-lg text-primary"></i>
                            Truyện được theo dõi nhiều
                        @elseif(isset($query) && $query === 'completed')
                            <i class="fa-solid fa-layer-group fa-lg text-primary"></i>
                            Truyện đã hoàn thành
                        @elseif(isset($query) && $query === 'new')
                            <i class="fa-solid fa-layer-group fa-lg text-primary"></i>
                            Truyện mới
                        @else
                            <i class="fa-solid fa-layer-group fa-lg text-primary"></i>
                            Truyện thể loại: {{ $currentCategory->name }}
                        @endif
                    </h2>

                    @if ($stories->total() > 0)
                        <p class="text-muted small">Tìm thấy {{ $stories->total() }} truyện</p>
                    @else
                        <p class="text-muted">Không tìm thấy truyện phù hợp</p>
                    @endif

                    @foreach ($stories as $story)
                        <div class="story-item border-bottom pb-3 pt-3">
                            <div class="row">
                                <div class="col-4 col-sm-3 col-lg-2">
                                    <a href="{{ route('stories.show', $story) }}" class="h-100 w-100 d-inline-block">
                                        <img src="{{ Storage::url($story->cover) }}" alt="{{ $story->title }}"
                                            class="img-fluid rounded"
                                            style="width: 100%; height: 150px; object-fit: cover;">
                                    </a>
                                </div>
                                <div class="col-8 col-sm-9 col-lg-10">
                                    <h6 class="h6 mb-1">
                                        <a href="{{ route('show.page.story', $story->slug) }}"
                                            class="text-dark text-decoration-none">
                                            {{ $story->title }}
                                        </a>
                                    </h6>
                                    @if (auth()->check() && auth()->user()->role != 'user')
                                        <span class="small text-muted mt-2"><i class="fa-solid fa-user"></i> Tác giả:
                                            {{ $story->author_name }}</span>
                                    @endif

                                    <div class="d-flex flex-wrap gap-1 my-2 text-sm">
                                        @php
                                            $mainCategories = $story->categories->where('is_main', true);
                                            $subCategories = $story->categories->where('is_main', false);
                                            $displayCategories = collect();

                                            if ($mainCategories->isNotEmpty()) {
                                                foreach ($mainCategories->take(2) as $category) {
                                                    $displayCategories->push($category);
                                                }

                                                // Nếu chỉ có 1 danh mục chính, thêm 1 danh mục phụ
                                                if ($displayCategories->count() === 1 && $subCategories->isNotEmpty()) {
                                                    $displayCategories->push($subCategories->first());
                                                }
                                            } else {
                                                foreach ($subCategories->take(2) as $category) {
                                                    $displayCategories->push($category);
                                                }
                                            }
                                        @endphp


                                        @foreach ($displayCategories as $category)
                                            <span
                                                class="badge bg-1 text-white small rounded-pill d-flex align-items-center">{{ $category->name }}</span>
                                        @endforeach

                                    </div>
                                    {{-- <div class="categories mb-2">
                                        @foreach ($story->categories as $category)
                                            <a href="{{ route('categories.story.show', $category->slug) }}"
                                                class="text-decoration-none">
                                                {{ $category->name }}
                                            </a>
                                        @endforeach
                                    </div> --}}
                                    {{-- <div class="d-flex small text-muted">
                                        <div class="me-3">
                                            <i class="fas fa-book-open me-1 text-danger"></i>
                                            {{ $story->chapters_count ?? $story->chapters->count() }} chương
                                        </div>
                                        <div class="me-3">
                                            <i class="fas fa-eye me-1 text-primary"></i>
                                            {{ number_format($story->view_count) }}
                                        </div>
                                        <div>
                                            <i class="fas fa-clock me-1 text-warning"></i>
                                            {{ $story->updated_at->diffForHumans() }}
                                        </div>
                                    </div> --}}
                                    <div class="story-description mt-2 small text-muted d-none d-md-block">
                                        {{ cleanDescription($story->description, 200) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-center mt-4">
                        <x-pagination :paginator="$stories" />
                    </div>
                </div>
            </div>

            <!-- Sidebar (4 columns) - Now using the component -->
            <div class="col-12 col-md-5">

                <div class="mb-4">
                    @include('components.recent-reads')
                </div>

                <div class="mb-4">
                    <x-categories-widget :categories="$categories" :current-category="$currentCategory ?? null" :is-search="$isSearch ?? false" />
                </div>
            </div>
        </div>
    </div>
@endsection
