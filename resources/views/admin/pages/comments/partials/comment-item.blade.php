<div class="comment-card {{ isset($highlight) && $highlight ? 'border border-primary' : '' }}">
    <div class="comment-header">
        <div class="comment-user">
            @if($comment->user)
                <a href="{{ route('users.show', $comment->user->id) }}">{{ $comment->user->name }}</a>
                <span class="badge bg-{{ $comment->user->role == 'admin' ? 'danger' : ($comment->user->role == 'mod' ? 'warning' : 'info') }} text-white">
                    {{ ucfirst($comment->user->role) }}
                </span>
            @else
                <span class="text-muted">Người dùng không tồn tại</span>
            @endif
            
            @if($comment->story)
                <a href="{{ route('stories.show', $comment->story->id) }}" class="badge bg-primary story-badge">
                    {{ Str::limit($comment->story->title, 30) }}
                </a>
            @endif
        </div>
        <div class="comment-meta">
            {{ $comment->created_at->format('d/m/Y H:i') }}
        </div>
    </div>
    
    <div class="comment-content">
        @php
            $content = $comment->comment;
            $search = request('search');
            if ($search && stripos($content, $search) !== false) {
                $content = preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark class="bg-warning">$1</mark>', $content);
                $highlight = true;
            }
        @endphp
        {!! $content !!}
    </div>
    
    <div class="comment-actions d-flex justify-content-between align-items-center">
        <div>
            @if($comment->is_pinned)
                <span class="badge bg-success">Đã ghim</span>
            @endif
            
            @if($comment->replies && $comment->replies->count() > 0)
                @php
                    // Check if any replies match our search filters
                    $hasMatchingReply = false;
                    $search = request('search');
                    $userId = request('user');
                    $date = request('date');
                    
                    if ($search || $userId || $date) {
                        foreach ($comment->replies as $reply) {
                            if ($search && stripos($reply->comment, $search) !== false) {
                                $hasMatchingReply = true;
                                break;
                            }
                            
                            if ($userId && $reply->user_id == $userId) {
                                $hasMatchingReply = true;
                                break;
                            }
                            
                            if ($date && $reply->created_at->format('Y-m-d') == $date) {
                                $hasMatchingReply = true;
                                break;
                            }
                        }
                    }
                @endphp
                <button class="btn btn-link btn-sm p-0 toggle-replies" data-comment-id="{{ $comment->id }}">
                    <i class="fa-solid fa-chevron-{{ $hasMatchingReply ? 'up' : 'down' }} me-1"></i>
                    {{ $hasMatchingReply ? 'Ẩn trả lời' : 'Xem trả lời' }} ({{ $comment->replies->count() }})
                </button>
            @endif
        </div>
        
        <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-link text-danger btn-sm p-0 delete-comment-btn">
                <i class="fa-solid fa-trash"></i> Xóa
            </button>
        </form>
    </div>
</div>

@if($comment->replies && $comment->replies->count() > 0)
    @php
        // Check if any replies match our search filters
        $hasMatchingReply = false;
        $search = request('search');
        $userId = request('user');
        $date = request('date');
        
        if ($search || $userId || $date) {
            foreach ($comment->replies as $reply) {
                if ($search && stripos($reply->comment, $search) !== false) {
                    $hasMatchingReply = true;
                    break;
                }
                
                if ($userId && $reply->user_id == $userId) {
                    $hasMatchingReply = true;
                    break;
                }
                
                if ($date && $reply->created_at->format('Y-m-d') == $date) {
                    $hasMatchingReply = true;
                    break;
                }
            }
        }
    @endphp
    <div class="comment-thread comment-level-{{ $level + 1 }} replies-container-{{ $comment->id }} {{ $hasMatchingReply ? '' : 'd-none' }}">
        @foreach($comment->replies as $reply)
            @php
                $highlight = false;
                if ($search && stripos($reply->comment, $search) !== false) {
                    $highlight = true;
                }
                
                if ($userId && $reply->user_id == $userId) {
                    $highlight = true;
                }
                
                if ($date && $reply->created_at->format('Y-m-d') == $date) {
                    $highlight = true;
                }
            @endphp
            @include('admin.pages.comments.partials.comment-item', ['comment' => $reply, 'level' => $level + 1, 'highlight' => $highlight])
        @endforeach
    </div>
@endif 