@extends('layouts.information')

@section('info_title', 'Chỉnh sửa chương')
@section('info_description', 'Chỉnh sửa chương của truyện ' . $story->title)
@section('info_keyword', 'sửa chương, đăng truyện, tác giả, ' . request()->getHost())
@section('info_section_title', 'Chỉnh sửa chương')
@section('info_section_desc', 'Truyện: ' . $story->title)

@push('styles')
    <style>
        .cke_contents {
            min-height: 300px;
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

        .pricing-options,
        .password-options,
        .publish-options {
            padding-top: 15px;
        }

        .form-text.text-muted {
            font-size: 0.85em;
        }
    </style>
@endpush

@section('info_content')
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <a href="{{ route('user.author.stories.chapters', $story->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>

        <div class="d-flex gap-2">
            @if ($prevChapter)
                <a href="{{ route('user.author.stories.chapters.edit', [$story->id, $prevChapter->id]) }}"
                    class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-angle-left me-1"></i> Chương trước
                </a>
            @endif

            @if ($nextChapter)
                <a href="{{ route('user.author.stories.chapters.edit', [$story->id, $nextChapter->id]) }}"
                    class="btn btn-sm btn-outline-primary">
                    Chương sau <i class="fas fa-angle-right ms-1"></i>
                </a>
            @endif
        </div>
    </div>


    <form action="{{ route('user.author.stories.chapters.update', ['story' => $story->id, 'chapter' => $chapter->id]) }}"
        method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="number" class="form-label">Số chương <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('number') is-invalid @enderror" id="number"
                        name="number" value="{{ old('number', $chapter->number) }}" required>
                    @error('number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-8">
                <div class="mb-3">
                    <label for="title" class="form-label">Tiêu đề chương <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                        name="title" value="{{ old('title', $chapter->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Nội dung chương <span class="text-danger">*</span></label>
            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content">{{ old('content', $chapter->content) }}</textarea>
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
                        {{ old('is_free', $chapter->is_free ? '1' : '0') == '1' ? 'checked' : '' }}
                        onchange="togglePricingOptions()">
                    <label class="form-check-label" for="is_free_yes">
                        <i class="fas fa-unlock text-success me-1"></i> Miễn phí
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="is_free" id="is_free_no" value="0"
                        {{ old('is_free', $chapter->is_free ? '1' : '0') == '0' ? 'checked' : '' }}
                        onchange="togglePricingOptions()">
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
                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price"
                        name="price" value="{{ old('price', $chapter->price ?? 5) }}" min="1">
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
                        <input class="form-check-input" type="radio" name="has_password" id="has_password_yes"
                            value="1"
                            {{ old('has_password', !empty($chapter->password) ? '1' : '0') == '1' ? 'checked' : '' }}
                            onchange="togglePasswordField()">
                        <label class="form-check-label" for="has_password_yes">
                            <i class="fas fa-lock text-warning me-1"></i> Có mật khẩu
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_password" id="has_password_no"
                            value="0"
                            {{ old('has_password', !empty($chapter->password) ? '1' : '0') == '0' ? 'checked' : '' }}
                            onchange="togglePasswordField()">
                        <label class="form-check-label" for="has_password_no">
                            <i class="fas fa-lock-open text-success me-1"></i> Không có mật khẩu
                        </label>
                    </div>
                </div>

                <div class="mb-3" id="passwordField" style="{{ !empty($chapter->password) ? '' : 'display: none;' }}">
                    <label for="password" class="form-label">Mật khẩu chương <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" name="password"
                            placeholder="{{ !empty($chapter->password) ? '********' : '' }}">
                        <span class="input-group-text toggle-password" onclick="togglePasswordVisibility()">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    @if (!empty($chapter->password))
                        <div class="form-text text-muted">
                            <i class="fas fa-info-circle me-1"></i> Chương này đã có mật khẩu. Để giữ nguyên mật khẩu hiện
                            tại, hãy để trống trường này.
                        </div>
                    @else
                        <div class="form-text text-muted">Người đọc cần nhập đúng mật khẩu để xem chương này.</div>
                    @endif
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
                            {{ old('status', $chapter->status) == 'draft' ? 'checked' : '' }}
                            onchange="toggleScheduleOptions()">
                        <label class="form-check-label" for="status_draft">
                            <i class="fas fa-edit text-secondary me-1"></i> Lưu nháp
                        </label>
                        <div class="form-text text-muted">Chương sẽ được lưu dưới dạng nháp, chỉ tác giả mới xem được.
                        </div>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" name="status" id="status_published"
                            value="published" {{ old('status', $chapter->status) == 'published' ? 'checked' : '' }}
                            onchange="toggleScheduleOptions()">
                        <label class="form-check-label" for="status_published">
                            <i class="fas fa-check-circle text-success me-1"></i> Xuất bản ngay
                        </label>
                        <div class="form-text text-muted">Chương sẽ được công khai ngay sau khi lưu.</div>
                    </div>
                </div>

                <!-- Thêm tùy chọn lịch xuất bản -->
                <div class="mb-3" id="scheduleOptionsContainer"
                    style="{{ old('status', $chapter->status) == 'published' ? 'display: none;' : '' }}">
                    <label class="form-label d-flex align-items-center">
                        <input type="checkbox" class="form-check-input me-2" id="enableSchedule"
                            onchange="toggleScheduleField()"
                            {{ old('scheduled_publish_at', $chapter->scheduled_publish_at) ? 'checked' : '' }}>
                        Hẹn giờ xuất bản
                    </label>
                    <div id="scheduleField"
                        style="{{ old('scheduled_publish_at', $chapter->scheduled_publish_at) ? '' : 'display: none;' }}">
                        <input type="datetime-local"
                            class="form-control @error('scheduled_publish_at') is-invalid @enderror"
                            id="scheduled_publish_at" name="scheduled_publish_at"
                            value="{{ old('scheduled_publish_at', $chapter->scheduled_publish_at ? $chapter->scheduled_publish_at->format('Y-m-d\TH:i') : '') }}">
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
                <i class="fas fa-save me-1"></i> Cập nhật chương
            </button>
        </div>
    </form>
@endsection

@push('info_scripts')
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Khởi tạo hiển thị tùy thuộc vào trạng thái ban đầu
            togglePricingOptions();
            togglePasswordField();
            toggleScheduleOptions();
        });

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

        function toggleScheduleOptions() {
            var isDraft = $('#status_draft').is(':checked');
            if (isDraft) {
                $('#scheduleOptionsContainer').show();
            } else {
                $('#scheduleOptionsContainer').hide();
                // Uncheck and reset schedule fields when publishing immediately
                $('#enableSchedule').prop('checked', false);
                $('#scheduleField').hide();
                $('#scheduled_publish_at').val('');
            }
        }

        function togglePasswordVisibility() {
            var passwordField = document.getElementById('password');
            var icon = document.querySelector('.toggle-password i');

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
            } else {
                $('#scheduleField').hide();
            }
        }
    </script>
@endpush
