@extends('layouts.information')

@section('info_title', 'Thêm nhiều chương')
@section('info_description', 'Thêm nhiều chương cho truyện ' . $story->title)
@section('info_keyword', 'thêm chương, đăng truyện, tác giả, ' . request()->getHost())
@section('info_section_title', 'Thêm nhiều chương')
@section('info_section_desc', 'Truyện: ' . $story->title)

@push('styles')
    <style>
        .batch-upload-area {
            display: block;
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

        .batch-format-example {
            background-color: #eee;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-line;
            margin-top: 10px;
            font-size: 0.9em;
        }

        .preview-chapters {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f8ff;
            border-radius: 5px;
            border: 1px solid #cce5ff;
        }

        .schedule-input {
            margin-top: 10px;
        }
    </style>
@endpush

@section('info_content')
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <a href="{{ route('user.author.stories.chapters', $story->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách chương
        </a>

        <a href="{{ route('user.author.stories.chapters.create', $story->id) }}" class="btn btn-sm btn-primary">
            <i class="fas fa-file-alt me-1"></i> Chuyển sang thêm một chương
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Thêm nhiều chương cùng lúc</h5>
        </div>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i> {!! session('error') !!}
                </div>
            @endif
            <div class="alert alert-info mb-4">
                <h5><i class="fas fa-info-circle me-2"></i>Hướng dẫn thêm nhiều chương</h5>
                <p class="mb-2">Copy & paste nội dung với định dạng sau:</p>
                <div class="batch-format-example">Chương 1: Hai con heo
                    Nội dung chương 1 ở đây...

                    Chương 2: Ba con chó
                    Nội dung chương 2 ở đây...

                    Chương 3: Bốn con mèo
                    Nội dung chương 3 ở đây...</div>
                <p class="mt-2">Hoặc định dạng không có tiêu đề:</p>
                <div class="batch-format-example">Chương 1
                    Nội dung chương 1 ở đây...

                    Chương 2
                    Nội dung chương 2 ở đây...</div>
                <ul class="mt-3 mb-0">
                    <li>Hệ thống sẽ <strong>giữ nguyên số chương</strong> từ tiêu đề (ví dụ: "Chương 5" sẽ được
                        lưu là chương số 5)</li>
                    <li>Nếu chương không có tiêu đề, hệ thống sẽ tự động đặt tiêu đề là "Chương X"</li>
                    <li>Các chương đã tồn tại (trùng số) sẽ được thông báo</li>
                    <li>Các chương có tiêu đề dẫn đến slug trùng lặp cũng sẽ được thông báo</li>
                    <li>Cài đặt về giá, mật khẩu và trạng thái sẽ áp dụng cho tất cả các chương mới</li>
                </ul>
            </div>

            <form action="{{ route('user.author.stories.chapters.batch.store', $story->id) }}" method="POST"
                id="batchChapterForm">
                @csrf

                <div class="mb-3">
                    <label for="batch_content" class="form-label">Nội dung các chương <span
                            class="text-danger">*</span></label>
                    <textarea class="form-control @error('batch_content') is-invalid @enderror" id="batch_content" name="batch_content"
                        rows="15">{{ old('batch_content') }}</textarea>
                    @error('batch_content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>



                <!-- Phần tùy chọn chương hàng loạt -->
                <div class="chapter-options">
                    <h5 class="mb-3">Tùy chọn cho tất cả các chương</h5>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <!-- Hình thức chương -->
                            <div class="mb-3">
                                <label class="form-label">Hình thức chương <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_free" id="is_free_yes"
                                        value="1" {{ old('is_free', '1') == '1' ? 'checked' : '' }}
                                        onchange="togglePricingOptions()">
                                    <label class="form-check-label" for="is_free_yes">
                                        <i class="fas fa-unlock text-success me-1"></i> Miễn phí
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_free" id="is_free_no"
                                        value="0" {{ old('is_free') == '0' ? 'checked' : '' }}
                                        onchange="togglePricingOptions()">
                                    <label class="form-check-label" for="is_free_no">
                                        <i class="fas fa-coins text-warning me-1"></i> Có phí
                                    </label>
                                </div>
                                @error('is_free')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <!-- Giá chương hàng loạt -->
                            <div class="pricing-options" id="pricingOptions">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Giá chương (Coin) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror"
                                        id="price" name="price" value="{{ old('price', 5) }}" min="1">
                                    <div class="form-text text-muted">Mức giá này sẽ áp dụng cho tất cả các chương.</div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>


                    </div>

                    <!-- Mật khẩu hàng loạt -->
                    <div class="password-options" id="passwordOptions">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Các chương có mật khẩu không?</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="has_password"
                                            id="has_password_yes" value="1"
                                            {{ old('has_password') == '1' ? 'checked' : '' }}
                                            onchange="togglePasswordField()">
                                        <label class="form-check-label" for="has_password_yes">
                                            <i class="fas fa-lock text-warning me-1"></i> Có mật khẩu
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="has_password"
                                            id="has_password_no" value="0"
                                            {{ old('has_password', '0') == '0' ? 'checked' : '' }}
                                            onchange="togglePasswordField()">
                                        <label class="form-check-label" for="has_password_no">
                                            <i class="fas fa-lock-open text-success me-1"></i> Không có mật khẩu
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="mb-3" id="passwordField"
                                    style="{{ old('has_password') == '1' ? '' : 'display: none;' }}">
                                    <label for="password" class="form-label">Mật khẩu chương <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password"
                                            class="form-control @error('password') is-invalid @enderror" id="password"
                                            name="password" value="{{ old('password') }}">
                                        <span class="input-group-text toggle-password"
                                            onclick="togglePasswordVisibility()">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                    <div class="form-text text-muted">Mật khẩu này sẽ áp dụng cho tất cả các chương.</div>
                                    <div class="form-text text-success">
                                        <i class="fas fa-lock me-1"></i> Mật khẩu sẽ được mã hóa an toàn trước khi lưu trữ.
                                    </div>
                                    @error('password')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tùy chọn xuất bản hàng loạt -->
                    <div class="publish-options">
                        <div class="mb-3">
                            <label class="form-label">Xuất bản chương <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_draft"
                                    value="draft" {{ old('status', 'draft') == 'draft' ? 'checked' : '' }}
                                    onchange="toggleScheduleOptions()">
                                <label class="form-check-label" for="status_draft">
                                    <i class="fas fa-edit text-secondary me-1"></i> Lưu nháp
                                </label>
                                <div class="form-text text-muted">Tất cả chương sẽ được lưu dưới dạng nháp.</div>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="status" id="status_published"
                                    value="published" {{ old('status') == 'published' ? 'checked' : '' }}
                                    onchange="toggleScheduleOptions()">
                                <label class="form-check-label" for="status_published">
                                    <i class="fas fa-check-circle text-success me-1"></i> Xuất bản ngay
                                </label>
                                <div class="form-text text-muted">Tất cả chương sẽ được công khai ngay sau khi lưu.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="scheduleOptionsContainer" style="{{ old('status') == 'published' ? 'display: none;' : '' }}">
                            <label class="form-label d-flex align-items-center">
                                <input type="checkbox" class="form-check-input me-2" id="enableSchedule"
                                    onchange="toggleScheduleField()" {{ old('scheduled_publish_at') ? 'checked' : '' }}>
                                Hẹn giờ xuất bản cho tất cả chương
                            </label>
                            <div id="scheduleField" style="{{ old('scheduled_publish_at') ? '' : 'display: none;' }}">
                                <input type="datetime-local"
                                    class="form-control @error('scheduled_publish_at') is-invalid @enderror"
                                    id="scheduled_publish_at" name="scheduled_publish_at"
                                    value="{{ old('scheduled_publish_at') }}">
                                <div class="form-text text-muted">Tất cả chương sẽ tự động xuất bản vào thời gian
                                    đã chọn (trừ khi có lịch riêng).</div>
                            </div>
                            @error('scheduled_publish_at')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="button" id="btnPreviewChapters" class="btn btn-secondary">
                                <i class="fas fa-eye me-1"></i> Xem trước các chương
                            </button>
                        </div>

                        <!-- Preview chapters section -->
                        <div id="previewChapters" class="preview-chapters mb-4 {{ old('chapters_count') ? '' : 'd-none' }}">
                            <h5 class="mb-3">Xem trước các chương</h5>
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i> Vui lòng kiểm tra các chương đã tách và
                                thiết lập lịch đăng (nếu cần)
                            </div>
                            <div id="chaptersPreviewContainer">
                                @if(old('chapters_count'))
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> Vui lòng nhấn "Xem trước các chương" để hiển thị lại các chương đã tách.
                                    </div>
                                @endif
                                <!-- Preview chapters will be dynamically inserted here -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('user.author.stories.chapters', $story->id) }}"
                        class="btn btn-secondary me-2">Hủy</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Lưu tất cả chương
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('info_scripts')
    <script>
        $(document).ready(function() {
            // Khởi tạo các hiển thị tùy thuộc vào trạng thái ban đầu
            togglePricingOptions();
            togglePasswordField();
            toggleScheduleOptions();

            // Kiểm tra trạng thái ban đầu của các trường mật khẩu
            if ($('#has_password_yes').is(':checked')) {
                $('#passwordField').show();
            }

            // Restore preview if validation errors occurred
            @if(old('chapters_count'))
                // Trigger preview generation as soon as the page loads if we had previous chapters
                setTimeout(function() {
                    $('#btnPreviewChapters').click();
                }, 500);
                
                // Restore individual chapter schedules
                @foreach(old('chapter_schedules', []) as $chapterNum => $schedule)
                    @if(!empty($schedule))
                        setTimeout(function() {
                            // Make sure chapter checkbox is checked and field is visible
                            $('#enableSchedule_{{ $chapterNum }}').prop('checked', true);
                            $('#scheduleField_{{ $chapterNum }}').removeClass('d-none');
                            // Set the value
                            $('#schedule_{{ $chapterNum }}').val('{{ $schedule }}');
                        }, 1000);
                    @endif
                @endforeach
            @endif

            // Xử lý xem trước các chương
            $('#btnPreviewChapters').click(function() {
                const batchContent = $('#batch_content').val();

                if (!batchContent.trim()) {
                    alert('Vui lòng nhập nội dung các chương trước khi xem trước.');
                    return;
                }

                // Hiển thị loading
                $('#btnPreviewChapters').html('<i class="fas fa-spinner fa-spin me-1"></i> Đang xử lý...');
                $('#btnPreviewChapters').prop('disabled', true);

                // Phân tích nội dung chương
                const chapters = parseChaptersFromContent(batchContent);

                if (chapters.length === 0) {
                    alert('Không thể tách nội dung thành các chương. Vui lòng kiểm tra định dạng.');
                    $('#btnPreviewChapters').html('<i class="fas fa-eye me-1"></i> Xem trước các chương');
                    $('#btnPreviewChapters').prop('disabled', false);
                    return;
                }

                displayParsedChapters(chapters);

                // Restore button state
                $('#btnPreviewChapters').html('<i class="fas fa-eye me-1"></i> Cập nhật xem trước');
                $('#btnPreviewChapters').prop('disabled', false);
            });

            // Function to parse chapters from content
            function parseChaptersFromContent(content) {
                const chapters = [];
                
                // First, normalize line endings
                const normalizedContent = content.replace(/\r\n/g, '\n');
                
                // Split content by chapter headings
                // This regex looks for "Chương X" at the start of a line
                const chapterBlocks = normalizedContent.split(/\n(?=Chương\s+\d+)/);
                
                // Process each chapter block
                chapterBlocks.forEach(block => {
                    if (!block.trim()) return; // Skip empty blocks
                    
                    // Extract the first line (chapter heading) and the rest (content)
                    const lines = block.trim().split('\n');
                    const headingLine = lines[0];
                    
                    // Match chapter number and optional title
                    const headingMatch = headingLine.match(/^Chương\s+(\d+)(?:\s*:\s*(.*))?$/);
                    
                    if (headingMatch) {
                        const chapterNumber = parseInt(headingMatch[1]);
                        const title = headingMatch[2] ? headingMatch[2].trim() : `Chương ${chapterNumber}`;
                        
                        // Content is everything after the first line
                        const content = lines.slice(1).join('\n').trim();
                        
                        chapters.push({
                            number: chapterNumber,
                            title: title,
                            content: content
                        });
                    }
                });
                
                return chapters;
            }

            // Function to display parsed chapters
            function displayParsedChapters(chapters) {
                const container = $('#chaptersPreviewContainer');
                container.empty();

                let html = '<div class="table-responsive"><table class="table table-bordered table-hover">';
                html +=
                    '<thead class="table-light"><tr><th>STT</th><th>Số chương</th><th>Tiêu đề</th><th>Độ dài nội dung</th><th>Hẹn giờ đăng riêng</th></tr></thead><tbody>';

                // Add info alert about scheduling when "Xuất bản ngay" is selected
                const isPublished = $('#status_published').is(':checked');
                if (isPublished) {
                    html = `
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i> <strong>Lưu ý:</strong> Bạn đã chọn "Xuất bản ngay" cho tất cả chương. 
                            Nếu bạn đặt lịch riêng cho một chương, chương đó sẽ được lưu ở trạng thái nháp và sẽ được xuất bản theo lịch.
                        </div>
                    ` + html;
                }

                chapters.forEach((chapter, index) => {
                    const contentLength = chapter.content.length;
                    const contentPreview = contentLength > 0 ?
                        `${contentLength} ký tự` :
                        '<span class="text-danger">Không có nội dung</span>';

                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>Chương ${chapter.number}</td>
                            <td>${chapter.title}</td>
                            <td>${contentPreview}</td>
                            <td>
                                <div class="form-check mb-2">
                                    <input class="form-check-input chapter-schedule-toggle" 
                                        type="checkbox" 
                                        id="enableSchedule_${chapter.number}" 
                                        data-chapter="${chapter.number}"
                                        onchange="toggleChapterSchedule(${chapter.number})">
                                    <label class="form-check-label" for="enableSchedule_${chapter.number}">
                                        Hẹn giờ riêng ${isPublished ? '<small class="text-info">(sẽ lưu nháp)</small>' : ''}
                                    </label>
                                </div>
                                <div class="schedule-input d-none" id="scheduleField_${chapter.number}">
                                    <input type="datetime-local" 
                                        class="form-control form-control-sm chapter-schedule-date" 
                                        id="schedule_${chapter.number}" 
                                        name="chapter_schedules[${chapter.number}]">
                                </div>
                            </td>
                        </tr>
                    `;
                });

                html += '</tbody></table></div>';
                html += '<input type="hidden" id="chapters_count" name="chapters_count" value="' + chapters.length +
                    '">';

                container.html(html);
                $('#previewChapters').removeClass('d-none');
            }
        });

        // Function to toggle individual chapter schedule
        function toggleChapterSchedule(chapterNumber) {
            const checkbox = $(`#enableSchedule_${chapterNumber}`);
            const scheduleField = $(`#scheduleField_${chapterNumber}`);

            if (checkbox.is(':checked')) {
                scheduleField.removeClass('d-none');
            } else {
                scheduleField.addClass('d-none');
                $(`#schedule_${chapterNumber}`).val('');
            }
        }

        // Xử lý các hiển thị cho form
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

        function toggleScheduleOptions() {
            var isDraft = $('#status_draft').is(':checked');
            if (isDraft) {
                $('#scheduleOptionsContainer').show();
            } else {
                $('#scheduleOptionsContainer').hide();
                // Uncheck and reset global schedule field when publishing immediately
                $('#enableSchedule').prop('checked', false);
                $('#scheduleField').hide();
                $('#scheduled_publish_at').val('');
            }
            
            // If preview is already visible, update it to reflect the new status
            if (!$('#previewChapters').hasClass('d-none') && $('#chaptersPreviewContainer').children().length > 0) {
                // Re-trigger the preview generation to update the UI
                $('#btnPreviewChapters').click();
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
            } else {
                $('#scheduleField').hide();
            }
        }

        // Validation before form submission
        $('#batchChapterForm').on('submit', function(e) {
            if ($('#has_password_yes').is(':checked') && $('#password').val() === '') {
                e.preventDefault();
                alert('Vui lòng nhập mật khẩu cho các chương.');
                $('#password').focus();
                return false;
            }

            const batchContent = $('#batch_content').val();
            if (!batchContent.trim()) {
                e.preventDefault();
                alert('Vui lòng nhập nội dung các chương.');
                $('#batch_content').focus();
                return false;
            }

            return true;
        });
    </script>
@endpush
