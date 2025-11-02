{{-- Show pinned comments first --}}
@if(isset($pinnedComments) && $pinnedComments->count() > 0)
    <div class="pinned-comments mb-4">
        <div class="pinned-header mb-3">
            <h6 class="pinned-title">
                <i class="fas fa-thumbtack text-warning me-2"></i> Bình luận đã ghim
            </h6>
        </div>
        @foreach($pinnedComments as $comment)
            @include('components.comments-item', ['comment' => $comment])
        @endforeach
    </div>
    
    @if(isset($regularComments) && $regularComments->count() > 0)
        <div class="regular-comments-header mb-3">
            <h6 class="text-muted">Bình luận khác</h6>
            <div class="header-line"></div>
        </div>
    @endif
@endif

{{-- Show regular comments --}}
@if(isset($regularComments))
    <div class="regular-comments-container">
        @foreach($regularComments as $comment)
            @include('components.comments-item', ['comment' => $comment])
        @endforeach

        @if($regularComments->count() == 0 && (!isset($pinnedComments) || $pinnedComments->count() == 0))
            <div class="text-center py-4 text-muted empty-comments animate__animated animate__fadeIn">
                <i class="far fa-comment-dots fa-3x mb-3"></i>
                <p>Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</p>
            </div>
        @endif
    </div>
@endif
