{{-- Show pinned comments first --}}
@if(isset($pinnedComments) && $pinnedComments->count() > 0)
    <div class="pinned-comments mb-3">
        @foreach($pinnedComments as $comment)
            @include('components.comments-item', ['comment' => $comment])
        @endforeach
    </div>
    
    @if(isset($regularComments) && $regularComments->count() > 0)
        <h6 class="my-3 text-muted">Bình luận khác</h6>
    @endif
@endif

{{-- Show regular comments --}}
@if(isset($regularComments))
    @foreach($regularComments as $comment)
        @include('components.comments-item', ['comment' => $comment])
    @endforeach

    @if($regularComments->count() == 0 && (!isset($pinnedComments) || $pinnedComments->count() == 0))
        <div class="text-center py-4 text-muted">
            <i class="far fa-comment-dots fa-2x mb-2"></i>
            <p>Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</p>
        </div>
    @endif
@endif