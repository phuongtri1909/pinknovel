@extends('admin.layouts.app')

@section('content-auth')
<div class="row">
    <div class="col-12">
        <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
            <div class="card-header pb-0">
                <h5 class="mb-0">Chỉnh sửa Banner</h5>
            </div>
            <div class="card-body">
                @include('admin.pages.components.success-error')

                <form action="{{ route('banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="image" class="form-control-label">Hình ảnh Banner</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                       name="image" id="image" accept="image/*">
                                <small class="form-text text-muted">
                                    Kích thước tối ưu: 1920x500px. Để trống để giữ nguyên hình ảnh cũ.
                                </small>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            @if($banner->image)
                                <div class="mt-3 mb-4">
                                    <p>Hình ảnh hiện tại:</p>
                                    <img src="{{ Storage::url($banner->image) }}" alt="Current Banner" class="img-fluid" style="max-height: 200px;">
                                </div>
                            @endif
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="story_id">Liên kết đến truyện (tùy chọn)</label>
                                <select name="story_id" id="story_id" class="form-control" onchange="toggleLinkField()">
                                    <option value="">-- Không liên kết truyện --</option>
                                    @foreach($stories as $story)
                                        <option value="{{ $story->id }}" {{ old('story_id', $banner->story_id) == $story->id ? 'selected' : '' }}>
                                            {{ $story->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('story_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12" id="link_field">
                            <div class="form-group">
                                <label for="link" class="form-control-label">Liên kết URL</label>
                                <input type="url" class="form-control @error('link') is-invalid @enderror"
                                       name="link" id="link" value="{{ old('link', $banner->link) }}" placeholder="https://">
                                <small class="form-text text-muted">
                                    URL liên kết bắt buộc khi không chọn truyện.
                                </small>
                                @error('link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="link_aff" class="form-label">Link Affiliate</label>
                                <input type="url" class="form-control" id="link_aff" name="link_aff"
                                    value="{{ old('link_aff', $banner->link_aff ?? '') }}"
                                    placeholder="Nhập link affiliate (Shopee, TikTok...)">
                                @error('link_aff')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="status">Trạng thái</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="status" id="status" value="1"
                                           {{ old('status', $banner->status) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status">Hiển thị</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn bg-gradient-primary">Cập nhật</button>
                            <a href="{{ route('banners.index') }}" class="btn btn-outline-secondary">Quay lại</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleLinkField() {
        const storyIdSelect = document.getElementById('story_id');
        const linkField = document.getElementById('link_field');
        const linkInput = document.getElementById('link');
        
        if (storyIdSelect.value) {
            linkField.style.display = 'none';
            linkInput.removeAttribute('required');
        } else {
            linkField.style.display = 'block';
            linkInput.setAttribute('required', 'required');
        }
    }
    
    // Run on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleLinkField();
    });
</script>
@endpush
@endsection