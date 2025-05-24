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
                                        class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                                    @error('description')
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
                                    <label for="categories">Thể loại (tối đa 4)</label>
                                    <select name="categories[]" id="categories" class="form-control" multiple required>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Chọn tối đa 4 thể loại</small>
                                    <div class="invalid-feedback category-limit-error" style="display: none;">
                                        Bạn chỉ được chọn tối đa 4 thể loại
                                    </div>
                                    @error('categories')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="link_aff" class="form-label">Link Affiliate</label>
                                    <input type="url" class="form-control" id="link_aff" name="link_aff"
                                        value="{{ old('link_aff', $story->link_aff ?? '') }}"
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
            const MAX_CATEGORIES = 4;
            const categoriesSelect = document.getElementById('categories');
            const categoryLimitError = document.querySelector('.category-limit-error');
            const submitButton = document.querySelector('button[type="submit"]');

            categoriesSelect.addEventListener('change', function() {
                const selectedOptions = Array.from(categoriesSelect.selectedOptions);

                if (selectedOptions.length > MAX_CATEGORIES) {
                    categoryLimitError.style.display = 'block';
                    submitButton.disabled = true;

                    // Deselect the last selected option
                    for (let i = MAX_CATEGORIES; i < selectedOptions.length; i++) {
                        selectedOptions[i].selected = false;
                    }
                } else {
                    categoryLimitError.style.display = 'none';
                    submitButton.disabled = false;
                }
            });

            // Form validation before submit
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                const selectedOptions = Array.from(categoriesSelect.selectedOptions);

                if (selectedOptions.length > MAX_CATEGORIES) {
                    event.preventDefault();
                    categoryLimitError.style.display = 'block';
                    return false;
                }

                if (selectedOptions.length === 0) {
                    event.preventDefault();
                    categoriesSelect.classList.add('is-invalid');
                    return false;
                }

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
    </script>

    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <script>
        CKEDITOR.replace('description', {
            on: {
                change: function(evt) {
                    this.updateElement();
                }
            },
            height: 200,
            removePlugins: 'uploadimage,image2,uploadfile,filebrowser',
        });
    </script>
@endpush
