@extends('admin.layouts.app')

@section('title', 'Chi tiết Hướng dẫn')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                        <h6 class="mb-0">Chi tiết hướng dẫn: {{ $guide->title }}</h6>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <a href="{{ route('admin.guides.edit', $guide) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-pencil-alt me-2"></i><span class="d-none d-md-inline">Chỉnh sửa</span><span class="d-md-none">Sửa</span>
                            </a>
                            <a href="{{ route('admin.guides.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i><span class="d-none d-md-inline">Quay lại</span><span class="d-md-none">Quay lại</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-sm mb-1">Tiêu đề:</h6>
                            <p class="text-sm mb-3">{{ $guide->title }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-sm mb-1">Slug:</h6>
                            <p class="text-sm mb-3">
                                <code>{{ $guide->slug }}</code>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-sm mb-1">Trạng thái:</h6>
                            <p class="mb-3">
                                @if($guide->is_published)
                                    <span class="badge bg-gradient-success">Đang hiển thị</span>
                                @else
                                    <span class="badge bg-gradient-secondary">Ẩn</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-sm mb-1">Ngày tạo:</h6>
                            <p class="text-sm mb-3">{{ $guide->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    
                    @if($guide->meta_description)
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="text-sm mb-1">Mô tả meta (SEO):</h6>
                            <p class="text-sm mb-3">{{ $guide->meta_description }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($guide->meta_keywords)
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="text-sm mb-1">Từ khóa meta (SEO):</h6>
                            <p class="text-sm mb-3">{{ $guide->meta_keywords }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="text-sm mb-1">Nội dung:</h6>
                            <div class="border rounded p-3 bg-light">
                                {!! $guide->content !!}
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex flex-column flex-md-row justify-content-end gap-2 mt-4">
                                <a href="{{ route('admin.guides.edit', $guide) }}" class="btn bg-gradient-primary btn-sm">
                                    <i class="fas fa-pencil-alt me-2"></i><span class="d-none d-md-inline">Chỉnh sửa</span><span class="d-md-none">Sửa</span>
                                </a>
                                <form action="{{ route('admin.guides.destroy', $guide) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn bg-gradient-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa hướng dẫn này?')">
                                        <i class="fas fa-trash me-2"></i><span class="d-none d-md-inline">Xóa</span><span class="d-md-none">Xóa</span>
                                    </button>
                                </form>
                                <a href="{{ route('admin.guides.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left me-2"></i><span class="d-none d-md-inline">Quay lại</span><span class="d-md-none">Quay lại</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<style>
    .card-body h6 {
        font-weight: 600;
        color: #344767;
    }
    .card-body .bg-light {
        background-color: #f8f9fa !important;
    }
</style>
@endsection

