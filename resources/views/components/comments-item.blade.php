@php
    $level = $comment->level ?? 0;
    $isPinned = $comment->is_pinned ?? false;
@endphp

<li class="clearfix d-flex {{ $isPinned ? 'pinned-comment' : '' }}" id="comment-{{ $comment->id }}">
    <img src="{{ $comment->user && $comment->user->avatar ? asset($comment->user->avatar) : asset('assets/images/avatar_default.jpg') }}"
        class="{{ $level > 0 ? 'avatar-reply' : 'avatar' }}"
        alt="{{ $comment->user ? $comment->user->name : 'Người dùng không tồn tại' }}">
    <div class="post-comments p-2 p-md-3">
        <div class="content-post-comments">
            <p class="meta mb-2">

                <a class="fw-bold ms-2 text-decoration-none" target="_blank">
                    @if ($comment->user)
                        @if ($comment->user->role === 'admin')
                            <span class="role-badge admin-badge">
                                @if (auth()->check() && auth()->user()->role === 'admin')
                                    <a href="{{ route('users.show', $comment->user->id) }}" target="_blank"
                                        class="text-decoration-none admin-badge">
                                        [ADMIN] {{ $comment->user->name }}
                                    </a>
                                @else
                                    [ADMIN] {{ $comment->user->name }}
                                @endif
                            </span>
                        @elseif($comment->user->role === 'mod')
                            <span class="role-badge mod-badge">
                                @if (auth()->check() && auth()->user()->role === 'admin')
                                    <a href="{{ route('users.show', $comment->user->id) }}" target="_blank"
                                        class="text-decoration-none mod-badge">
                                        [MOD] {{ $comment->user->name }}
                                    </a>
                                @else
                                    [MOD] {{ $comment->user->name }}
                                @endif
                            </span>
                        @else
                            @if (auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role === 'mod'))
                                <a href="{{ route('users.show', $comment->user->id) }}" target="_blank"
                                    class="text-decoration-none text-dark">
                                    {{ $comment->user->name }}
                                </a>
                            @else
                                <span class="text-dark">{{ $comment->user->name }}</span>
                            @endif
                        @endif
                    @else
                        <span>Người dùng không tồn tại</span>
                    @endif
                </a>

                @if ($isPinned)
                    <span class="pinned-badge ms-2">
                        <i class="fas fa-thumbtack"></i> Đã ghim
                    </span>
                @endif

                @if ($level < 2 && auth()->check())
                    <span class="pull-right">
                        <small class="reply-btn text-decoration-underline" style="cursor: pointer;"
                            data-id="{{ $comment->id }}">
                            Trả lời
                        </small>
                    </span>
                @endif

                @if (auth()->check())
                    @if (auth()->user()->role === 'admin' ||
                            (auth()->user()->role === 'mod' && $comment->user && in_array($comment->user->role, ['user', 'vip'])))
                        <span class="delete-comment text-danger ms-2" style="cursor: pointer;"
                            data-id="{{ $comment->id }}">
                            <i class="fas fa-times"></i>
                        </span>
                    @endif

                    @if ($level == 0 && auth()->user()->role === 'admin')
                        <button class="btn btn-sm pin-comment ms-2" data-id="{{ $comment->id }}">
                            @if ($isPinned)
                                <i class="fas fa-thumbtack text-warning" title="Bỏ ghim"></i>
                            @else
                                <i class="fas fa-thumbtack" title="Ghim"></i>
                            @endif
                        </button>
                    @endif
                @endif
            </p>

            <p class="mb-2">{{ $comment->comment }}</p>

            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">{{ $comment->created_at->locale('vi')->diffForHumans() }}</span>

                @php
                    $userLiked = auth()->check()
                        ? $comment->reactions
                            ->where('user_id', auth()->id())
                            ->where('type', 'like')
                            ->first()
                        : null;
                    $userDisliked = auth()->check()
                        ? $comment->reactions
                            ->where('user_id', auth()->id())
                            ->where('type', 'dislike')
                            ->first()
                        : null;
                    $likesCount = $comment->reactions->where('type', 'like')->count();
                    $dislikesCount = $comment->reactions->where('type', 'dislike')->count();
                @endphp

                <button class="btn btn-sm btn-outline-primary reaction-btn {{ $userLiked ? 'active' : '' }}"
                    data-type="like" data-id="{{ $comment->id }}">
                    <i class="fas fa-thumbs-up"></i>
                    <span class="likes-count">{{ $likesCount }}</span>
                </button>

                <button class="btn btn-sm btn-outline-danger reaction-btn {{ $userDisliked ? 'active' : '' }}"
                    data-type="dislike" data-id="{{ $comment->id }}">
                    <i class="fas fa-thumbs-down"></i>
                    <span class="dislikes-count">{{ $dislikesCount }}</span>
                </button>
            </div>
        </div>

        @if ($comment->replies && $comment->replies->count() > 0)
            <ul class="comments mt-3">
                @foreach ($comment->replies as $reply)
                    @include('components.comments-item', ['comment' => $reply])
                @endforeach
            </ul>
        @endif
    </div>
</li>


<!-- Add Modal template -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc muốn xóa bình luận này?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .role-badge {
                font-weight: bold;
                padding: 0 3px;
            }

            .admin-badge {
                color: #dc3545;
            }

            .mod-badge {
                color: #198754;
            }

            .vip-badge {
                color: #0d6efd;
            }

            .clickable-name {
                cursor: pointer;
                text-decoration: underline;
            }

            .clickable-name:hover {
                opacity: 0.8;
            }
        </style>
    @endpush
    @push('scripts')
        {{-- Ghim comment --}}
        <script>
            $(document).on('click', '.pin-comment', function() {
                const btn = $(this);
                const commentId = btn.data('id');

                if (btn.prop('disabled')) return;
                btn.prop('disabled', true);

                $.ajax({
                    url: `/comments/${commentId}/pin`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            // Update comments list with new HTML
                            $('#comments-list').html(res.html);
                            showToast(res.message, 'success');
                        }
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON.message || 'Có lỗi xảy ra', 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                    }
                });
            });
        </script>
        <script>
            $(document).ready(function() {
                let commentToDelete = null;
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

                $('body').on('click', '.delete-comment', function() {
                    commentToDelete = $(this).data('id');
                    deleteModal.show();
                });

                $('#confirmDelete').click(function() {
                    if (!commentToDelete) return;

                    $.ajax({
                        url: '{{ route('delete.comments', '') }}/' + commentToDelete,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                $(`#comment-${commentToDelete}`).fadeOut(300, function() {
                                    $(this).remove();
                                });
                                showToast('Đã xóa bình luận thành công');
                            }
                            deleteModal.hide();
                        },
                        error: function(xhr) {
                            console.log(xhr);
                            showToast('Có lỗi xảy ra khi xóa bình luận', 'error');
                            deleteModal.hide();
                        }
                    });
                });
            });
        </script>

        <!-- Add existing delete modal scripts first -->
        <script>
            $('.reaction-btn').click(function() {
                const btn = $(this);
                const commentId = btn.data('id');
                const type = btn.data('type');

                $.ajax({
                    url: `/comments/${commentId}/react`,
                    type: 'POST',
                    data: {
                        type: type,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            btn.find(type === 'like' ? '.likes-count' : '.dislikes-count').text(response[
                                type + 's']);
                            btn.toggleClass('active');
                            showToast(response.message, 'success');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            window.location.href = xhr.responseJSON.redirect;
                        } else {
                            showToast('Có lỗi xảy ra', 'error');
                        }
                    }
                });
            });
        </script>
    @endpush
@endonce
