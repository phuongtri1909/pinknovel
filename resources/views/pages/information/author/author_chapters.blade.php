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
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách truyện
            </a>
        </div>

        <div>
            @if($story->chapters->count() < 3 && $story->status == 'draft')
                <span class="badge bg-warning text-dark me-2">
                    <i class="fas fa-exclamation-triangle me-1"></i> Cần thêm {{ 3 - $story->chapters->count() }} chương nữa để đủ điều kiện duyệt
                </span>
            @endif
            
            <a href="{{ route('user.author.stories.chapters.create', $story->id) }}" class="btn btn-outline-success">
                <i class="fas fa-plus me-1"></i> Thêm chương mới
            </a>
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
                    <h5 class="card-title mb-1">{{ $story->title }}</h5>
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
                    </div>
                    <a href="{{ route('user.author.stories.edit', $story->id) }}#review" class="btn btn-sm btn-outline-primary me-1">
                        <i class="fas fa-check-circle me-1"></i> Yêu cầu duyệt
                    </a>
                    <a href="{{ route('user.author.stories.edit', $story->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit me-1"></i> Sửa thông tin
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if ($chapters->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th scope="col" width="60">Số</th>
                        <th scope="col">Tên chương</th>
                        <th scope="col" width="120">Trạng thái</th>
                        <th scope="col" width="200">Thời gian</th>
                        <th scope="col" width="150">Hành động</th>
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
                                @if ($chapter->status == 'published')
                                    <span class="badge bg-success">Đã đăng</span>
                                @else
                                    <span class="badge bg-secondary">Nháp</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    <div><i class="fas fa-calendar-plus me-1"></i>
                                        {{ $chapter->created_at->format('d/m/Y H:i') }}</div>
                                    <div><i class="fas fa-calendar-check me-1"></i>
                                        {{ $chapter->updated_at->format('d/m/Y H:i') }}</div>
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
                    Truyện này chưa được duyệt. Vui lòng chờ quản trị viên duyệt truyện trước khi thêm chương.
                </div>
            @endif
        </div>
    @endif
@endsection
