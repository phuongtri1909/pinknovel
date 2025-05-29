@extends('layouts.information')

@section('info_title', 'Quản lý chương truyện')
@section('info_description', 'Quản lý chương truyện của bạn trên ' . request()->getHost())
@section('info_keyword', 'quản lý chương, đăng truyện, tác giả, ' . request()->getHost())
@section('info_section_title', 'Quản lý chương truyện')
@section('info_section_desc', 'Truyện: ' . $story->title)

@section('info_content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('user.author.stories') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <div>
            @if ($story->chapters->count() < 3 && $story->status == 'draft')
                <span class="badge bg-warning text-dark me-2">
                    <i class="fas fa-exclamation-triangle me-1"></i> Cần thêm {{ 3 - $story->chapters->count() }} chương nữa
                    để đủ điều kiện duyệt
                </span>
            @endif

            @if ($story->completed)
                @if ($story->hasCombo())
                    <a href="{{ route('user.author.stories.combo.edit', $story->id) }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-tags me-1"></i> Chỉnh sửa combo
                    </a>
                @else
                    <a href="{{ route('user.author.stories.combo.create', $story->id) }}"
                        class="btn btn-outline-primary me-2">
                        <i class="fas fa-tags me-1"></i> Tạo combo
                    </a>
                @endif
            @endif

            <div>
                <a href="{{ route('user.author.stories.chapters.create', $story->id) }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-plus me-1"></i> Chương
            </a>
             <a href="{{ route('user.author.stories.chapters.batch.create', $story->id) }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-plus me-1"></i> Nhiều Chương
            </a>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-1">
                    <img src="{{ Storage::url($story->cover_thumbnail) }}" alt="{{ $story->title }}" class="img-thumbnail"
                        style="width: 60px; height: 80px; object-fit: cover;">
                </div>
                <div class="col-md-7">
                    <h5 class="card-title mb-1">{{ $story->title }}
                        @if ($story->is_18_plus)
                            <span class="badge bg-danger ms-2"><i class="fas fa-exclamation-triangle me-1"></i> 18+</span>
                        @endif
                    </h5>
                    <div class="text-muted small mb-2">
                        <span class="me-3">
                            <i class="fas fa-book me-1"></i> {{ $story->chapters->count() }} chương
                        </span>
                        <span>
                            <i class="fas fa-calendar-alt me-1"></i> Cập nhật: {{ $story->updated_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    <div>
                        @foreach ($story->categories as $category)
                            <span class="badge bg-secondary me-1">{{ $category->name }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="mb-2">
                        @if ($story->status == 'published')
                            <span class="badge bg-success">Đã xuất bản</span>
                        @elseif($story->status == 'draft')
                            <span class="badge bg-secondary">Bản nháp</span>
                        @elseif($story->status == 'pending')
                            <span class="badge bg-warning">Chờ duyệt</span>
                        @endif

                        @if ($story->completed)
                            <span class="badge bg-info">Đã hoàn thành</span>
                        @else
                            <span class="badge bg-secondary">Đang ra</span>
                        @endif
                    </div>

                    <div>
                        @if (!$story->completed && $story->status == 'published')
                            <button type="button" class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal"
                                data-bs-target="#markCompleteModal">
                                <i class="fas fa-check-double me-1"></i> Đánh dấu hoàn thành
                            </button>
                        @endif

                        @if ($story->status == 'draft')
                            <a href="{{ route('user.author.stories.edit', $story->id) }}#review"
                                class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-check-circle me-1"></i> Yêu cầu duyệt
                            </a>
                        @elseif ($story->status == 'pending')
                            <a href="{{ route('user.author.stories.edit', $story->id) }}#review"
                                class="btn btn-sm btn-outline-secondary me-1">
                                <i class="fas fa-check-circle me-1"></i> Đang chờ duyệt
                            </a>
                        @endif

                        <a href="{{ route('user.author.stories.edit', $story->id) }}"
                            class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit me-1"></i> Sửa thông tin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($story->hasCombo())
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-tags me-2"></i> Combo trọn bộ</h5>
                <a href="{{ route('user.author.stories.combo.edit', $story->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-2">
                            <div class="combo-badge me-3">
                                <span class="badge bg-primary rounded-pill px-3 py-2">
                                    <i class="fas fa-book-open me-1"></i> Trọn bộ
                                    {{ $story->chapters->where('status', 'published')->count() }} chương
                                </span>
                            </div>
                            <div class="combo-status">
                                @if (!$story->has_combo)
                                    <span class="badge bg-danger rounded-pill px-3 py-2">
                                        <i class="fas fa-eye-slash me-1"></i> Đã ẩn
                                    </span>
                                @else
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        <i class="fas fa-eye me-1"></i> Đang hiển thị
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="mb-2">
                            <span class="fs-4 fw-bold text-primary">{{ number_format($story->combo_price) }} <i
                                    class="fas fa-coins"></i></span>
                            <span
                                class="text-muted text-decoration-line-through ms-2">{{ number_format($story->total_chapter_price) }}</span>
                        </div>
                        <div class="discount-badge">
                            <span class="badge bg-success rounded-pill px-3 py-2">
                                <i class="fas fa-percentage me-1"></i> Tiết kiệm {{ $story->discount_percentage }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($story->completed)
        <div class="alert alert-info mb-4 d-flex align-items-center justify-content-between">
            <div>
                <i class="fas fa-info-circle me-2"></i>
                Truyện của bạn đã hoàn thành. Bạn có thể tạo combo trọn bộ để độc giả mua với mức giá ưu đãi.
            </div>
            <div>
                <a href="{{ route('user.author.stories.combo.create', $story->id) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-tags me-1"></i> Tạo combo ngay
                </a>
            </div>
        </div>
    @endif

    @if ($chapters->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th scope="col" width="10">Chương</th>
                        <th scope="col">Tên chương</th>
                        <th scope="col">Coin</th>
                        <th scope="col" width="120">Trạng thái</th>
                        <th scope="col" width="150">Thời gian</th>
                        <th scope="col" width="110">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($chapters as $chapter)
                        <tr>
                            <td>{{ $chapter->number }}</td>
                            <td>
                                <div class="fw-bold">{{ $chapter->title }}</div>
                                <div class="text-muted small">
                                    <i class="fas fa-eye me-1"></i> {{ number_format($chapter->views) }} lượt xem
                                </div>
                            </td>
                            <td>
                                @if ($chapter->is_free == true)
                                    <span class="badge bg-secondary">Miễn phí</span>
                                    @if($chapter->password)
                                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-lock"></i></span>
                                    @endif
                                @else
                                    <span class="badge bg-warning text-dark">{{ $chapter->price }} <i
                                            class="fa-solid fa-sack-dollar"></i></span>
                                @endif
                            </td>
                            <td>
                                @if ($chapter->status == 'published')
                                    <span class="badge bg-success">Đã đăng</span>
                                @elseif($chapter->status == 'draft' && $chapter->scheduled_publish_at && $chapter->scheduled_publish_at->isFuture())
                                    <span class="badge bg-info">Đã lên lịch</span>
                                    <div class="countdown-timer small mt-1"
                                        data-target="{{ $chapter->scheduled_publish_at->timestamp }}"
                                        data-id="{{ $chapter->id }}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock me-1"></i> <span class="countdown-text">Đang
                                                tính...</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-secondary">Nháp</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    <div><i class="fas fa-calendar-plus me-1"></i>
                                        {{ $chapter->created_at->format('d/m/Y H:i') }}</div>

                                    @if ($chapter->scheduled_publish_at && $chapter->scheduled_publish_at->isFuture())
                                        <div><i class="fas fa-calendar-alt me-1"></i>
                                            {{ $chapter->scheduled_publish_at->format('d/m/Y H:i') }}</div>
                                    @else
                                        <div><i class="fas fa-calendar-check me-1"></i>
                                            {{ $chapter->updated_at->format('d/m/Y H:i') }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('user.author.stories.chapters.edit', ['story' => $story->id, 'chapter' => $chapter->id]) }}"
                                        class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal{{ $chapter->id }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal xác nhận xóa -->
                        <div class="modal fade" id="deleteModal{{ $chapter->id }}" tabindex="-1"
                            aria-labelledby="deleteModalLabel{{ $chapter->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel{{ $chapter->id }}">
                                            Xác nhận xóa chương
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Bạn có chắc chắn muốn xóa chương "{{ $chapter->number }}:
                                        {{ $chapter->title }}"?<br>
                                        Hành động này không thể hoàn tác.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Hủy</button>
                                        <form
                                            action="{{ route('user.author.stories.chapters.destroy', ['story' => $story->id, 'chapter' => $chapter->id]) }}"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-4">
            <x-pagination :paginator="$chapters" />
        </div>
    @else
        <div class="empty-state text-center py-5">
            <div class="empty-icon mb-4">
                <i class="fas fa-book"></i>
            </div>
            <h5>Chưa có chương nào</h5>
            <p class="text-muted">Hãy thêm chương đầu tiên cho truyện của bạn!</p>

            @if ($story->approved)
                <a href="{{ route('user.author.stories.chapters.create', $story->id) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Thêm chương mới
                </a>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Truyện này chưa được duyệt. Vui lòng thêm ít nhất 3 chương và gửi duyệt.
                </div>
            @endif
        </div>
    @endif

    <!-- Modal đánh dấu truyện hoàn thành -->
    <div class="modal fade" id="markCompleteModal" tabindex="-1" aria-labelledby="markCompleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="markCompleteModalLabel">Đánh dấu truyện đã hoàn thành</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn đánh dấu truyện này là đã hoàn thành?</p>
                    <p>Sau khi đánh dấu hoàn thành, bạn có thể:</p>
                    <ul>
                        <li>Vẫn tiếp tục thêm chương mới nếu muốn</li>
                        <li>Tạo combo trọn bộ để độc giả mua với giá ưu đãi</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <form action="{{ route('user.author.stories.mark-complete', $story->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-primary">Đánh dấu hoàn thành</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
    <style>
        .countdown-timer {
            background-color: rgba(0, 0, 0, 0.05);
            border-radius: 4px;
            padding: 2px 6px;
            margin-top: 4px;
        }

        .countdown-timer .fas {
            font-size: 0.8rem;
        }

        .countdown-timer .text-danger {
            font-weight: bold;
            animation: pulse 1s infinite alternate;
        }

        @keyframes pulse {
            from {
                opacity: 1;
            }

            to {
                opacity: 0.7;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tìm tất cả các đồng hồ đếm ngược
            const countdownTimers = document.querySelectorAll('.countdown-timer');

            // Nếu có bất kỳ đồng hồ đếm ngược nào
            if (countdownTimers.length > 0) {
                // Thiết lập bộ đếm
                const timers = [];

                countdownTimers.forEach(timer => {
                    // Lấy thời điểm đích từ thuộc tính data
                    const targetTimestamp = parseInt(timer.getAttribute('data-target'));
                    const chapterId = timer.getAttribute('data-id');
                    const countdownText = timer.querySelector('.countdown-text');

                    // Thêm vào mảng timers để theo dõi
                    timers.push({
                        element: countdownText,
                        targetTime: targetTimestamp,
                        chapterId: chapterId
                    });

                    // Cập nhật ngay lập tức
                    updateCountdown(countdownText, targetTimestamp, chapterId);
                });
 
                // Cập nhật tất cả đồng hồ mỗi giây
                setInterval(() => {
                    timers.forEach(timer => {
                        updateCountdown(timer.element, timer.targetTime, timer.chapterId);
                    });
                }, 1000);
            }

            // Hàm cập nhật đồng hồ đếm ngược
            function updateCountdown(element, targetTime, chapterId) {
                // Tính thời gian còn lại
                const now = Math.floor(Date.now() / 1000);
                const timeLeft = targetTime - now;

                // Nếu đã hết thời gian
                if (timeLeft <= 0) {
                    element.innerHTML = '<span class="text-success">Đang xuất bản...</span>';
                    return;
                }

                // Tính ngày, giờ, phút, giây
                const days = Math.floor(timeLeft / 86400);
                const hours = Math.floor((timeLeft % 86400) / 3600);
                const minutes = Math.floor((timeLeft % 3600) / 60);
                const seconds = timeLeft % 60;

                // Tạo chuỗi hiển thị
                let displayText = '';

                if (days > 0) {
                    displayText += `${days}d `;
                }

                displayText += `${padZero(hours)}:${padZero(minutes)}:${padZero(seconds)}`;

                // Hiển thị thời gian
                element.textContent = displayText;

                // Thêm màu sắc dựa trên thời gian còn lại
                if (timeLeft < 300) { // Dưới 5 phút
                    element.classList.remove('text-info', 'text-warning');
                    element.classList.add('text-danger');
                } else if (timeLeft < 3600) { // Dưới 1 giờ
                    element.classList.remove('text-info', 'text-danger');
                    element.classList.add('text-warning');
                } else {
                    element.classList.remove('text-warning', 'text-danger');
                    element.classList.add('text-info');
                }
            }

            // Hàm thêm số 0 phía trước nếu số < 10
            function padZero(num) {
                return num < 10 ? '0' + num : num;
            }
        });
    </script>
@endpush
