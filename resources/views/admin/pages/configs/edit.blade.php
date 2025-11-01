@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                        <h6 class="mb-0">Chỉnh sửa cấu hình: <span class="d-none d-md-inline">{{ $config->key }}</span><span class="d-md-none">{{ Str::limit($config->key, 15) }}</span></h6>
                        <a href="{{ route('admin.configs.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i><span class="d-none d-md-inline">Quay lại</span><span class="d-md-none">Quay lại</span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.configs.update', $config->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="key" class="form-control-label">Khóa</label>
                            <input class="form-control" type="text" value="{{ $config->key }}" disabled>
                            <small class="form-text text-muted">Khóa không thể chỉnh sửa sau khi đã tạo</small>
                        </div>

                        <div class="form-group mt-3">
                            <label for="value" class="form-control-label">Giá trị <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('value') is-invalid @enderror" name="value" id="value" rows="3" required>{{ old('value', $config->value) }}</textarea>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mt-3">
                            <label for="description" class="form-control-label">Mô tả</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description" rows="3">{{ old('description', $config->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Mô tả ngắn gọn về mục đích của cấu hình này</small>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-end gap-2 mt-4">
                            <button type="submit" class="btn bg-gradient-primary btn-sm">
                                <i class="fas fa-save me-2"></i><span class="d-none d-md-inline">Cập nhật cấu hình</span><span class="d-md-none">Cập nhật</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection 