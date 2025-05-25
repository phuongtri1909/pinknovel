@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Chỉnh sửa truyện</h5>
                </div>
                <div class="card-body">
                    @include('admin.pages.components.success-error')

                    <form action="{{ route('stories.update', $story) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="title">Tiêu đề</label>
                                    <input type="text" name="title" id="title"
                                        class="form-control @error('title') is-invalid @enderror"
                                        value="{{ old('title', $story->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description">Mô tả</label>
                                    <textarea name="description" id="description" rows="5"
                                        class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $story->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="author_name">Tên tác giả</label>
                                            <input type="text" name="author_name" id="author_name"
                                                class="form-control @error('author_name') is-invalid @enderror"
                                                value="{{ old('author_name', $story->author_name) }}">
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
                                                value="{{ old('translator_name', $story->translator_name) }}">
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
                                        <option value="original" {{ old('story_type', $story->story_type) == 'original' ? 'selected' : '' }}>Sáng tác</option>
                                        <option value="translated" {{ old('story_type', $story->story_type) == 'translated' ? 'selected' : '' }}>Dịch</option>
                                        <option value="rewritten" {{ old('story_type', $story->story_type) == 'rewritten' ? 'selected' : '' }}>Chuyển ngữ</option>
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
                                        onchange="previewImage(this)">
                                    <div id="cover-preview" class="mt-2">
                                        @if ($story->cover)
                                            <img src="{{ Storage::url($story->cover) }}" class="img-thumbnail"
                                                style="max-height: 200px">
                                        @endif
                                    </div>
                                    @error('cover')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="categories">Thể loại (tối đa 4)</label>
                                    <select name="categories[]" id="categories" class="form-control" multiple required>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ in_array($category->id, $story->categories->pluck('id')->toArray()) ? 'selected' : '' }}>
                                                {{ $category->name }}
                                                @if($category->is_main)
                                                    ⭐ (Thể loại chính)
                                                @endif
                                            </option>
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
                                        <option value="draft" {{ $story->status === 'draft' ? 'selected' : '' }}>Bản nháp
                                        </option>
                                        <option value="published" {{ $story->status === 'published' ? 'selected' : '' }}>
                                            Xuất bản</option>
                                    </select>
                                </div>

                                <div class="form-group mt-3">
                                    <div class="d-flex align-items-center">
                                        <label class="mb-0 me-3" for="completed">Truyện đã hoàn thành</label>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" name="completed" class="form-check-input" id="completed"
                                                role="switch" {{ $story->completed ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <div class="d-flex align-items-center">
                                        <label class="mb-0 me-3" for="has_combo">Bán combo (tất cả chương)</label>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" name="has_combo" class="form-check-input" id="has_combo"
                                                role="switch" {{ $story->has_combo ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                <div id="combo-pricing-container" class="mt-3" style="{{ $story->has_combo ? '' : 'display: none;' }}">
                                    <div class="form-group">
                                        <label for="combo_price">Giá combo (xu)</label>
                                        <input type="number" name="combo_price" id="combo_price" 
                                               class="form-control @error('combo_price') is-invalid @enderror"
                                               value="{{ old('combo_price', $story->combo_price) }}" min="0">
                                        @error('combo_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    @php
                                        $totalChapters = $story->chapters()->where('status', 'published')->count();
                                        $paidChapters = $story->chapters()->where('status', 'published')->where('is_free', 0)->count();
                                        $totalRegularPrice = $story->chapters()->where('status', 'published')->where('is_free', 0)->sum('price');
                                        $savings = $totalRegularPrice > 0 ? $totalRegularPrice - ($story->combo_price ?? 0) : 0;
                                        $savingsPercent = $totalRegularPrice > 0 ? round(($savings / $totalRegularPrice) * 100) : 0;
                                    @endphp

                                    <div class="alert alert-info mt-3" id="combo-summary">
                                        <h6>Thông tin combo:</h6>
                                        <p class="mb-1">- Tổng số chương: <span id="total-chapters">{{ $totalChapters }}</span></p>
                                        <p class="mb-1">- Số chương trả phí: <span id="paid-chapters">{{ $paidChapters }}</span></p>
                                        <p class="mb-1">- Tổng giá nếu mua lẻ: <span id="total-regular-price">{{ $totalRegularPrice }}</span> xu</p>
                                        <p class="mb-1">- Tiết kiệm: <span id="savings">{{ $savings }}</span> xu (<span id="savings-percent">{{ $savingsPercent }}</span>%)</p>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <div class="d-flex align-items-center">
                                        <label class="mb-0 me-3" for="is_18_plus">Nội dung 18+</label>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" name="is_18_plus" class="form-check-input" id="is_18_plus"
                                                role="switch" {{ old('is_18_plus', $story->is_18_plus) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <div class="d-flex align-items-center">
                                        <label class="mb-0 me-3" for="is_monopoly">Truyện độc quyền</label>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" name="is_monopoly" class="form-check-input" id="is_monopoly"
                                                role="switch" {{ old('is_monopoly', $story->is_monopoly) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn bg-gradient-primary">Cập nhật</button>
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

            // Check initial state
            checkCategoryLimit();

            categoriesSelect.addEventListener('change', checkCategoryLimit);

            function checkCategoryLimit() {
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
            }

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
                    updateComboSummary();
                } else {
                    comboPricingContainer.style.display = 'none';
                    comboPriceInput.value = '0';
                }
            });
            
            // Update combo summary calculations when price changes
            comboPriceInput.addEventListener('input', updateComboSummary);
            
            function updateComboSummary() {
                const totalRegularPrice = parseInt(document.getElementById('total-regular-price').textContent) || 0;
                const comboPrice = parseInt(comboPriceInput.value) || 0;
                
                const savings = totalRegularPrice - comboPrice;
                const savingsPercent = totalRegularPrice > 0 ? Math.round((savings / totalRegularPrice) * 100) : 0;
                
                document.getElementById('savings').textContent = savings > 0 ? savings : 0;
                document.getElementById('savings-percent').textContent = savingsPercent > 0 ? savingsPercent : 0;
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

@push('styles')
    <style>
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            cursor: pointer;
        }

        .form-switch .form-check-input:checked {
            background-color: #2dce89;
            border-color: #2dce89;
        }

        .form-switch .form-check-input:focus {
            border-color: rgba(45, 206, 137, 0.25);
            box-shadow: 0 0 0 0.2rem rgba(45, 206, 137, 0.25);
        }
    </style>
@endpush
