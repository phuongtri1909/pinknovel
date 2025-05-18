@extends('layouts.information')

@section('info_title', 'Đăng truyện mới')
@section('info_description', 'Đăng truyện mới trên ' . request()->getHost())
@section('info_keyword', 'đăng truyện, tác giả, sáng tác, ' . request()->getHost())
@section('info_section_title', 'Đăng truyện mới')
@section('info_section_desc', 'Tạo truyện mới của bạn')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .preview-cover {
            max-width: 140px;
            max-height: 180px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .cover-container {
            position: relative;
            width: fit-content;
        }

        .cover-container .btn-remove {
            position: absolute;
            top: -10px;
            right: -10px;
            background: white;
            border-radius: 50%;
            color: #dc3545;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dc3545;
            cursor: pointer;
        }
    </style>
@endpush

@section('info_content')
    <div class="mb-4">
        <a href="{{ route('user.author.stories') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <form action="{{ route('user.author.stories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="title" class="form-label">Tiêu đề truyện <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                        name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="category_input" class="form-label">Thể loại <span class="text-danger">*</span></label>
                    <p class="text-muted small">Nhập các thể loại phân cách bởi dấu phẩy (,). Ví dụ: Tình cảm, Hài hước,
                        Viễn tưởng</p>
                    <input type="text" class="form-control @error('category_input') is-invalid @enderror"
                        id="category_input" name="category_input" value="{{ old('category_input') }}" required>
                    @error('category_input')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description2" name="description"
                        rows="6">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3">
                    <label for="author_name" class="form-label">Tên tác giả <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('author_name') is-invalid @enderror" id="author_name"
                        name="author_name" value="{{ old('author_name') }}" required>
                    @error('author_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="story_type" class="form-label">Loại truyện <span class="text-danger">*</span></label>
                    <select class="form-select @error('story_type') is-invalid @enderror" id="story_type" name="story_type"
                        required>
                        <option value="">-- Chọn loại truyện --</option>
                        <option value="original" {{ old('story_type') == 'original' ? 'selected' : '' }}>Sáng tác</option>
                        <option value="translated" {{ old('story_type') == 'translated' ? 'selected' : '' }}>Dịch/Edit
                        </option>
                        <option value="collected" {{ old('story_type') == 'collected' ? 'selected' : '' }}>Sưu tầm</option>
                    </select>
                    @error('story_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="translator_name" class="form-label">Chuyển ngữ <span class="text-muted">(nếu
                            có)</span></label>
                    <input type="text" class="form-control @error('translator_name') is-invalid @enderror"
                        id="translator_name" name="translator_name" value="{{ old('translator_name') }}">
                    <div class="form-text text-muted">Điền nếu đây là truyện dịch hoặc sưu tầm</div>
                    @error('translator_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 mt-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_18_plus" name="is_18_plus" value="1"
                            {{ old('is_18_plus') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_18_plus">
                            <span class="badge bg-danger me-1"><i class="fas fa-exclamation-triangle"></i> 18+</span>
                            Truyện có nội dung người lớn
                        </label>
                        <div class="form-text text-danger">
                            Chọn nếu truyện có nội dung nhạy cảm, bạo lực hoặc tình dục không phù hợp với độc giả dưới 18
                            tuổi.
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="cover" class="form-label">Ảnh bìa <span class="text-danger">*</span></label>
                    <input type="file" class="form-control @error('cover') is-invalid @enderror" id="cover"
                        name="cover" accept="image/*" required>
                    @error('cover')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div class="mt-3 cover-preview d-none">
                        <div class="cover-container">
                            <img src="" class="preview-cover" id="coverPreview">
                            <span class="btn-remove" id="removeCover"><i class="fas fa-times"></i></span>
                        </div>
                    </div>
                    <div class="text-muted mt-2 small">
                        <i class="fas fa-info-circle"></i> Tỷ lệ ảnh tốt nhất là 2:3, kích thước tối thiểu 300x450 pixels.
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('user.author.stories') }}" class="btn btn-secondary me-2">Hủy</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Đăng truyện
            </button>
        </div>
    </form>
@endsection

@push('info_scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Xử lý preview ảnh bìa
            $('#cover').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#coverPreview').attr('src', e.target.result);
                        $('.cover-preview').removeClass('d-none');
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Xử lý xóa ảnh bìa
            $('#removeCover').click(function() {
                $('#cover').val('');
                $('.cover-preview').addClass('d-none');
            });

            // Thay thế Summernote bằng CKEditor
            CKEDITOR.replace('description2', {
                on: {
                    change: function(evt) {
                        this.updateElement();
                    }
                },
                height: 200,
                removePlugins: 'uploadimage,image2,uploadfile,filebrowser',
            });
        });
    </script>
@endpush
