<section id="comments" class="my-3 my-md-5">
    <div class="container px-2 px-md-3">
        <h5 class="mb-3">BÌNH LUẬN TRUYỆN</h5>
        <div class="row">
            <div class="col-12">
                <div class="form-floating submit-comment">
                    <textarea class="form-control" id="comment-input" placeholder="Nhập bình luận..." rows="2" maxlength="700"></textarea>
                    <label for="comment-input">Bình luận</label>
                    <button class="btn btn-sm btn-outline-info btn-send-comment" id="btn-comment">
                        <i class="fa-regular fa-paper-plane"></i>
                    </button>
                </div>

                <div class="blog-comment">
                    <ul class="comments mb-0" id="comments-list">
                        @include('components.comments-list', [
                            'pinnedComments' => $pinnedComments,
                            'regularComments' => $regularComments,
                        ])
                    </ul>
                </div>

                @if ($regularComments->hasMorePages())
                    <div class="text-center mt-3">
                        <button class="btn btn-link" id="load-more-comments">
                            Xem thêm bình luận...
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>


@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let page = 1;
            let isSubmitting = false;

            $('#btn-comment').click(function() {
                const btn = $(this);
                const comment = $('#comment-input').val().trim();
                if (!comment || isSubmitting) return;

                // Disable button and show loading
                isSubmitting = true;
                btn.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '{{ route('comment.store.client') }}',
                    type: 'POST',
                    data: {
                        comment: comment,
                        story_id: '{{ $story->id }}',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            $('#comments-list').prepend(res.html);
                            $('#comment-input').val('');
                            showToast(res.message, 'success');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            window.location.href = '{{ route('login') }}';
                        } else {
                            showToast(xhr.responseJSON.message || 'Có lỗi xảy ra', 'error');
                        }
                    },
                    complete: function() {
                        // Re-enable button and restore original state
                        isSubmitting = false;
                        btn.prop('disabled', false)
                            .html('<i class="fa-regular fa-paper-plane"></i>');
                    }
                });
            });

            $('#load-more-comments').click(function() {
                const btn = $(this);
                btn.html('<i class="fas fa-spinner fa-spin"></i> Đang tải...');

                page++;
                $.ajax({
                    url: '{{ route('comments.load', $story->id) }}',
                    data: {
                        page: page
                    },
                    success: function(res) {
                        $('#comments-list').append(res.html);
                        if (!res.hasMore) {
                            $('#load-more-comments').remove();
                        }
                        btn.html('Xem thêm bình luận...');
                    },
                    error: function(xhr) {
                        showToast('Có lỗi xảy ra khi tải bình luận', 'error');
                        btn.html('Thử lại');
                    }
                });
            });

            $(document).on('click', '.reply-btn', function(e) {
                e.preventDefault();
                const commentId = $(this).data('id');
                // Remove any existing reply forms first
                $('.reply-form').remove();
                $('.reply-btn').show();

                const replyForm = `
                    <div class="reply-form mt-2">
                        <div class="form-floating">
                            <textarea class="form-control" placeholder="Nhập trả lời..." maxlength="700"></textarea>
                            <label>Trả lời</label>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-2">
                            <button class="btn btn-sm btn-secondary cancel-reply">Hủy</button>
                            <button class="btn btn-sm btn-primary submit-reply" data-id="${commentId}">Gửi</button>
                        </div>
                    </div>
                `;
                $(this).closest('.post-comments').append(replyForm);
                $(this).hide();
            });

            $(document).on('click', '.cancel-reply', function() {
                const replyForm = $(this).closest('.reply-form');
                const replyBtn = replyForm.closest('.post-comments').find('.reply-btn');
                replyForm.remove();
                replyBtn.show();
            });

            $(document).on('click', '.submit-reply', function() {
                const btn = $(this);
                const commentId = btn.data('id');
                const reply = btn.closest('.reply-form').find('textarea').val().trim();

                if (!reply || btn.prop('disabled')) return;

                // Disable button and show loading
                btn.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '{{ route('comment.store.client') }}',
                    type: 'POST',
                    data: {
                        comment: reply,
                        reply_id: commentId,
                        story_id: '{{ $story->id }}',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            let replyContainer = btn.closest('.post-comments').find(
                                'ul.comments');

                            // Create replies container if it doesn't exist
                            if (replyContainer.length === 0) {
                                btn.closest('.post-comments').append(
                                    '<ul class="comments mt-3"></ul>');
                                replyContainer = btn.closest('.post-comments').find(
                                    'ul.comments');
                            }

                            replyContainer.append(res.html);
                            btn.closest('.reply-form').remove();

                            // Re-enable reply button
                            btn.closest('.post-comments').find('.reply-btn').show();
                            showToast(res.message, 'success');
                        }
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON.message || 'Có lỗi xảy ra', 'error');
                        // Re-enable button on error
                        btn.prop('disabled', false).text('Gửi');
                    }
                });
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .blog-comment ul.comments ul {
            position: relative;
        }

        .blog-comment ul.comments ul:before {
            content: '';
            position: absolute;
            left: -25px;
            top: 0;
            height: 100%;
            border-left: 2px solid #eee;
        }

        .blog-comment ul.comments ul li:before {
            content: '';
            position: absolute;
            left: -25px;
            top: 20px;
            width: 25px;
            border-top: 2px solid #eee;
        }

        .blog-comment ul.comments ul li {
            position: relative;
        }

        @media (max-width: 768px) {
            .blog-comment ul.comments ul:before {
                left: -10px;
            }

            .blog-comment ul.comments ul li:before {
                left: -10px;
                width: 10px;
            }
        }

        /* comment */
        .blog-comment::before,
        .blog-comment::after,
        .blog-comment-form::before,
        .blog-comment-form::after {
            content: "";
            display: table;
            clear: both;
        }

        .blog-comment ul {
            list-style-type: none;
            padding: 0;
        }

        .blog-comment img {
            opacity: 1;
            filter: Alpha(opacity=100);
            -webkit-border-radius: 4px;
            -moz-border-radius: 4px;
            -o-border-radius: 4px;
            border-radius: 4px;
        }

        .blog-comment img.avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .blog-comment img.avatar-reply {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            .blog-comment img.avatar {
                width: 35px;
                height: 35px;
            }

            .blog-comment img.avatar-reply {
                width: 25px;
                height: 25px;
            }
        }

        .blog-comment .post-comments {
            margin-bottom: 15px;
            position: relative;
            width: 100%;
        }

        .blog-comment .post-comments .content-post-comments {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 15px;
            padding: 5px;
        }

        .blog-comment .meta {
            font-size: 13px;
            color: #aaa;
            padding-bottom: 8px;
            margin-bottom: 10px !important;
            border-bottom: 1px solid #eee;
        }

        .submit-comment {
            position: relative;
            margin-bottom: 20px;
        }

        .btn-send-comment {
            position: absolute;
            right: 12px;
            bottom: 8px;
        }

        .reaction-btn {
            padding: 4px 8px;
            font-size: 12px;
        }

        .reaction-btn.active {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .reply-form {
            margin: 10px 0;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .blog-comment .post-comments {
                padding: 10px !important;
            }

            .reaction-btn {
                padding: 2px 6px;
            }

            .btn-send-comment {
                bottom: 4px;
            }

            .meta {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                align-items: center;
            }

            .meta .pull-right {
                margin-left: auto;
            }
        }

        /* Pinned comment styling */
        .pinned-comment .content-post-comments {
            border: 1px solid #ffc107 !important;
            background-color: #fffdf5 !important;
        }

        .pinned-comment .pinned-badge {
            color: #ffc107;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
    </style>
@endpush
