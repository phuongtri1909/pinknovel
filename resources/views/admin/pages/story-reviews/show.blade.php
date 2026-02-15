@extends('admin.layouts.app')

@section('title', 'Chi tiết truyện')

@push('styles-admin')
    <style>
        .story-detail {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .story-info-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .story-info-item:last-child {
            border-bottom: none;
        }

        .label {
            font-weight: 600;
            color: #495057;
        }

        .value {
            color: #212529;
        }

        .author-info {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            border-left: 5px solid #6c757d;
            margin-bottom: 20px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .status-published {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-draft {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        .description-text {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            border-left: 3px solid #17a2b8;
        }

        .actions-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #dee2e6;
        }

        .cover-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .chapter-list {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
@endpush

@section('content-auth')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Chi tiết truyện: {{ $story->title }}</h4>
                    <a href="{{ route('admin.story-reviews.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
                <div class="card-body">
                    <!-- Status information -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5>Trạng thái:
                                @if ($story->status == 'pending')
                                    <span class="status-badge status-pending">Chờ duyệt</span>
                                @elseif ($story->status == 'published')
                                    <span class="status-badge status-published">Đã xuất bản</span>
                                @elseif ($story->status == 'rejected')
                                    <span class="status-badge status-rejected">Từ chối</span>
                                @elseif ($story->status == 'draft')
                                    <span class="status-badge status-draft">Nháp</span>
                                @endif
                            </h5>
                            <p class="text-muted">
                                Ngày gửi:
                                {{ $story->submitted_at ? \Carbon\Carbon::parse($story->submitted_at)->format('H:i:s d/m/Y') : 'N/A' }}
                                @if ($story->reviewed_at)
                                    <br>Ngày xét duyệt:
                                    {{ $story->reviewed_at ? \Carbon\Carbon::parse($story->reviewed_at)->format('H:i:s d/m/Y') : 'N/A' }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <!-- Cover image -->
                            <div class="mb-4">
                                <img src="{{ Storage::url($story->cover_medium) }}" alt="{{ $story->title }}"
                                    class="cover-image">
                            </div>

                            <!-- Author information -->
                            <div class="author-info mb-4">
                                <h5 class="mb-3"><i class="fas fa-user me-2"></i> Thông tin tác giả</h5>
                                <div class="story-info-item">
                                    <div class="label">Tên người dùng:</div>
                                    <div class="value">{{ $story->user->name }}</div>
                                </div>
                                <div class="story-info-item">
                                    <div class="label">Email:</div>
                                    <div class="value">{{ $story->user->email }}</div>
                                </div>
                                <div class="story-info-item">
                                    <div class="label">Vai trò:</div>
                                    <div class="value">{{ ucfirst($story->user->role) }}</div>
                                </div>
                                <div class="story-info-item">
                                    <div class="label">Ngày đăng ký:</div>
                                    <div class="value">{{ $story->user->created_at->format('d/m/Y') }}</div>
                                </div>
                            </div>

                            <!-- Story information -->
                            <div class="story-detail">
                                <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i> Thông tin truyện</h5>
                                <div class="story-info-item">
                                    <div class="label">Tiêu đề:</div>
                                    <div class="value">{{ $story->title }}</div>
                                </div>
                                <div class="story-info-item">
                                    <div class="label">Slug:</div>
                                    <div class="value">{{ $story->slug }}</div>
                                </div>
                                <div class="story-info-item">
                                    <div class="label">Tên tác giả:</div>
                                    <div class="value">{{ $story->author_name }}</div>
                                </div>
                                @if ($story->translator_name)
                                    <div class="story-info-item">
                                        <div class="label">Người dịch:</div>
                                        <div class="value">{{ $story->translator_name }}</div>
                                    </div>
                                @endif
                                <div class="story-info-item">
                                    <div class="label">Loại truyện:</div>
                                    <div class="value">
                                        @if ($story->story_type == 'original')
                                            Sáng tác
                                        @elseif ($story->story_type == 'translated')
                                            Dịch
                                        @elseif ($story->story_type == 'collected')
                                            Sưu tầm
                                        @else
                                            {{ $story->story_type }}
                                        @endif
                                    </div>
                                </div>
                                <div class="story-info-item">
                                    <div class="label">Thể loại:</div>
                                    <div class="value">
                                        @foreach ($story->categories as $category)
                                            <span class="badge bg-info me-1">{{ $category->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="story-info-item">
                                    <div class="label">Số chương:</div>
                                    <div class="value">{{ $chapterCount }}</div>
                                </div>
                                <div class="story-info-item">
                                    <div class="label">Độc quyền:</div>
                                    <div class="value">{{ $story->is_monopoly ? 'Có' : 'Không' }}</div>
                                </div>
                                <div class="story-info-item">
                                    <div class="label">Giới hạn độ tuổi:</div>
                                    <div class="value">{{ $story->is_18_plus ? '18+' : 'Không' }}</div>
                                </div>
                                @if ($story->review_note)
                                    <div class="story-info-item">
                                        <div class="label">Ghi chú từ tác giả:</div>
                                        <div class="value">{{ $story->review_note }}</div>
                                    </div>
                                @endif

                                @if ($story->source_link)
                                    <div class="story-info-item">
                                        <div class="label">Link nguồn:</div>
                                        <a href="{{ $story->source_link }}" target="_blank">{{ $story->source_link }}</a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-8">
                            <!-- Description text -->
                            <div class="story-detail">
                                <h5 class="mb-3"><i class="fas fa-align-left me-2"></i> Mô tả truyện</h5>
                                <div class="description-text">
                                    {!! description_for_display($story->description) !!}
                                </div>
                            </div>

                            <!-- Chapter list -->
                            <div class="story-detail">
                                <h5 class="mb-3"><i class="fas fa-list-ol me-2"></i> Danh sách chương
                                    ({{ $chapterCount }})</h5>
                                @if ($chapterCount > 0)
                                    <div class="chapter-list">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Số</th>
                                                    <th>Tiêu đề</th>
                                                    <th>Miễn phí</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($story->chapters as $chapter)
                                                    <tr>
                                                        <td>{{ $chapter->number }}</td>
                                                        <td>
                                                            <a
                                                                href="{{ route('stories.chapters.show', ['story' => $story, 'chapter' => $chapter]) }}">{{ $chapter->title }}</a>
                                                        </td>
                                                        <td>{{ $chapter->is_free ? 'Có' : 'Không' }}</td>
                                                        <td>
                                                            @if ($chapter->status == 'published')
                                                                <span class="badge bg-success">Xuất bản</span>
                                                            @elseif ($chapter->status == 'draft')
                                                                <span class="badge bg-secondary">Nháp</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-warning">Truyện chưa có chương nào.</div>
                                @endif
                            </div>

                            @if ($story->reviewHistories->isNotEmpty())
                                <div class="story-detail mt-4">
                                    <h5 class="mb-3"><i class="fas fa-history me-2"></i> Lịch sử duyệt</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Thời gian</th>
                                                    <th>Kết quả</th>
                                                    <th>Người duyệt</th>
                                                    <th>Ghi chú</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($story->reviewHistories as $history)
                                                    <tr>
                                                        <td class="text-nowrap text-muted">
                                                            {{ $history->reviewed_at->format('H:i d/m/Y') }}
                                                        </td>
                                                        <td>
                                                            @if ($history->action === 'approved')
                                                                <span class="badge bg-success">Đã duyệt</span>
                                                            @else
                                                                <span class="badge bg-danger">Từ chối</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($history->reviewer)
                                                                {{ $history->reviewer->name }}
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($history->note)
                                                                <div class="small">{{ $history->note }}</div>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            @if ($story->status == 'pending')
                                <!-- Actions for pending stories -->
                                <div class="actions-container">
                                    <h5 class="mb-3">Hành động</h5>

                                    <div class="row">
                                        <!-- Approve form -->
                                        <div class="col-md-6">
                                            <form action="{{ route('admin.story-reviews.approve', $story) }}"
                                                method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="approve_note" class="form-label">Ghi chú khi duyệt (không
                                                        bắt buộc)</label>
                                                    <textarea class="form-control" id="approve_note" name="admin_note" rows="3"
                                                        placeholder="Ghi chú khi duyệt truyện..."></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="fas fa-check-circle me-2"></i> Duyệt truyện
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Reject form -->
                                        <div class="col-md-6">
                                            <form action="{{ route('admin.story-reviews.reject', $story) }}"
                                                method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="reject_note" class="form-label">Lý do từ chối <span
                                                            class="text-danger">*</span></label>
                                                    <textarea class="form-control" id="reject_note" name="admin_note" rows="3" required
                                                        placeholder="Lý do từ chối truyện..."></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-danger w-100">
                                                    <i class="fas fa-times-circle me-2"></i> Từ chối truyện
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @elseif ($story->admin_note)
                                <!-- Admin note for approved/rejected stories -->
                                <div class="story-detail">
                                    <h5 class="mb-3"><i class="fas fa-comment-alt me-2"></i> Phản hồi từ quản trị viên
                                    </h5>
                                    <div class="description-text">
                                        {!! nl2br(e($story->admin_note)) !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
