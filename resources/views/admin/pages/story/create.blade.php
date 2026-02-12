@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Thêm truyện mới</h5>
                </div>
                <div class="card-body">
                    @include('admin.pages.components.success-error')

                    <form action="{{ route('stories.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="title">Tiêu đề</label>
                                    <input type="text" name="title" id="title"
                                        class="form-control @error('title') is-invalid @enderror"
                                        value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description">Mô tả</label>
                                    <textarea name="description" id="description" rows="5"
                                        class="form-control @error('description') is-invalid @enderror" required placeholder="Nhập mô tả (văn bản thuần)...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="story_notice">Thông báo truyện <span class="text-muted">(không bắt buộc)</span></label>
                                    <textarea name="story_notice" id="story_notice" rows="5"
                                        class="form-control @error('story_notice') is-invalid @enderror">{{ old('story_notice') }}</textarea>
                                    <small class="text-muted">Thông báo sẽ hiển thị dưới nội dung mỗi chương của truyện. Có thể chèn ảnh, link và định dạng văn bản.</small>
                                    @error('story_notice')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="author_name">Tên tác giả</label>
                                            <input type="text" name="author_name" id="author_name"
                                                class="form-control @error('author_name') is-invalid @enderror"
                                                value="{{ old('author_name') }}">
                                            @error('author_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="translator_name">Tên dịch giả</label>
                                            <input type="text" name="translator_name" id="translator_name"
                                                class="form-control @error('translator_name') is-invalid @enderror"
                                                value="{{ old('translator_name') }}">
                                            @error('translator_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="story_type">Loại truyện</label>
                                    <select name="story_type" id="story_type" class="form-control @error('story_type') is-invalid @enderror">
                                        <option value="">-- Chọn loại truyện --</option>
                                        <option value="original" {{ old('story_type') == 'original' ? 'selected' : '' }}>Sáng tác</option>
                                        <option value="translated" {{ old('story_type') == 'translated' ? 'selected' : '' }}>Dịch</option>
                                        <option value="rewritten" {{ old('story_type') == 'rewritten' ? 'selected' : '' }}>Chuyển ngữ</option>
                                    </select>
                                    @error('story_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cover">Ảnh bìa</label>
                                    <input type="file" name="cover" id="cover"
                                        class="form-control @error('cover') is-invalid @enderror"
                                        onchange="previewImage(this)" required>
                                    <div id="cover-preview" class="mt-2"></div>
                                    @error('cover')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="categories">Thể loại</label>
                                    <div class="category-tags-container">
                                        <div class="selected-categories mb-2" id="selected-categories">
                                            <!-- Selected categories will appear here -->
                                        </div>
                                        <select id="category-select" class="form-control">
                                            <option value="">-- Chọn thể loại --</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">
                                                    {{ $category->name }}
                                                    @if($category->is_main)
                                                        ⭐ (Thể loại chính)
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <!-- Hidden inputs will be generated dynamically by JavaScript -->
                                    </div>
                                    @error('categories')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="link_aff" class="form-label">Link Affiliate</label>
                                    <input type="url" class="form-control" id="link_aff" name="link_aff"
                                        value="{{ old('link_aff') }}"
                                        placeholder="Nhập link affiliate (Shopee, TikTok...)">
                                    @error('link_aff')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status">Trạng thái</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="draft">Bản nháp</option>
                                        <option value="published" selected>Xuất bản</option>
                                    </select>
                                </div>

                                <div class="form-group mt-3">
                                    <div class="d-flex align-items-center">
                                        <label class="mb-0 me-3" for="has_combo">Bán combo (tất cả chương)</label>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" name="has_combo" class="form-check-input" id="has_combo"
                                                role="switch">
                                        </div>
                                    </div>
                                </div>

                                <div id="combo-pricing-container" class="mt-3" style="display: none;">
                                    <div class="form-group">
                                        <label for="combo_price">Giá combo (xu)</label>
                                        <input type="number" name="combo_price" id="combo_price" 
                                               class="form-control @error('combo_price') is-invalid @enderror"
                                               value="{{ old('combo_price', 0) }}" min="0">
                                        <small class="text-muted">Đặt giá combo cho tất cả các chương</small>
                                        @error('combo_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="alert alert-info mt-3">
                                        <p class="mb-0"><i class="fas fa-info-circle me-1"></i> Bạn có thể thiết lập giá combo sau khi thêm các chương cho truyện.</p>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <div class="d-flex align-items-center">
                                        <label class="mb-0 me-3" for="is_18_plus">Nội dung 18+</label>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" name="is_18_plus" class="form-check-input" id="is_18_plus"
                                                role="switch" {{ old('is_18_plus') ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <div class="d-flex align-items-center">
                                        <label class="mb-0 me-3" for="is_monopoly">Truyện độc quyền</label>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" name="is_monopoly" class="form-check-input" id="is_monopoly"
                                                role="switch" {{ old('is_monopoly') ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn bg-gradient-primary">Lưu truyện</button>
                                <a href="{{ route('stories.index') }}" class="btn btn-outline-secondary">Quay lại</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts-admin')
    <script>
        function previewImage(input) {
            const preview = document.getElementById('cover-preview');
            preview.innerHTML = '';

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('img-thumbnail', 'mt-2');
                    img.style.maxHeight = '200px';
                    preview.appendChild(img);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category-select');
            const selectedCategoriesContainer = document.getElementById('selected-categories');

            // Initialize hidden inputs for existing categories (if any from old input)
            updateCategoriesInput();

            // Add category when select changes
            categorySelect.addEventListener('change', function() {
                const selectedValue = this.value;
                if (selectedValue) {
                    addCategory(selectedValue, this.options[this.selectedIndex].text);
                    this.value = ''; // Reset select
                }
            });

            // Form validation before submit
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                const selectedCategories = document.querySelectorAll('.category-tag');
                const categoryTagsContainer = document.querySelector('.category-tags-container');
                
                if (selectedCategories.length === 0) {
                    event.preventDefault();
                    categoryTagsContainer.classList.add('is-invalid');
                    categorySelect.classList.add('is-invalid');
                    return false;
                } else {
                    categoryTagsContainer.classList.remove('is-invalid');
                    categorySelect.classList.remove('is-invalid');
                }

                // Ensure hidden inputs are updated before submit
                updateCategoriesInput();
                
                // Debug: Log the hidden inputs
                const hiddenInputs = document.querySelectorAll('input[name="categories[]"]');
                console.log('Hidden inputs before submit:', Array.from(hiddenInputs).map(input => input.value));

                return true;
            });
            
            // Handle combo pricing toggle
            const hasComboCheckbox = document.getElementById('has_combo');
            const comboPricingContainer = document.getElementById('combo-pricing-container');
            const comboPriceInput = document.getElementById('combo_price');
            
            hasComboCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    comboPricingContainer.style.display = 'block';
                } else {
                    comboPricingContainer.style.display = 'none';
                    comboPriceInput.value = '0';
                }
            });
            
            // Initialize based on old input if form was submitted with errors
            if ("{{ old('has_combo') }}" === '1') {
                hasComboCheckbox.checked = true;
                comboPricingContainer.style.display = 'block';
            }
        });

        // Add category function
        function addCategory(categoryId, categoryName) {
            const selectedCategoriesContainer = document.getElementById('selected-categories');
            const categorySelect = document.getElementById('category-select');
            
            // Check if category already exists
            if (document.querySelector(`[data-category-id="${categoryId}"]`)) {
                return;
            }
            
            // Create tag element
            const tag = document.createElement('span');
            tag.className = 'badge bg-primary me-2 mb-2 category-tag';
            tag.setAttribute('data-category-id', categoryId);
            tag.innerHTML = `${categoryName} <button type="button" class="btn-close btn-close-white ms-1" onclick="removeCategory(${categoryId})"></button>`;
            
            // Add to container
            selectedCategoriesContainer.appendChild(tag);
            
            // Disable option in select
            const option = categorySelect.querySelector(`option[value="${categoryId}"]`);
            if (option) {
                option.disabled = true;
            }
            
            // Update hidden input
            updateCategoriesInput();
        }
        
        // Remove category function
        function removeCategory(categoryId) {
            const tag = document.querySelector(`[data-category-id="${categoryId}"]`);
            const categorySelect = document.getElementById('category-select');
            
            if (tag) {
                tag.remove();
                
                // Enable option in select
                const option = categorySelect.querySelector(`option[value="${categoryId}"]`);
                if (option) {
                    option.disabled = false;
                }
                
                // Update hidden input
                updateCategoriesInput();
            }
        }
        
        // Update hidden inputs with selected categories
        function updateCategoriesInput() {
            const selectedCategories = document.querySelectorAll('.category-tag');
            const categoryIds = Array.from(selectedCategories).map(tag => tag.getAttribute('data-category-id'));
            
            // Remove existing hidden inputs
            const existingInputs = document.querySelectorAll('input[name="categories[]"]');
            existingInputs.forEach(input => input.remove());
            
            // Create new hidden inputs for each category ID
            categoryIds.forEach(categoryId => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'categories[]';
                hiddenInput.value = categoryId;
                document.querySelector('.category-tags-container').appendChild(hiddenInput);
            });
        }
    </script>

    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <script>
        // CKEditor for story notice
        CKEDITOR.replace('story_notice', {
            extraPlugins: 'image2,uploadimage,justify',
            uploadUrl: '{{ route('user.author.stories.upload-image') }}',
            filebrowserUploadMethod: 'form',
            height: 200,
            image2_alignClasses: ['image-align-left', 'image-align-center', 'image-align-right'],
            toolbarGroups: [
                { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
                { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'paragraph' ] },
                { name: 'links', groups: [ 'links' ] },
                { name: 'insert', groups: [ 'insert', 'image2' ] },
                { name: 'styles', groups: [ 'styles' ] },
                { name: 'colors', groups: [ 'colors' ] },
                { name: 'tools', groups: [ 'tools' ] }
            ],
            removeButtons: 'Save,NewPage,ExportPdf,Preview,Print,Templates,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Language,BidiRtl,BidiLtr,Anchor,Flash,Smiley,SpecialChar,PageBreak,Iframe,ShowBlocks,About,Image'
        });

        CKEDITOR.instances.story_notice.on('fileUploadRequest', function(evt) {
            var fileLoader = evt.data.fileLoader;
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            if (fileLoader.formData === undefined) {
                fileLoader.formData = new FormData();
                fileLoader.formData.append('upload', fileLoader.file);
            }
            fileLoader.formData.append('_token', csrfToken);
            fileLoader.xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        });

        CKEDITOR.instances.story_notice.on('fileUploadResponse', function(evt) {
            var fileLoader = evt.data.fileLoader;
            var xhr = fileLoader.xhr;
            
            evt.stop();
            
            var response = {};
            try {
                response = JSON.parse(xhr.responseText);
            } catch (e) {
                fileLoader.message = 'Lỗi khi upload hình ảnh: ' + xhr.responseText;
                return;
            }
            
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

@push('styles')
    <style>
        /* Category Tags Styles */
        .category-tags-container {
            border: 1px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 0.75rem;
            background-color: #f8f9fa;
        }

        .selected-categories {
            min-height: 40px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }

        .category-tag {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            background-color: #5e72e4 !important;
            color: white;
            border: none;
            cursor: default;
        }

        .category-tag .btn-close {
            font-size: 0.75rem;
            margin-left: 0.5rem;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .category-tag .btn-close:hover {
            opacity: 1;
        }

        .category-tag .btn-close:focus {
            box-shadow: none;
        }

        #category-select {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }

        #category-select:focus {
            border-color: #5e72e4;
            box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, 0.25);
        }

        .category-tags-container.is-invalid #category-select {
            border-color: #dc3545;
        }

        .category-tags-container.is-invalid .selected-categories {
            border-color: #dc3545;
        }
    </style>
@endpush
