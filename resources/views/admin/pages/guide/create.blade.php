@extends('admin.layouts.app')

@section('title', 'Thêm hướng dẫn mới')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                        <h6 class="mb-0">Thêm hướng dẫn mới</h6>
                        <a href="{{ route('admin.guides.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i><span class="d-none d-md-inline">Quay lại</span><span class="d-md-none">Quay lại</span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @include('admin.pages.components.success-error')

                    <form action="{{ route('admin.guides.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="title" class="form-control-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="content" class="form-control-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="meta_description" class="form-control-label">Mô tả meta (SEO)</label>
                            <input type="text" class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" value="{{ old('meta_description') }}">
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="meta_keywords" class="form-control-label">Từ khóa meta (SEO)</label>
                            <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}">
                            @error('meta_keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="is_published" name="is_published" {{ old('is_published', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">Hiển thị</label>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-end gap-2 mt-4">
                            <button type="submit" class="btn bg-gradient-primary btn-sm">
                                <i class="fas fa-save me-2"></i><span class="d-none d-md-inline">Lưu hướng dẫn</span><span class="d-md-none">Lưu</span>
                            </button>
                            <a href="{{ route('admin.guides.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times me-2"></i><span class="d-none d-md-inline">Hủy</span><span class="d-md-none">Hủy</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts-admin')
<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<script>
    var editor = CKEDITOR.replace('content', {
        extraPlugins: 'image2,uploadimage,justify',
        uploadUrl: '{{ route('admin.guides.upload-image') }}',
        filebrowserUploadMethod: 'form',
        height: 500,
        image2_alignClasses: ['image-align-left', 'image-align-center', 'image-align-right'],
        image2_captionedClass: 'image-captioned',
        image2_removeClasses: ['image-align-left', 'image-align-center', 'image-align-right'],
        image2_captionPlaceholder: 'Nhập chú thích hình ảnh...',
        toolbarGroups: [
            { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
            { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
            { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
            { name: 'forms', groups: [ 'forms' ] },
            '/',
            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
            { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
            { name: 'links', groups: [ 'links' ] },
            { name: 'insert', groups: [ 'insert', 'image2' ] },
            '/',
            { name: 'styles', groups: [ 'styles' ] },
            { name: 'colors', groups: [ 'colors' ] },
            { name: 'tools', groups: [ 'tools' ] },
            { name: 'others', groups: [ 'others' ] },
            { name: 'about', groups: [ 'about' ] }
        ],
        removeButtons: 'Save,NewPage,ExportPdf,Preview,Print,Templates,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Language,BidiRtl,BidiLtr,Anchor,Flash,Smiley,SpecialChar,PageBreak,Iframe,ShowBlocks,About,Image'
    });

    // Handle CKEditor upload request - Add CSRF token
    editor.on('fileUploadRequest', function(evt) {
        var fileLoader = evt.data.fileLoader;
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Add CSRF token to form data
        if (fileLoader.formData === undefined) {
            fileLoader.formData = new FormData();
            fileLoader.formData.append('upload', fileLoader.file);
        }
        fileLoader.formData.append('_token', csrfToken);
        
        // Set CSRF token header
        fileLoader.xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
    });

    // Handle CKEditor upload response
    editor.on('fileUploadResponse', function(evt) {
        var fileLoader = evt.data.fileLoader;
        var xhr = fileLoader.xhr;
        
        // Stop the default response handler
        evt.stop();
        
        // Parse response
        var response = {};
        try {
            response = JSON.parse(xhr.responseText);
        } catch (e) {
            fileLoader.message = 'Lỗi khi upload hình ảnh: ' + xhr.responseText;
            return;
        }
        
        // Handle response
        if (response.uploaded === true) {
            fileLoader.url = response.url;
            fileLoader.uploaded = true;
        } else {
            fileLoader.uploaded = false;
            fileLoader.message = response.error ? response.error.message : 'Có lỗi xảy ra khi upload hình ảnh.';
        }
    });
</script>
@endpush

