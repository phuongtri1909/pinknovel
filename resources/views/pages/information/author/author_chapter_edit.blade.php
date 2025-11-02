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

        .content-stats {
            background-color: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .content-stats .stat-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .content-stats .stat-number {
            font-weight: 600;
            color: #495057;
        }
    </style>
@endpush

@section('info_content')
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div>
            <a href="{{ route('user.author.stories.chapters', $story->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
            <a href="{{ route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug]) }}"
                class="btn btn-sm btn-outline-primary ms-2">
               Xem Chương
            </a>
        </div>

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
                    <label for="title" class="form-label">Tiêu đề chương </label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                        name="title" value="{{ old('title', $chapter->title) }}">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Nội dung chương <span class="text-danger">*</span></label>
            <textarea class="form-control rounded-4 @error('content') is-invalid @enderror" id="content" name="content"
                rows="25">{{ old('content', $chapter->content) }}</textarea>
            @error('content')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            <!-- Thêm thống kê từ và ký tự -->
            <div class="mt-2 d-flex justify-content-between">
                <div class="text-muted small">
                    <i class="fas fa-file-word me-1 text-primary"></i>
                    <span id="wordCount">0</span> từ
                </div>
                <div class="text-muted small">
                    <i class="fas fa-keyboard me-1 text-info"></i>
                    <span id="charCount">0</span> ký tự
                </div>
            </div>
        </div>

        <!-- Phần tùy chọn chương -->
        <div class="chapter-options">
            <h5 class="mb-3">Tùy chọn</h5>

            <div class="row">
                <!-- Cột trái: Hình thức chương + Giá/Mật khẩu -->
                <div class="col-12 col-md-6">
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
                    <div class="pricing-options mb-3" id="pricingOptions" style="display: none;">
                        <label for="price" class="form-label">Giá chương (Xu) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('price') is-invalid @enderror" id="price"
                            name="price" value="{{ old('price', $chapter->price ?? 5) }}" min="1">
                        <div class="form-text text-muted">Mức giá tối thiểu là 1 xu.</div>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Mật khẩu (hiển thị khi chọn Miễn phí) -->
                    <div id="passwordOptions">
                        <div class="mb-3">
                            <label class="form-label">Chương có mật khẩu không?</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="has_password" id="has_password_no"
                                    value="0"
                                    {{ old('has_password', !empty($chapter->password) ? '1' : '0') == '0' ? 'checked' : '' }}
                                    onchange="togglePasswordField()">
                                <label class="form-check-label" for="has_password_no">
                                    <i class="fas fa-lock-open text-success me-1"></i> Không có mật khẩu
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="has_password" id="has_password_yes"
                                    value="1"
                                    {{ old('has_password', !empty($chapter->password) ? '1' : '0') == '1' ? 'checked' : '' }}
                                    onchange="togglePasswordField()">
                                <label class="form-check-label" for="has_password_yes">
                                    <i class="fas fa-lock text-warning me-1"></i> Có mật khẩu
                                </label>
                            </div>
                        </div>

                        <div class="mb-3" id="passwordField"
                            style="{{ !empty($chapter->password) ? '' : 'display: none;' }}">
                            <label for="password" class="form-label">Mật khẩu chương <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password"
                                value="{{ old('password', $chapter->getDecryptedPassword() ?? '') }}"
                                placeholder="{{ !empty($chapter->password) ? 'Nhập mật khẩu mới hoặc để giữ nguyên' : 'Nhập mật khẩu' }}">
                            @if (!empty($chapter->password))
                                <div class="form-text text-muted mt-1">
                                    <i class="fas fa-info-circle me-1"></i> Mật khẩu hiện tại: <strong>{{ $chapter->getDecryptedPassword() }}</strong>. 
                                    Bạn có thể thay đổi hoặc giữ nguyên.
                                </div>
                            @else
                                <div class="form-text text-muted mt-1">Người đọc cần nhập đúng mật khẩu để xem chương này.</div>
                            @endif
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            
                            <div class="mt-2">
                                <label for="password_hint" class="form-label">Hướng dẫn lấy mật khẩu</label>
                                <textarea class="form-control @error('password_hint') is-invalid @enderror" 
                                    id="password_hint" name="password_hint" rows="2" 
                                    placeholder="Ví dụ: Nhập phép tính 1 + 1 = ? hoặc hướng dẫn tìm mật khẩu...">{{ old('password_hint', $chapter->password_hint) }}</textarea>
                                <div class="form-text text-muted">Hướng dẫn người đọc cách lấy mật khẩu để xem chương</div>
                                @error('password_hint')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cột phải: Xuất bản + Hẹn giờ -->
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Xuất bản chương <span class="text-danger">*</span></label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="status" id="status_published"
                                value="published"
                                {{ old('status', $chapter->status) == 'published' ? 'checked' : '' }}
                                onchange="toggleScheduleOptions()">
                            <label class="form-check-label" for="status_published">
                                <i class="fas fa-check-circle text-success me-1"></i> Xuất bản ngay
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="status_draft"
                                value="draft" {{ old('status', $chapter->status) == 'draft' ? 'checked' : '' }}
                                onchange="toggleScheduleOptions()">
                            <label class="form-check-label" for="status_draft">
                                <i class="fas fa-edit text-secondary me-1"></i> Lưu nháp
                            </label>
                        </div>
                    </div>

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
                            <!-- Hidden input để lưu giá trị datetime -->
                            <input type="hidden" id="scheduled_publish_at" name="scheduled_publish_at"
                                value="{{ old('scheduled_publish_at', $chapter->scheduled_publish_at ? $chapter->scheduled_publish_at->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i') : '') }}">
                            
                            <div class="mb-2 mt-2">
                                <label class="form-label small mb-1">Chương đầu</label>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <!-- Ngày -->
                                    <select class="form-select form-select-sm" id="schedule_day" style="width: auto; min-width: 80px;">
                                        <option value="">Ngày</option>
                                    </select>
                                    <!-- Tháng -->
                                    <select class="form-select form-select-sm" id="schedule_month" style="width: auto; min-width: 100px;">
                                        <option value="">Tháng</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" 
                                                @if($chapter->scheduled_publish_at)
                                                    {{ old('schedule_month', $chapter->scheduled_publish_at->setTimezone('Asia/Ho_Chi_Minh')->format('n')) == $i ? 'selected' : '' }}
                                                @else
                                                    {{ old('schedule_month', date('n')) == $i ? 'selected' : '' }}
                                                @endif
                                            >Tháng {{ $i }}</option>
                                        @endfor
                                    </select>
                                    <!-- Năm -->
                                    <select class="form-select form-select-sm" id="schedule_year" style="width: auto; min-width: 90px;">
                                        <option value="">Năm</option>
                                        @for($i = date('Y'); $i <= date('Y') + 5; $i++)
                                            <option value="{{ $i }}"
                                                @if($chapter->scheduled_publish_at)
                                                    {{ old('schedule_year', $chapter->scheduled_publish_at->setTimezone('Asia/Ho_Chi_Minh')->format('Y')) == $i ? 'selected' : '' }}
                                                @else
                                                    {{ old('schedule_year', date('Y')) == $i ? 'selected' : '' }}
                                                @endif
                                            >{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <span class="small">lúc</span>
                                    <!-- Giờ -->
                                    <select class="form-select form-select-sm" id="schedule_hour" style="width: auto; min-width: 80px;">
                                        <option value="">Giờ</option>
                                        @for($i = 0; $i <= 23; $i++)
                                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
                                                @if($chapter->scheduled_publish_at)
                                                    {{ old('schedule_hour', $chapter->scheduled_publish_at->setTimezone('Asia/Ho_Chi_Minh')->format('H')) == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}
                                                @else
                                                    {{ old('schedule_hour', date('H')) == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}
                                                @endif
                                            >{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                                        @endfor
                                    </select>
                                    <span>:</span>
                                    <!-- Phút -->
                                    <select class="form-select form-select-sm" id="schedule_minute" style="width: auto; min-width: 80px;">
                                        <option value="">Phút</option>
                                        @for($i = 0; $i <= 59; $i++)
                                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
                                                @if($chapter->scheduled_publish_at)
                                                    {{ old('schedule_minute', $chapter->scheduled_publish_at->setTimezone('Asia/Ho_Chi_Minh')->format('i')) == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}
                                                @else
                                                    {{ old('schedule_minute', date('i')) == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}
                                                @endif
                                            >{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="form-text text-muted">Chương sẽ tự động xuất bản vào thời gian đã chọn.</div>
                        </div>
                        @error('scheduled_publish_at')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('user.author.stories.chapters', $story->id) }}" class="btn btn-outline-danger me-2">Hủy</a>
            <button type="submit" class="btn btn-outline-dark">
                Cập nhật
            </button>
        </div>
    </form>
@endsection

@push('info_scripts')
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <script>
        $(document).ready(function() {
            togglePricingOptions();
            togglePasswordField();
            toggleScheduleOptions();

            updateContentStats();

            $('#content').on('input paste keyup', function() {
                updateContentStats();
            });
        });

        function countWords(text) {
            if (!text || typeof text !== 'string') return 0;

            const normalizedText = text.replace(/\s+/g, ' ').trim();

            if (normalizedText === '') return 0;

            const words = normalizedText.split(' ').filter(word => word.length > 0);

            return words.length;
        }

        function updateContentStats() {
            const content = $('#content').val();
            const wordCount = countWords(content);
            const charCount = content.length;

            $('#wordCount').text(wordCount.toLocaleString());
            $('#charCount').text(charCount.toLocaleString());

            const wordCountElement = $('#wordCount');
            const charCountElement = $('#charCount');

            wordCountElement.removeClass('text-danger text-warning text-success');
            charCountElement.removeClass('text-danger text-warning text-success');

            if (wordCount === 0) {
                wordCountElement.addClass('text-danger');
            } else if (wordCount < 100) {
                wordCountElement.addClass('text-warning');
            } else {
                wordCountElement.addClass('text-success');
            }

            if (charCount === 0) {
                charCountElement.addClass('text-danger');
            } else if (charCount < 500) {
                charCountElement.addClass('text-warning');
            } else {
                charCountElement.addClass('text-success');
            }
        }

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
                $('#enableSchedule').prop('checked', false);
                $('#scheduleField').hide();
                $('#scheduled_publish_at').val('');
            }
        }

        function toggleScheduleField() {
            var enableSchedule = $('#enableSchedule').is(':checked');
            if (enableSchedule) {
                $('#scheduleField').show();
                updateScheduleDays();
                updateScheduleDateTime();
            } else {
                $('#scheduleField').hide();
                $('#scheduled_publish_at').val('');
            }
        }
        
        function updateScheduleDays() {
            var month = parseInt($('#schedule_month').val());
            var year = parseInt($('#schedule_year').val());
            var selectedDay = parseInt($('#schedule_day').val());
            
            if (month && year) {
                var daysInMonth = new Date(year, month, 0).getDate();
                var $daySelect = $('#schedule_day');
                var currentValue = $daySelect.val();
                
                $daySelect.empty();
                $daySelect.append('<option value="">Ngày</option>');
                
                for (var i = 1; i <= daysInMonth; i++) {
                    var selected = (currentValue && i == parseInt(currentValue)) ? 'selected' : '';
                    $daySelect.append('<option value="' + String(i).padStart(2, '0') + '" ' + selected + '>' + String(i).padStart(2, '0') + '</option>');
                }
                
                if (selectedDay && selectedDay > daysInMonth) {
                    $daySelect.val('');
                }
            }
        }
        
        function updateScheduleDateTime() {
            var day = $('#schedule_day').val();
            var month = $('#schedule_month').val();
            var year = $('#schedule_year').val();
            var hour = $('#schedule_hour').val();
            var minute = $('#schedule_minute').val();
            
            if (day && month && year && hour !== '' && minute !== '') {
                var datetime = year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0') + 'T' + String(hour).padStart(2, '0') + ':' + String(minute).padStart(2, '0');
                
                var selectedDate = new Date(datetime);
                var now = new Date();
                
                if (selectedDate <= now) {
                    now.setMinutes(now.getMinutes() + 1);
                    var newYear = now.getFullYear();
                    var newMonth = String(now.getMonth() + 1).padStart(2, '0');
                    var newDay = String(now.getDate()).padStart(2, '0');
                    var newHour = String(now.getHours()).padStart(2, '0');
                    var newMinute = String(now.getMinutes()).padStart(2, '0');
                    
                    $('#schedule_day').val(newDay);
                    $('#schedule_month').val(parseInt(newMonth));
                    $('#schedule_year').val(newYear);
                    $('#schedule_hour').val(newHour);
                    $('#schedule_minute').val(newMinute);
                    
                    datetime = newYear + '-' + newMonth + '-' + newDay + 'T' + newHour + ':' + newMinute;
                }
                
                $('#scheduled_publish_at').val(datetime);
            } else {
                $('#scheduled_publish_at').val('');
            }
        }
        
        $(document).ready(function() {
            var oldDateTime = $('#scheduled_publish_at').val();
            
            if (oldDateTime) {
                var parts = oldDateTime.match(/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/);
                if (parts) {
                    $('#schedule_month').val(parseInt(parts[2]));
                    $('#schedule_year').val(parts[1]);
                    updateScheduleDays();
                    $('#schedule_day').val(parts[3]);
                    $('#schedule_hour').val(parts[4]);
                    $('#schedule_minute').val(parts[5]);
                }
            } else {
                @php
                    $now = now('Asia/Ho_Chi_Minh');
                @endphp
                $('#schedule_month').val('{{ $now->format('n') }}');
                $('#schedule_year').val('{{ $now->format('Y') }}');
                updateScheduleDays();
                $('#schedule_day').val('{{ $now->format('d') }}');
                $('#schedule_hour').val('{{ $now->format('H') }}');
                $('#schedule_minute').val('{{ $now->format('i') }}');
            }
            
            updateScheduleDateTime();
            
            $('#schedule_month, #schedule_year').on('change', function() {
                updateScheduleDays();
                updateScheduleDateTime();
            });
            
            $('#schedule_day, #schedule_hour, #schedule_minute').on('change', function() {
                updateScheduleDateTime();
            });
            
            $('form').on('submit', function() {
                if ($('#enableSchedule').is(':checked') && $('#status_draft').is(':checked')) {
                    updateScheduleDateTime();
                }
            });
        });
    </script>
@endpush
