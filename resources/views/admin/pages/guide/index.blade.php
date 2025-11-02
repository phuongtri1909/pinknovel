@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                        <h6 class="mb-0">Danh sách hướng dẫn</h6>
                        <a href="{{ route('admin.guides.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i><span class="d-none d-md-inline">Thêm hướng dẫn mới</span><span class="d-md-none">Thêm</span>
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @include('admin.pages.components.success-error')
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-xxs font-weight-bolder">ID</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2">Tiêu đề</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2 d-none d-md-table-cell">Slug</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2">Trạng thái</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2 d-none d-lg-table-cell">Ngày tạo</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($guides as $guide)
                                <tr>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $guide->id }}</p>
                                    </td>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $guide->title }}</h6>
                                                <p class="text-xs text-secondary mb-0 d-md-none">{{ Str::limit($guide->slug, 30) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <p class="text-xs mb-0" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $guide->slug }}">
                                            {{ $guide->slug }}
                                        </p>
                                    </td>
                                    <td>
                                        @if($guide->is_published)
                                            <span class="badge bg-gradient-success">Đang hiển thị</span>
                                        @else
                                            <span class="badge bg-gradient-secondary">Ẩn</span>
                                        @endif
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <p class="text-xs mb-0">{{ $guide->created_at->format('d/m/Y H:i') }}</p>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('admin.guides.show', $guide) }}" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                                <i class="fas fa-eye me-2"></i><span class="d-none d-md-inline">Chi tiết</span>
                                            </a>
                                            <a href="{{ route('admin.guides.edit', $guide) }}" class="btn btn-sm btn-outline-success" title="Sửa">
                                                <i class="fas fa-pencil-alt me-2"></i><span class="d-none d-md-inline">Sửa</span>
                                            </a>
                                            <form action="{{ route('admin.guides.destroy', $guide) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa hướng dẫn này?')">
                                                    <i class="fas fa-trash me-2"></i><span class="d-none d-md-inline">Xóa</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-inbox text-muted mb-2" style="font-size: 3rem;"></i>
                                            <p class="text-muted mb-0">Chưa có hướng dẫn nào</p>
                                            <a href="{{ route('admin.guides.create') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="fas fa-plus me-2"></i>Thêm hướng dẫn mới
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($guides->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            <x-pagination :paginator="$guides" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

