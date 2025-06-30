@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-0 mx-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">Chuyển nhượng truyện</h5>
                            <p class="text-sm mb-0">Quản lý việc chuyển nhượng truyện giữa các tác giả</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.story-transfer.history') }}" class="btn bg-gradient-info btn-sm me-2">
                                <i class="fas fa-history me-2"></i>Lịch sử chuyển nhượng
                            </a>
                            <button class="btn bg-gradient-warning btn-sm" data-bs-toggle="modal"
                                data-bs-target="#bulkTransferModal">
                                <i class="fas fa-exchange-alt me-2"></i>Chuyển nhượng hàng loạt
                            </button>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <form method="GET" class="d-flex gap-2 flex-wrap">
                                <div class="form-group">
                                    <select name="current_author" class="form-select form-select-sm"
                                        onchange="this.form.submit()">
                                        <option value="">- Tác giả hiện tại -</option>
                                        @foreach ($authors as $author)
                                            <option value="{{ $author->id }}"
                                                {{ request('current_author') == $author->id ? 'selected' : '' }}>
                                                {{ $author->name }} ({{ $author->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">- Trạng thái -</option>
                                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp
                                        </option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ
                                            duyệt</option>
                                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>
                                            Đã xuất bản</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ
                                            chối</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <select name="story_type" class="form-select form-select-sm"
                                        onchange="this.form.submit()">
                                        <option value="">- Loại truyện -</option>
                                        <option value="original"
                                            {{ request('story_type') == 'original' ? 'selected' : '' }}>Truyện gốc</option>
                                        <option value="translation"
                                            {{ request('story_type') == 'translation' ? 'selected' : '' }}>Truyện dịch
                                        </option>
                                    </select>
                                </div>

                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" name="search"
                                        value="{{ request('search') }}" placeholder="Tìm kiếm truyện, tác giả...">
                                    <button class="btn bg-gradient-primary btn-sm px-2 mb-0" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    @include('admin.pages.components.success-error')

                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Truyện
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tác
                                        giả hiện tại</th>

                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Chương
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng
                                        thái</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ngày tạo
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stories as $story)
                                    <tr>
                                        <td class="ps-4">
                                            <input type="checkbox" class="story-checkbox" value="{{ $story->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div>
                                                    <img src="{{ $story->cover_thumbnail ? asset('storage/' . $story->cover_thumbnail) : asset('assets/img/default-story.png') }}"
                                                        class="avatar avatar-sm me-3" alt="story cover">
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ Str::limit($story->title, 40) }}</h6>

                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <div>
                                                    <img src="{{ $story->user->avatar ? asset('storage/' . $story->user->avatar) : asset('assets/img/default-avatar.png') }}"
                                                        class="avatar avatar-sm me-2" alt="user image">
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-xs">{{ $story->user->name }}</h6>
                                                    <p class="text-xs text-secondary mb-0">{{ $story->user->email }}</p>
                                                    <span
                                                        class="badge badge-xs bg-gradient-info">{{ $story->user->role }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        <td>

                                            <p class="text-xs font-weight-bold mb-0">{{ $story->chapters_count ?? 0 }}
                                                chương</p>
                                            <p class="text-xs text-secondary mb-0">{{ number_format($story->total_views) }}
                                                lượt xem</p>
                                        </td>
                                        <td>
                                            @if ($story->status === 'published')
                                                <span class="badge badge-sm bg-gradient-success">Đã xuất bản</span>
                                            @elseif($story->status === 'pending')
                                                <span class="badge badge-sm bg-gradient-warning">Chờ duyệt</span>
                                            @elseif($story->status === 'draft')
                                                <span class="badge badge-sm bg-gradient-secondary">Nháp</span>
                                            @else
                                                <span class="badge badge-sm bg-gradient-danger">Từ chối</span>
                                            @endif
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                {{ $story->created_at->format('d/m/Y') }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ $story->created_at->format('H:i') }}
                                            </p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="{{ route('admin.story-transfer.show', $story) }}"
                                                class="btn btn-link text-info text-gradient px-3 mb-0">
                                                <i class="fas fa-exchange-alt me-2"></i>Chuyển nhượng
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">Không có truyện nào</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 pt-4">
                        {{ $stories->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Transfer Modal -->
    <div class="modal fade" id="bulkTransferModal" tabindex="-1" aria-labelledby="bulkTransferModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkTransferModalLabel">Chuyển nhượng hàng loạt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="bulkTransferForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="selectedCount">0</span> truyện được chọn để chuyển nhượng
                        </div>

                        <div class="form-group mb-3">
                            <label for="bulk_new_author_id" class="form-control-label">Tác giả mới</label>
                            <select class="form-select" id="bulk_new_author_id" name="new_author_id" required>
                                <option value="">-- Chọn tác giả mới --</option>
                                @foreach ($authors as $author)
                                    <option value="{{ $author->id }}">{{ $author->name }} ({{ $author->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="bulk_reason" class="form-control-label">Lý do chuyển nhượng</label>
                            <textarea class="form-control" id="bulk_reason" name="reason" rows="3" required
                                placeholder="Nhập lý do chuyển nhượng truyện..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn bg-gradient-warning">
                            <i class="fas fa-exchange-alt me-2"></i>Chuyển nhượng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts-admin')
    <script>
        $(document).ready(function() {
            // Handle select all checkbox
            $('#selectAll').change(function() {
                $('.story-checkbox').prop('checked', $(this).prop('checked'));
                updateSelectedCount();
            });

            // Handle individual checkboxes
            $('.story-checkbox').change(function() {
                updateSelectedCount();

                // Update select all checkbox
                const totalCheckboxes = $('.story-checkbox').length;
                const checkedCheckboxes = $('.story-checkbox:checked').length;

                $('#selectAll').prop('checked', checkedCheckboxes === totalCheckboxes);
                $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes <
                    totalCheckboxes);
            });

            function updateSelectedCount() {
                const count = $('.story-checkbox:checked').length;
                $('#selectedCount').text(count);
            }

            // Handle bulk transfer form
            $('#bulkTransferForm').submit(function(e) {
                e.preventDefault();

                const selectedStories = $('.story-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedStories.length === 0) {
                    alert('Vui lòng chọn ít nhất một truyện để chuyển nhượng');
                    return;
                }

                const formData = {
                    story_ids: selectedStories,
                    new_author_id: $('#bulk_new_author_id').val(),
                    reason: $('#bulk_reason').val(),
                    _token: '{{ csrf_token() }}'
                };

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...');

                $.ajax({
                    url: '{{ route('admin.story-transfer.bulk') }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            $('#bulkTransferModal').modal('hide');
                            window.location.reload();
                        } else {
                            alert('Lỗi: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Có lỗi xảy ra khi chuyển nhượng';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
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
