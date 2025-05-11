@extends('layouts.information')

@section('info_title', 'Truyện đã lưu')
@section('info_description', 'Danh sách truyện bạn đã lưu trên ' . request()->getHost())
@section('info_keyword', 'Truyện đã lưu, bookmark, theo dõi truyện, ' . request()->getHost())
@section('info_section_title', 'Truyện đã lưu')
@section('info_section_desc', 'Quản lý các truyện bạn đang theo dõi')

@section('info_content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">Danh sách theo dõi</h5>
        <div class="dropdown">
            <button class="btn btn-sm action-btn-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-sort me-1"></i> Sắp xếp
            </button>
            <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                <li><a class="dropdown-item sort-option" data-sort="newest" href="#"><i class="fas fa-calendar-alt me-2"></i> Mới nhất</a></li>
                <li><a class="dropdown-item sort-option" data-sort="oldest" href="#"><i class="fas fa-calendar me-2"></i> Cũ nhất</a></li>
                <li><a class="dropdown-item sort-option" data-sort="az" href="#"><i class="fas fa-sort-alpha-down me-2"></i> A-Z</a></li>
            </ul>
        </div>
    </div>
    
    <div class="bookmarks-list">
        @if(count($bookmarks ?? []) > 0)
            @foreach($bookmarks as $key => $bookmark)
                <div class="bookmark-item" data-date="{{ $bookmark->created_at->timestamp }}" data-delay="{{ $key }}">
                    <div class="d-flex">
                        <div class="story-thumb-container me-3">
                            <img src="{{ Storage::url($bookmark->story->cover) }}" alt="{{ $bookmark->story->title }}" class="story-thumb">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="story-title">
                                        <a href="{{ route('show.page.story', $bookmark->story->slug) }}">
                                            {{ $bookmark->story->title }}
                                        </a>
                                    </h6>
                                    <span class="status-badge {{ $bookmark->story->completed ? 'status-completed' : 'status-ongoing' }}">
                                        <i class="fas {{ $bookmark->story->completed ? 'fa-check-circle' : 'fa-spinner fa-spin' }} me-1"></i>
                                        {{ $bookmark->story->completed ? 'Hoàn thành' : 'Đang ra' }}
                                    </span>
                                </div>
                                <button class="btn btn-sm delete-btn remove-bookmark" data-id="{{ $bookmark->id }}">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                            
                            <div class="row align-items-center mb-2">
                                <div class="col">
                                    <div class="story-meta">
                                        <i class="fas fa-list me-1"></i> {{ $bookmark->story->chapters_count ?? 0 }} chương
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-eye me-1"></i> {{ number_format($bookmark->story->views ?? 0) }} lượt xem
                                    </div>
                                </div>
                            </div>
                            
                            <p class="mb-3 small text-muted">
                                {{ Str::limit($bookmark->story->short_description ?? 'Không có mô tả.', 100) }}
                            </p>
                            
                            <div class="d-flex flex-wrap">
                                <a href="{{ route('show.page.story', $bookmark->story->slug) }}" class="btn btn-sm action-btn-primary story-action-btn me-2 mb-2 mb-sm-0">
                                    <i class="fas fa-book-open me-1"></i> Đọc tiếp
                                </a>
                                @if($bookmark->story->latestChapter)
                                    <a href="{{ route('chapter', [$bookmark->story->slug, $bookmark->story->latestChapter->slug]) }}" 
                                       class="btn btn-sm action-btn-secondary story-action-btn">
                                        <i class="fas fa-arrow-right me-1"></i> Chương mới nhất
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="empty-state">
                <i class="fa-solid fa-bookmark empty-icon"></i>
                <p class="empty-text">Bạn chưa lưu truyện nào.</p>
                <a href="{{ route('home') }}" class="btn discover-btn">Khám phá truyện ngay</a>
            </div>
        @endif
    </div>
@endsection

@push('info_scripts')
    <script>
        $(document).ready(function() {
            // Hiệu ứng hiện dần các item
            $('.bookmark-item').each(function(index) {
                $(this).css({
                    'opacity': '0',
                    'transform': 'translateY(20px)'
                }).delay(100 * index).animate({
                    'opacity': '1',
                    'transform': 'translateY(0)'
                }, 300);
            });
            
            // Handle remove bookmark
            $('.remove-bookmark').on('click', function() {
                const bookmarkId = $(this).data('id');
                const bookmarkItem = $(this).closest('.bookmark-item');
                
                Swal.fire({
                    title: 'Xóa truyện khỏi danh sách?',
                    text: 'Bạn có chắc muốn xóa truyện này khỏi danh sách theo dõi?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('user.bookmark.remove') }}",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                bookmark_id: bookmarkId
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    bookmarkItem.fadeOut(300, function() {
                                        $(this).remove();
                                        
                                        if ($('.bookmark-item').length === 0) {
                                            $('.bookmarks-list').html(`
                                                <div class="empty-state">
                                                    <i class="fa-solid fa-bookmark empty-icon"></i>
                                                    <p class="empty-text">Bạn chưa lưu truyện nào.</p>
                                                    <a href="{{ route('home') }}" class="btn discover-btn">Khám phá truyện ngay</a>
                                                </div>
                                            `);
                                        }
                                    });
                                    showToast(response.message, 'success');
                                } else {
                                    showToast(response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                showToast('Đã xảy ra lỗi, vui lòng thử lại.', 'error');
                            }
                        });
                    }
                });
            });
            
            // Handle sorting
            $('.sort-option').on('click', function(e) {
                e.preventDefault();
                const sortType = $(this).data('sort');
                const bookmarkItems = $('.bookmark-item').get();
                
                bookmarkItems.sort(function(a, b) {
                    if (sortType === 'newest') {
                        return $(b).data('date') - $(a).data('date');
                    } else if (sortType === 'oldest') {
                        return $(a).data('date') - $(b).data('date');
                    } else if (sortType === 'az') {
                        const titleA = $(a).find('h6 a').text().trim().toLowerCase();
                        const titleB = $(b).find('h6 a').text().trim().toLowerCase();
                        return titleA.localeCompare(titleB);
                    }
                });
                
                const bookmarksList = $('.bookmarks-list');
                $.each(bookmarkItems, function(i, item) {
                    bookmarksList.append(item);
                });
                
                // Update UI to show current sort
                $('#sortDropdown').html('<i class="fas fa-sort me-1"></i> ' + $(this).text().trim());
            });
        });
    </script>
@endpush