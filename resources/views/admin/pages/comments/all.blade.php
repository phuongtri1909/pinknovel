@extends('admin.layouts.app')

@push('styles-admin')
<style>
    .comment-thread {
        padding-left: 20px;
        border-left: 2px solid #e9ecef;
        margin-top: 10px;
    }
    .comment-level-1 { border-left-color: #5e72e4; }
    .comment-level-2 { border-left-color: #11cdef; }
    .comment-level-3 { border-left-color: #fb6340; }
    
    .comment-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    
    .comment-card.border-primary {
        box-shadow: 0 0 0 1px #5e72e4;
        background-color: #f8fbff;
    }
    
    mark {
        padding: 0 2px;
        border-radius: 2px;
    }
    
    .comment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
    }
    
    .comment-user {
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .comment-meta {
        font-size: 0.75rem;
        color: #8898aa;
    }
    
    .comment-content {
        font-size: 0.875rem;
        margin-bottom: 5px;
    }
    
    .comment-actions {
        font-size: 0.75rem;
    }
    
    .story-badge {
        font-size: 0.7rem;
        padding: 3px 6px;
        margin-left: 5px;
    }
</style>
@endpush

@section('content-auth')
<div class="row">
    <div class="col-12">
        <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between align-items-center flex-wrap">
                    <div>
                        <h5 class="mb-0">Quản lý tất cả bình luận</h5>
                        <p class="text-sm mb-0">Tổng số: {{ $totalComments }} bình luận</p>
                    </div>
                    
                    <form method="GET" class="mt-3 d-flex flex-column flex-md-row gap-2 w-100 w-md-auto">
                        <div class="d-flex flex-column flex-md-row gap-2 mb-2 mb-md-0 w-100">
                            <select name="story" class="form-select form-select-sm">
                                <option value="">Tất cả truyện</option>
                                @foreach($stories as $story)
                                    <option value="{{ $story->id }}" {{ request('story') == $story->id ? 'selected' : '' }}>
                                        {{ $story->title }}
                                    </option>
                                @endforeach
                            </select>
                            
                            <select name="user" class="form-select form-select-sm">
                                <option value="">Tất cả người dùng</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            
                            <input type="date" name="date" 
                                   class="form-control form-control-sm" 
                                   value="{{ request('date') }}">
                                   
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" 
                                       name="search" placeholder="Nội dung..." 
                                       value="{{ request('search') }}">
                                <button class="btn bg-gradient-primary btn-sm px-2 mb-0" type="submit">
                                    <i class="fa-solid fa-search"></i>
                                </button>
                                <a href="{{ route('comments.all') }}" class="btn btn-outline-secondary btn-sm px-2 mb-0">
                                    <i class="fa-solid fa-rotate"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body px-0 pt-2 pb-2">
                @include('admin.pages.components.success-error')

                <div class="px-4">
                    @if($comments->count() > 0)
                        @foreach($comments as $comment)
                            @include('admin.pages.comments.partials.comment-item', ['comment' => $comment, 'level' => 0])
                        @endforeach
                        
                        <div class="mt-4">
                            {{ $comments->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            Không có bình luận nào phù hợp với tiêu chí tìm kiếm
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts-admin')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle visibility of replies
        document.querySelectorAll('.toggle-replies').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                const repliesContainer = document.querySelector(`.replies-container-${commentId}`);
                
                if (repliesContainer) {
                    if (repliesContainer.classList.contains('d-none')) {
                        repliesContainer.classList.remove('d-none');
                        this.innerHTML = '<i class="fa-solid fa-chevron-up me-1"></i>Ẩn trả lời';
                    } else {
                        repliesContainer.classList.add('d-none');
                        this.innerHTML = '<i class="fa-solid fa-chevron-down me-1"></i>Xem trả lời';
                    }
                }
            });
        });
        
        // Confirm delete
        document.querySelectorAll('.delete-comment-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endpush 