@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-0 mx-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">Chuyển nhượng truyện: {{ $story->title }}</h5>
                            <p class="text-sm mb-0">Thông tin chi tiết và form chuyển nhượng</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.story-transfer.index') }}" class="btn bg-gradient-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Story Information -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6>Thông tin truyện</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-4">
                                            <img src="{{ $story->cover ? asset('storage/' . $story->cover) : asset('assets/img/default-story.png') }}" 
                                                 class="img-fluid border-radius-lg" alt="Story Cover">
                                        </div>
                                        <div class="col-8">
                                            <h6 class="mb-2">{{ $story->title }}</h6>
                                            <p class="text-sm mb-2">
                                                <strong>Tác giả:</strong> {{ $story->author_name ?? $story->user->name }}
                                            </p>
                                            <p class="text-sm mb-2">
                                                <strong>Loại:</strong> 
                                                {{ $story->story_type === 'original' ? 'Truyện gốc' : 'Truyện dịch' }}
                                                @if($story->is_18_plus)
                                                    <span class="badge badge-sm bg-gradient-danger ms-1">18+</span>
                                                @endif
                                            </p>
                                            <p class="text-sm mb-2">
                                                <strong>Trạng thái:</strong> 
                                                @if($story->status === 'published')
                                                    <span class="badge badge-sm bg-gradient-success">Đã xuất bản</span>
                                                @elseif($story->status === 'pending')
                                                    <span class="badge badge-sm bg-gradient-warning">Chờ duyệt</span>
                                                @elseif($story->status === 'draft')
                                                    <span class="badge badge-sm bg-gradient-secondary">Nháp</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-danger">Từ chối</span>
                                                @endif
                                            </p>
                                            <p class="text-sm mb-2">
                                                <strong>Ngày tạo:</strong> {{ $story->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h6 class="text-sm mb-2">Mô tả:</h6>
                                            <p class="text-xs">{!! Str::limit($story->description, 200) !!}</p>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h6 class="text-sm mb-2">Thể loại:</h6>
                                            <div class="d-flex flex-wrap">
                                                @foreach($story->categories as $category)
                                                    <span class="badge badge-sm bg-gradient-info me-1 mb-1">{{ $category->name }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Author Info -->
                            <div class="card mt-4">
                                <div class="card-header pb-0">
                                    <h6>Tác giả hiện tại</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div>
                                            <img src="{{ $story->user->avatar ? asset('storage/' . $story->user->avatar) : asset('assets/images/avatar_default.jpg') }}" 
                                                 class="avatar avatar-lg me-3" alt="author avatar">
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $story->user->name }}</h6>
                                            <p class="text-sm mb-1">{{ $story->user->email }}</p>
                                            <span class="badge badge-sm bg-gradient-info">{{ $story->user->role }}</span>
                                            <p class="text-xs mt-2 mb-0">
                                                <strong>Ngày tham gia:</strong> {{ $story->user->created_at->format('d/m/Y') }}
                                            </p>
                                            <p class="text-xs mb-0">
                                                <strong>Tổng truyện:</strong> {{ $story->user->stories()->count() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics & Transfer Form -->
                        <div class="col-lg-6">
                            <!-- Statistics -->
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6>Thống kê truyện</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="border-radius-md bg-gradient-primary p-3 text-center">
                                                <h4 class="text-white mb-0">{{ $storyStats['total_chapters'] }}</h4>
                                                <p class="text-white text-xs mb-0">Tổng chương</p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border-radius-md bg-gradient-success p-3 text-center">
                                                <h4 class="text-white mb-0">{{ $storyStats['published_chapters'] }}</h4>
                                                <p class="text-white text-xs mb-0">Đã xuất bản</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <div class="border-radius-md bg-gradient-info p-3 text-center">
                                                <h4 class="text-white mb-0">{{ number_format($storyStats['total_views']) }}</h4>
                                                <p class="text-white text-xs mb-0">Lượt xem</p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border-radius-md bg-gradient-warning p-3 text-center">
                                                <h4 class="text-white mb-0">{{ $storyStats['total_bookmarks'] }}</h4>
                                                <p class="text-white text-xs mb-0">Bookmark</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="border-radius-md bg-gradient-dark p-3 text-center">
                                                <h4 class="text-white mb-0">{{ number_format($storyStats['total_revenue']) }}</h4>
                                                <p class="text-white text-xs mb-0">Tổng doanh thu (xu)</p>
                                                <small class="text-white opacity-8">
                                                    Truyện: {{ number_format($storyStats['story_revenue'] ?? 0) }} xu | 
                                                    Chương: {{ number_format($storyStats['chapter_revenue'] ?? 0) }} xu
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <div class="border-radius-md bg-gradient-secondary p-3 text-center">
                                                <h4 class="text-white mb-0">{{ $storyStats['total_comments'] }}</h4>
                                                <p class="text-white text-xs mb-0">Bình luận</p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border-radius-md bg-gradient-light p-3 text-center">
                                                <h4 class="text-dark mb-0">{{ $storyStats['story_purchases'] + $storyStats['chapter_purchases'] }}</h4>
                                                <p class="text-dark text-xs mb-0">Lượt mua</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Transfer Form -->
                            <div class="card mt-4">
                                <div class="card-header pb-0">
                                    <h6>Chuyển nhượng truyện</h6>
                                </div>
                                <div class="card-body">
                                    <form id="transferForm">
                                        @csrf
                                        <div class="form-group mb-3">
                                            <label for="new_author_id" class="form-control-label">Tác giả mới <span class="text-danger">*</span></label>
                                            <select class="form-select" id="new_author_id" name="new_author_id" required>
                                                <option value="">-- Chọn tác giả mới --</option>
                                                @foreach($authors as $author)
                                                    <option value="{{ $author->id }}">
                                                        {{ $author->name }} ({{ $author->email }})
                                                        <span class="badge">{{ $author->role }}</span>
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">Chọn tác giả mới để chuyển nhượng truyện này</small>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="reason" class="form-control-label">Lý do chuyển nhượng <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="reason" name="reason" rows="4" required
                                                      placeholder="Nhập lý do chi tiết cho việc chuyển nhượng truyện này..."></textarea>
                                            <small class="form-text text-muted">Lý do này sẽ được lưu vào lịch sử và ghi chú của truyện</small>
                                        </div>

                                        <div class="form-group mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="notify_old_author" name="notify_old_author" value="1">
                                                <label class="form-check-label" for="notify_old_author">
                                                    Thông báo cho tác giả hiện tại
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="notify_new_author" name="notify_new_author" value="1" checked>
                                                <label class="form-check-label" for="notify_new_author">
                                                    Thông báo cho tác giả mới
                                                </label>
                                            </div>
                                        </div>

                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Lưu ý:</strong>
                                            <ul class="mb-0 mt-2">
                                                <li>Tất cả chương của truyện cũng sẽ được chuyển nhượng</li>
                                                <li>Lịch sử doanh thu và thống kê sẽ được giữ nguyên</li>
                                                <li>Thao tác này không thể hoàn tác</li>
                                                <li>Thông tin chuyển nhượng sẽ được ghi vào log hệ thống</li>
                                            </ul>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn bg-gradient-warning">
                                                <i class="fas fa-exchange-alt me-2"></i>Xác nhận chuyển nhượng
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts-admin')
    <script>
        $(document).ready(function() {
            $('#transferForm').submit(function(e) {
                e.preventDefault();
                
                // Confirm transfer
                if (!confirm('Bạn có chắc chắn muốn chuyển nhượng truyện này? Thao tác này không thể hoàn tác.')) {
                    return;
                }

                const formData = {
                    new_author_id: $('#new_author_id').val(),
                    reason: $('#reason').val(),
                    notify_old_author: $('#notify_old_author').is(':checked'),
                    notify_new_author: $('#notify_new_author').is(':checked'),
                    _token: '{{ csrf_token() }}'
                };

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...');

                $.ajax({
                    url: '{{ route("admin.story-transfer.transfer", $story) }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            if (response.redirect_url) {
                                window.location.href = response.redirect_url;
                            }
                        } else {
                            alert('Lỗi: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Có lỗi xảy ra khi chuyển nhượng truyện';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors);
                            errorMessage = errors.flat().join('\n');
                        }
                        alert(errorMessage);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
@endpush