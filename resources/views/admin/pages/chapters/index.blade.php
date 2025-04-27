@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-0 mx-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">
                               
                                Danh sách chương truyện: {{ $story->title }}
                                
                            </h5>
                            <p class="text-sm mb-0">
                                Tổng số: {{ $totalChapters }} chương
                                ({{ $publishedChapters }} hiển thị / {{ $draftChapters }} nháp)
                            </p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <form method="GET" class="d-flex gap-2">
                            <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                <option value="">- Trạng thái -</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Hiển thị</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                            </select>

                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" name="search" 
                                       value="{{ request('search') }}" placeholder="Tìm kiếm...">
                                <button class="btn bg-gradient-primary btn-sm px-2 mb-0" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>

                        <div>

                            <a href="{{ route('stories.index') }}" class="btn bg-gradient-secondary btn-sm mb-0 me-2">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <a href="{{ route('stories.chapters.create', $story) }}" class="btn bg-gradient-primary btn-sm mb-0">
                                <i class="fas fa-plus me-2"></i>Thêm chương mới
                            </a>

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
                                        STT
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Tên chương
                                    </th>

                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Nội dung
                                    </th>

                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Views
                                    </th>

                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Link aff
                                    </th>

                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Trạng thái
                                    </th>

                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Ngày tạo
                                    </th>

                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Hành động
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($chapters as $chapter)
                                    <tr>
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0">Chương {{ $chapter->number }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                {{ $chapter->title }}
                                            </p>
                                        </td>
                                        <td>
                                            <p class="text-xs text-truncate" style="max-width: 200px;">
                                                {{ Str::limit($chapter->content, 50) }}
                                            </p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $chapter->views ?? 0 }}</p>
                                        </td>

                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                <a href="{{ $chapter->link_aff }}" target="_blank"> {{ Str::limit($chapter->link_aff, 20) }}</a>
                                            </p>
                                        </td>

                                        <td>
                                            <span
                                                class="badge badge-sm bg-gradient-{{ $chapter->status === 'published' ? 'success' : 'warning' }}">
                                                {{ $chapter->status === 'published' ? 'Hiển thị' : 'Nháp' }}
                                            </span>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                {{ $chapter->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </td>
                                        <td class="text-center d-flex flex-column">
                                            <a href="{{ route('stories.chapters.edit', ['story' => $story, 'chapter' => $chapter]) }}" 
                                               class="btn btn-link text-dark px-3 mb-0">
                                                <i class="fas fa-pencil-alt text-dark me-2"></i>Sửa
                                            </a>
                                            @include('admin.pages.components.delete-form', [
                                                'id' => $chapter->id,
                                                'route' => route('stories.chapters.destroy', ['story' => $story, 'chapter' => $chapter])
                                            ])
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">Chưa có chương nào</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 pt-4">
                        <x-pagination :paginator="$chapters" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts-admin')
    <script>
        $(document).ready(function() {
            $('#statusToggle').change(function() {
                const toggle = $(this);
                const label = toggle.closest('.form-check').find('.badge-status');

                $.ajax({
                    url: '{{ route('status.toggle') }}',
                    type: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            if (response.newStatus === 'done') {
                                label.removeClass('bg-warning').addClass('bg-success');
                                label.text('Hoàn thành');
                            } else {
                                label.removeClass('bg-success').addClass('bg-warning');
                                label.text('Đang viết');
                            }
                            showToast('Đã cập nhật trạng thái thành công', 'success');
                        }
                    },
                    error: function() {
                        toggle.prop('checked', !toggle.prop(
                            'checked')); // Revert checkbox state
                        showToast('Có lỗi xảy ra', 'error');
                    }
                });
            });
        });
    </script>
@endpush
