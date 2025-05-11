@extends('layouts.information')

@section('info_title', 'Thêm chương mới')
@section('info_description', 'Thêm chương mới cho truyện ' . $story->title)
@section('info_keyword', 'thêm chương, đăng truyện, tác giả, ' . request()->getHost())
@section('info_section_title', 'Thêm chương mới')
@section('info_section_desc', 'Truyện: ' . $story->title)

@push('styles')
<style>
    .cke_contents {
        min-height: 300px;
    }
    .batch-upload-area {
        display: none;
    }
    .chapter-options {
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .toggle-password {
        cursor: pointer;
    }
    .pricing-options, .password-options, .publish-options {
        padding-top: 15px;
    }
    .form-text.text-muted {
        font-size: 0.85em;
    }
    .batch-format-example {
        background-color: #eee;
        padding: 15px;
        border-radius: 5px;
        font-family: monospace;
        white-space: pre-line;
        margin-top: 10px;
        font-size: 0.9em;
    }
</style>
@endpush

@section('info_content')
    <div class="mb-4">
        <a href="{{ route('user.author.stories.chapters', $story->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách chương
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="uploadTypeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single-upload" 
                        type="button" role="tab" aria-selected="true">
                        <i class="fas fa-file-alt me-1"></i> Thêm một chương
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="batch-tab" data-bs-toggle="tab" data-bs-target="#batch-upload" 
                        type="button" role="tab" aria-selected="false">
                        <i class="fas fa-layer-group me-1"></i> Thêm nhiều chương
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="uploadTypeTabContent">
                <!-- Tab Thêm một chương -->
                <div class="tab-pane fade show active" id="single-upload" role="tabpanel" aria-labelledby="single-tab">
                    <form action="{{ route('user.author.stories.chapters.store', $story->id) }}" method="POST" id="singleChapterForm">
                        @csrf
                        <input type="hidden" name="upload_type" value="single">
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="number" class="form-label">Số chương <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('number') is-invalid @enderror" 
                                        id="number" name="number" value="{{ old('number', $nextChapterNumber) }}" required>
                                    @error('number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Tiêu đề chương <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                        id="title" name="title" value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Nội dung chương <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                id="content" name="content">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phần tùy chọn chương -->
                        <div class="chapter-options">
                            <h5 class="mb-3">Tùy chọn chương</h5>
                            
                            <!-- Hình thức chương -->
                            <div class="mb-3">
                                <label class="form-label">Hình thức chương <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_free" id="is_free_yes" value="1" 
                                        {{ old('is_free', '1') == '1' ? 'checked' : '' }} onchange="togglePricingOptions()">
                                    <label class="form-check-label" for="is_free_yes">
                                        <i class="fas fa-unlock text-success me-1"></i> Miễn phí
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_free" id="is_free_no" value="0" 
                                        {{ old('is_free') == '0' ? 'checked' : '' }} onchange="togglePricingOptions()">
                                    <label class="form-check-label" for="is_free_no">
                                        <i class="fas fa-coins text-warning me-1"></i> Có phí
                                    </label>
                                </div>
                                @error('is_free')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Giá chương (hiển thị khi chọn Có phí) -->
                            <div class="pricing-options" id="pricingOptions">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Giá chương (Coin) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                        id="price" name="price" value="{{ old('price', 5) }}" min="1">
                                    <div class="form-text text-muted">Mức giá tối thiểu là 1 coin.</div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Mật khẩu (hiển thị khi chọn Miễn phí) -->
                            <div class="password-options" id="passwordOptions">
                                <div class="mb-3">
                                    <label class="form-label">Chương có mật khẩu không?</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="has_password" id="has_password_yes" value="1" 
                                            {{ old('has_password') == '1' ? 'checked' : '' }} onchange="togglePasswordField()">
                                        <label class="form-check-label" for="has_password_yes">
                                            <i class="fas fa-lock text-warning me-1"></i> Có mật khẩu
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="has_password" id="has_password_no" value="0" 
                                            {{ old('has_password', '0') == '0' ? 'checked' : '' }} onchange="togglePasswordField()">
                                        <label class="form-check-label" for="has_password_no">
                                            <i class="fas fa-lock-open text-success me-1"></i> Không có mật khẩu
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3" id="passwordField" style="display: none;">
                                    <label for="password" class="form-label">Mật khẩu chương <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                            id="password" name="password" value="{{ old('password') }}">
                                        <span class="input-group-text toggle-password" onclick="togglePasswordVisibility()">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                    <div class="form-text text-muted">Người đọc cần nhập đúng mật khẩu để xem chương này.</div>
                                    @error('password')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Tùy chọn xuất bản -->
                            <div class="publish-options">
                                <div class="mb-3">
                                    <label class="form-label">Xuất bản chương <span class="text-danger">*</span></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_draft" value="draft" 
                                            {{ old('status', 'draft') == 'draft' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_draft">
                                            <i class="fas fa-edit text-secondary me-1"></i> Lưu nháp
                                        </label>
                                        <div class="form-text text-muted">Chương sẽ được lưu dưới dạng nháp, chỉ tác giả mới xem được.</div>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="radio" name="status" id="status_published" value="published" 
                                            {{ old('status') == 'published' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_published">
                                            <i class="fas fa-check-circle text-success me-1"></i> Xuất bản ngay
                                        </label>
                                        <div class="form-text text-muted">Chương sẽ được công khai ngay sau khi lưu.</div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label d-flex align-items-center">
                                        <input type="checkbox" class="form-check-input me-2" id="enableSchedule" 
                                            onchange="toggleScheduleField()" {{ old('scheduled_publish_at') ? 'checked' : '' }}>
                                        Hẹn giờ xuất bản
                                    </label>
                                    <div id="scheduleField" style="{{ old('scheduled_publish_at') ? '' : 'display: none;' }}">
                                        <input type="datetime-local" class="form-control @error('scheduled_publish_at') is-invalid @enderror" 
                                            id="scheduled_publish_at" name="scheduled_publish_at" value="{{ old('scheduled_publish_at') }}">
                                        <div class="form-text text-muted">Chương sẽ tự động xuất bản vào thời gian đã chọn.</div>
                                    </div>
                                    @error('scheduled_publish_at')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('user.author.stories.chapters', $story->id) }}" class="btn btn-secondary me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Lưu chương
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Tab Thêm nhiều chương -->
                <div class="tab-pane fade" id="batch-upload" role="tabpanel" aria-labelledby="batch-tab">
                    <div class="alert alert-info mb-4">
                        <h5><i class="fas fa-info-circle me-2"></i>Hướng dẫn thêm nhiều chương</h5>
                        <p class="mb-2">Copy & paste nội dung với định dạng sau:</p>
                        <div class="batch-format-example">[Chương 1] : Hai con heo
Nội dung chương 1 ở đây...

[Chương 2] : Ba con chó
Nội dung chương 2 ở đây...

[Chương 3] : Bốn con mèo
Nội dung chương 3 ở đây...</div>
                        <ul class="mt-3 mb-0">
                            <li>Hệ thống hỗ trợ cả hai định dạng: <code>[Chương X] : Tiêu đề</code> (mới) và <code>Chương X: Tiêu đề</code> (cũ)</li>
                            <li>Hệ thống sẽ <strong>giữ nguyên số chương</strong> từ tiêu đề (ví dụ: "[Chương 5]" sẽ được lưu là chương số 5)</li>
                            <li>Các chương đã tồn tại (trùng số) sẽ được bỏ qua và thông báo</li>
                            <li>Các chương có tiêu đề dẫn đến slug trùng lặp cũng sẽ được bỏ qua và thông báo</li>
                            <li>Cài đặt về giá, mật khẩu và trạng thái sẽ áp dụng cho tất cả các chương mới</li>
                        </ul>
                    </div>
                
                    <form action="{{ route('user.author.stories.chapters.store', $story->id) }}" method="POST" id="batchChapterForm">
                        @csrf
                        <input type="hidden" name="upload_type" value="multiple">
                        
                        <div class="mb-3">
                            <label for="chapter_prefix" class="form-label">Tiền tố tiêu đề chương (tùy chọn)</label>
                            <input type="text" class="form-control @error('chapter_prefix') is-invalid @enderror" 
                                id="chapter_prefix" name="chapter_prefix" value="{{ old('chapter_prefix', 'Chương') }}" 
                                placeholder="Ví dụ: Chương, Chapter, Hồi, ...">
                            <div class="form-text text-muted">Nếu để trống, hệ thống sẽ tự tìm kiếm các định dạng phổ biến như "Chương", "Chapter", "Chap".</div>
                            @error('chapter_prefix')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="batch_content" class="form-label">Nội dung các chương <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('batch_content') is-invalid @enderror" 
                                id="batch_content" name="batch_content" rows="15">{{ old('batch_content') }}</textarea>
                            @error('batch_content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Phần tùy chọn chương hàng loạt -->
                        <div class="chapter-options">
                            <h5 class="mb-3">Tùy chọn cho tất cả các chương</h5>
                            
                            <!-- Hình thức chương -->
                            <div class="mb-3">
                                <label class="form-label">Hình thức chương <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_free" id="batch_is_free_yes" value="1" 
                                        {{ old('is_free', '1') == '1' ? 'checked' : '' }} onchange="toggleBatchPricingOptions()">
                                    <label class="form-check-label" for="batch_is_free_yes">
                                        <i class="fas fa-unlock text-success me-1"></i> Miễn phí
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_free" id="batch_is_free_no" value="0" 
                                        {{ old('is_free') == '0' ? 'checked' : '' }} onchange="toggleBatchPricingOptions()">
                                    <label class="form-check-label" for="batch_is_free_no">
                                        <i class="fas fa-coins text-warning me-1"></i> Có phí
                                    </label>
                                </div>
                                @error('is_free')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Giá chương hàng loạt -->
                            <div class="pricing-options" id="batchPricingOptions">
                                <div class="mb-3">
                                    <label for="batch_price" class="form-label">Giá chương (Coin) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                        id="batch_price" name="price" value="{{ old('price', 5) }}" min="1">
                                    <div class="form-text text-muted">Mức giá này sẽ áp dụng cho tất cả các chương.</div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Mật khẩu hàng loạt -->
                            <div class="password-options" id="batchPasswordOptions">
                                <div class="mb-3">
                                    <label class="form-label">Các chương có mật khẩu không?</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="has_password" id="batch_has_password_yes" value="1" 
                                            {{ old('has_password') == '1' ? 'checked' : '' }} onchange="toggleBatchPasswordField()">
                                        <label class="form-check-label" for="batch_has_password_yes">
                                            <i class="fas fa-lock text-warning me-1"></i> Có mật khẩu
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="has_password" id="batch_has_password_no" value="0" 
                                            {{ old('has_password', '0') == '0' ? 'checked' : '' }} onchange="toggleBatchPasswordField()">
                                        <label class="form-check-label" for="batch_has_password_no">
                                            <i class="fas fa-lock-open text-success me-1"></i> Không có mật khẩu
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3" id="batchPasswordField" style="display: none;">
                                    <label for="batch_password" class="form-label">Mật khẩu chương <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                            id="batch_password" name="password" value="{{ old('password') }}">
                                        <span class="input-group-text toggle-password" onclick="toggleBatchPasswordVisibility()">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                    <div class="form-text text-muted">Mật khẩu này sẽ áp dụng cho tất cả các chương.</div>
                                    @error('password')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Tùy chọn xuất bản hàng loạt -->
                            <div class="publish-options">
                                <div class="mb-3">
                                    <label class="form-label">Xuất bản chương <span class="text-danger">*</span></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="batch_status_draft" value="draft" 
                                            {{ old('status', 'draft') == 'draft' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="batch_status_draft">
                                            <i class="fas fa-edit text-secondary me-1"></i> Lưu nháp
                                        </label>
                                        <div class="form-text text-muted">Tất cả chương sẽ được lưu dưới dạng nháp.</div>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="radio" name="status" id="batch_status_published" value="published" 
                                            {{ old('status') == 'published' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="batch_status_published">
                                            <i class="fas fa-check-circle text-success me-1"></i> Xuất bản ngay
                                        </label>
                                        <div class="form-text text-muted">Tất cả chương sẽ được công khai ngay sau khi lưu.</div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label d-flex align-items-center">
                                        <input type="checkbox" class="form-check-input me-2" id="batchEnableSchedule" 
                                            onchange="toggleBatchScheduleField()" {{ old('scheduled_publish_at') ? 'checked' : '' }}>
                                        Hẹn giờ xuất bản
                                    </label>
                                    <div id="batchScheduleField" style="{{ old('scheduled_publish_at') ? '' : 'display: none;' }}">
                                        <input type="datetime-local" class="form-control @error('scheduled_publish_at') is-invalid @enderror" 
                                            id="batch_scheduled_publish_at" name="scheduled_publish_at" value="{{ old('scheduled_publish_at') }}">
                                        <div class="form-text text-muted">Tất cả chương sẽ tự động xuất bản vào thời gian đã chọn.</div>
                                    </div>
                                    @error('scheduled_publish_at')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('user.author.stories.chapters', $story->id) }}" class="btn btn-secondary me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Lưu tất cả chương
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('info_scripts')
<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<script>
    $(document).ready(function() {
        CKEDITOR.replace('content', {
            on: {
                change: function(evt) {
                    this.updateElement();
                }
            },
            height: 400,
            removePlugins: 'uploadimage,image2,uploadfile,filebrowser',
        });
        
        // Khởi tạo các hiển thị tùy thuộc vào trạng thái ban đầu
        togglePricingOptions();
        togglePasswordField();
        toggleBatchPricingOptions();
        toggleBatchPasswordField();
        
        // Xử lý nếu có tab được chỉ định trong hash URL
        let hash = window.location.hash;
        if (hash && hash === '#batch-upload') {
            $('#batch-tab').tab('show');
        }
    });
    
    // Xử lý các hiển thị cho tab 1 chương
    function togglePricingOptions() {
        var isFree = $('#is_free_yes').is(':checked');
        if (isFree) {
            $('#pricingOptions').hide();
            $('#passwordOptions').show();
        } else {
            $('#pricingOptions').show();
            $('#passwordOptions').hide();
        }
    }
    
    function togglePasswordField() {
        var hasPassword = $('#has_password_yes').is(':checked');
        if (hasPassword) {
            $('#passwordField').show();
        } else {
            $('#passwordField').hide();
        }
    }
    
    function togglePasswordVisibility() {
        var passwordField = document.getElementById('password');
        var icon = document.querySelector('#passwordField .toggle-password i');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    function toggleScheduleField() {
        var enableSchedule = $('#enableSchedule').is(':checked');
        if (enableSchedule) {
            $('#scheduleField').show();
            if ($('#status_draft').is(':checked')) {
                $('#status_published').prop('checked', true);
            }
        } else {
            $('#scheduleField').hide();
        }
    }
    
    // Xử lý các hiển thị cho tab nhiều chương
    function toggleBatchPricingOptions() {
        var isFree = $('#batch_is_free_yes').is(':checked');
        if (isFree) {
            $('#batchPricingOptions').hide();
            $('#batchPasswordOptions').show();
        } else {
            $('#batchPricingOptions').show();
            $('#batchPasswordOptions').hide();
        }
    }
    
    function toggleBatchPasswordField() {
        var hasPassword = $('#batch_has_password_yes').is(':checked');
        if (hasPassword) {
            $('#batchPasswordField').show();
        } else {
            $('#batchPasswordField').hide();
        }
    }
    
    function toggleBatchPasswordVisibility() {
        var passwordField = document.getElementById('batch_password');
        var icon = document.querySelector('#batchPasswordField .toggle-password i');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    function toggleBatchScheduleField() {
        var enableSchedule = $('#batchEnableSchedule').is(':checked');
        if (enableSchedule) {
            $('#batchScheduleField').show();
            if ($('#batch_status_draft').is(':checked')) {
                $('#batch_status_published').prop('checked', true);
            }
        } else {
            $('#batchScheduleField').hide();
        }
    }
</script>
@endpush