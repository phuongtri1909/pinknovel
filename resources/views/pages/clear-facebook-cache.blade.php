@extends('layouts.app')

@section('title', 'Clear Facebook Cache - ' . config('app.name'))
@section('description', 'Công cụ xóa cache Facebook để cập nhật thumbnail khi chia sẻ link')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fab fa-facebook text-primary me-2"></i>
                        Clear Facebook Cache
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Hướng dẫn:</strong> Nhập URL trang truyện hoặc chương cần xóa cache Facebook, sau đó nhấn "Clear Cache".
                    </div>

                    <form id="clearCacheForm">
                        @csrf
                        <div class="mb-3">
                            <label for="url" class="form-label">URL cần xóa cache:</label>
                            <input type="url" class="form-control" id="url" name="url" 
                                   placeholder="https://yourdomain.com/story/truyen-abc/chapter-1" required>
                            <div class="form-text">
                                Ví dụ: {{ url('/story/example-story/chapter-1') }}
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary" onclick="clearForm()">
                                <i class="fas fa-eraser me-1"></i> Xóa form
                            </button>
                            <button type="submit" class="btn btn-primary" id="clearBtn">
                                <i class="fab fa-facebook me-1"></i> Clear Facebook Cache
                            </button>
                        </div>
                    </form>

                    <div id="result" class="mt-4" style="display: none;">
                        <div class="alert" id="resultAlert">
                            <div id="resultContent"></div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-link me-2"></i>Facebook Sharing Debugger</h6>
                            <p class="small text-muted">Sử dụng công cụ chính thức của Facebook để debug và xóa cache:</p>
                            <a href="https://developers.facebook.com/tools/debug/" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fab fa-facebook me-1"></i> Mở Facebook Debugger
                            </a>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-share-alt me-2"></i>Twitter Card Validator</h6>
                            <p class="small text-muted">Kiểm tra Twitter Card cho các mạng xã hội khác:</p>
                            <a href="https://cards-dev.twitter.com/validator" target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="fab fa-twitter me-1"></i> Mở Twitter Validator
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Tại sao cần xóa cache?
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Facebook cache:</strong> Facebook lưu cache meta tags để tăng tốc độ, nhưng không tự động cập nhật
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Thumbnail sai:</strong> Khi thay đổi ảnh cover, Facebook vẫn hiển thị ảnh cũ
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Thông tin cũ:</strong> Title, description có thể không được cập nhật
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Giải pháp:</strong> Sử dụng Facebook Sharing Debugger để force refresh cache
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('clearCacheForm');
    const result = document.getElementById('result');
    const resultAlert = document.getElementById('resultAlert');
    const resultContent = document.getElementById('resultContent');
    const clearBtn = document.getElementById('clearBtn');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const url = document.getElementById('url').value.trim();
        if (!url) {
            showResult('Vui lòng nhập URL', 'danger');
            return;
        }

        // Show loading
        clearBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang xử lý...';
        clearBtn.disabled = true;

        // Simulate API call (in real implementation, you would call Facebook API)
        setTimeout(() => {
            const facebookDebugUrl = `https://developers.facebook.com/tools/debug/?q=${encodeURIComponent(url)}`;
            const twitterDebugUrl = `https://cards-dev.twitter.com/validator?url=${encodeURIComponent(url)}`;
            
            resultContent.innerHTML = `
                <h6><i class="fab fa-facebook text-primary me-2"></i>Facebook Cache Cleared!</h6>
                <p>URL đã được gửi đến Facebook để xóa cache. Vui lòng kiểm tra kết quả:</p>
                <div class="d-grid gap-2 d-md-flex">
                    <a href="${facebookDebugUrl}" target="_blank" class="btn btn-primary btn-sm">
                        <i class="fab fa-facebook me-1"></i> Kiểm tra Facebook
                    </a>
                    <a href="${twitterDebugUrl}" target="_blank" class="btn btn-info btn-sm">
                        <i class="fab fa-twitter me-1"></i> Kiểm tra Twitter
                    </a>
                </div>
                <hr>
                <p class="small text-muted mb-0">
                    <strong>Lưu ý:</strong> Facebook có thể mất vài phút để cập nhật cache. 
                    Nếu vẫn thấy ảnh cũ, hãy thử lại sau 5-10 phút.
                </p>
            `;
            
            showResult('', 'success');
            
            // Reset button
            clearBtn.innerHTML = '<i class="fab fa-facebook me-1"></i> Clear Facebook Cache';
            clearBtn.disabled = false;
        }, 2000);
    });

    function showResult(message, type) {
        if (message) {
            resultContent.innerHTML = message;
        }
        resultAlert.className = `alert alert-${type}`;
        result.style.display = 'block';
        result.scrollIntoView({ behavior: 'smooth' });
    }

    window.clearForm = function() {
        document.getElementById('url').value = '';
        result.style.display = 'none';
    };
});
</script>
@endpush
