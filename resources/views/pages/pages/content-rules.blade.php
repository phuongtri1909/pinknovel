@extends('layouts.app')

@section('title', 'Quy định về nội dung - Content Rules - ' . config('app.name'))

@section('description', 'Quy định về nội dung của ' . config('app.name') . '. Tìm hiểu các quy tắc và hướng dẫn về nội dung được phép và bị cấm trên nền tảng.')

@section('keyword', 'quy định nội dung, content rules, nội dung bị cấm, quy tắc đăng bài, hướng dẫn tác giả, ' . config('app.name'))

@section('meta')
    <meta property="og:type" content="website">
    <meta property="og:title" content="Quy định về nội dung - Content Rules - {{ config('app.name') }}">
    <meta property="og:description" content="Quy định về nội dung của {{ config('app.name') }}. Tìm hiểu các quy tắc và hướng dẫn về nội dung được phép và bị cấm trên nền tảng.">
    <meta property="og:image" content="{{ url(asset('assets/images/logo/logo_site.webp')) }}">
    <meta property="og:image:secure_url" content="{{ url(asset('assets/images/logo/logo_site.webp')) }}">
    <meta property="og:image:width" content="600">
    <meta property="og:image:height" content="600">
    <meta property="og:image:alt" content="Logo {{ config('app.name') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="vi_VN">

    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Quy định về nội dung - Content Rules - {{ config('app.name') }}">
    <meta name="twitter:description" content="Quy định về nội dung của {{ config('app.name') }}. Tìm hiểu các quy tắc và hướng dẫn về nội dung được phép và bị cấm trên nền tảng.">
    <meta name="twitter:image" content="{{ url(asset('assets/images/logo/logo_site.webp')) }}">
    <meta name="twitter:image:alt" content="Logo {{ config('app.name') }}">
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header Section -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">Quy định về nội dung - Content Rules</h1>
            </div>

            <!-- Content Section -->
            <div class="content-rules-content">
                <div class="content-section">
                    <p class="content-text">
                        Sau đây là một phần của <strong>"Điều khoản sử dụng"</strong> có tại 
                        <a href="{{ route('privacy-policy') }}" class="contact-link">Chính sách bảo mật</a>
                    </p>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Nội dung bị cấm</h2>
                    <p class="content-text">
                        Chúng tôi <strong>cấm xuất bản</strong> trên <strong>{{ parse_url(config('app.url'), PHP_URL_HOST) }}</strong> 
                        những nội dung sau:
                    </p>
                    
                    <div class="rules-list">
                        <div class="rule-item">
                            <div class="rule-icon">
                                <i class="fas fa-ban text-danger"></i>
                            </div>
                            <div class="rule-content">
                                <h4 class="rule-title">Vi phạm quyền và xúc phạm</h4>
                                <p class="rule-description">
                                    Vi phạm quyền, quyền riêng tư của Người dùng khác hoặc bên thứ ba, xúc phạm, chứa đựng các mối đe dọa
                                </p>
                            </div>
                        </div>

                        <div class="rule-item">
                            <div class="rule-icon">
                                <i class="fas fa-ban text-danger"></i>
                            </div>
                            <div class="rule-content">
                                <h4 class="rule-title">Nội dung khiêu dâm</h4>
                                <p class="rule-description">
                                    Thô tục hoặc khiêu dâm, chứa hình ảnh và văn bản khiêu dâm, cảnh có tính chất tình dục liên quan đến trẻ vị thành niên, 
                                    loạn luân, thú tính và các hành vi tình dục bất hợp pháp khác
                                </p>
                            </div>
                        </div>

                        <div class="rule-item">
                            <div class="rule-icon">
                                <i class="fas fa-ban text-danger"></i>
                            </div>
                            <div class="rule-content">
                                <h4 class="rule-title">Bạo lực tình dục</h4>
                                <p class="rule-description">
                                    Tôn vinh bạo lực tình dục và quan hệ tình dục vô cớ
                                </p>
                            </div>
                        </div>

                        <div class="rule-item">
                            <div class="rule-icon">
                                <i class="fas fa-ban text-danger"></i>
                            </div>
                            <div class="rule-content">
                                <h4 class="rule-title">Tự sát và tự hại</h4>
                                <p class="rule-description">
                                    Chứa các mô tả về các phương tiện và phương pháp tự sát, bất kỳ sự xúi giục nào để thực hiện hành vi đó
                                </p>
                            </div>
                        </div>

                        <div class="rule-item">
                            <div class="rule-icon">
                                <i class="fas fa-ban text-danger"></i>
                            </div>
                            <div class="rule-content">
                                <h4 class="rule-title">Kích động thù hận</h4>
                                <p class="rule-description">
                                    Chứa thông tin kích động chủng tộc, tôn giáo, hận thù sắc tộc hoặc thù địch, bất hòa chính trị, 
                                    chứa các nhận xét tiêu cực về bất kỳ quốc gia, con người, tôn giáo, chính trị nào. Chứa các tài liệu cực đoan
                                </p>
                            </div>
                        </div>

                        <div class="rule-item">
                            <div class="rule-icon">
                                <i class="fas fa-ban text-danger"></i>
                            </div>
                            <div class="rule-content">
                                <h4 class="rule-title">Hoạt động tội phạm</h4>
                                <p class="rule-description">
                                    Thúc đẩy hoạt động tội phạm hoặc chứa các mẹo, hướng dẫn hoặc hướng dẫn
                                </p>
                            </div>
                        </div>

                        <div class="rule-item">
                            <div class="rule-icon">
                                <i class="fas fa-ban text-danger"></i>
                            </div>
                            <div class="rule-content">
                                <h4 class="rule-title">Quảng cáo không được phép</h4>
                                <p class="rule-description">
                                    Được thực hiện để quảng cáo bất kỳ hàng hóa hoặc dịch vụ nào (bao gồm cả tiền điện tử) 
                                    ngoại trừ sách của người dùng trên <strong>{{ parse_url(config('app.url'), PHP_URL_HOST) }}</strong> 
                                    hoặc các trang xã hội của người dùng
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Giới hạn độ tuổi 18+</h2>
                    <p class="content-text">
                        Truyện phải được chỉ định <strong>giới hạn độ tuổi "18+"</strong> nếu có:
                    </p>
                    
                    <div class="age-restriction-list">
                        <div class="restriction-item">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            <span>Mô tả chi tiết về bạo lực hoặc lạm dụng nghiêm trọng</span>
                        </div>
                        <div class="restriction-item">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            <span>Cảnh tự hại bản thân</span>
                        </div>
                        <div class="restriction-item">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            <span>Cảnh quan hệ tình dục rõ ràng</span>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Tài liệu đồ họa bị cấm</h2>
                    <p class="content-text">
                        Nghiêm cấm xuất bản các <strong>tài liệu đồ họa</strong> (bìa sách, hình minh họa trong các bài đăng trên blog, v.v.) mô tả:
                    </p>
                    
                    <div class="graphic-rules-list">
                        <div class="graphic-rule-item">
                            <i class="fas fa-times-circle text-danger me-2"></i>
                            <span>Người gần như hoặc hoàn toàn khỏa thân</span>
                        </div>
                        <div class="graphic-rule-item">
                            <i class="fas fa-times-circle text-danger me-2"></i>
                            <span>Người mặc quần áo trong suốt hoặc quá hở hang</span>
                        </div>
                        <div class="graphic-rule-item">
                            <i class="fas fa-times-circle text-danger me-2"></i>
                            <span>Những người ở tư thế khiêu dâm hoặc thách thức</span>
                        </div>
                        <div class="graphic-rule-item">
                            <i class="fas fa-times-circle text-danger me-2"></i>
                            <span>Cận cảnh cơ quan sinh dục, vú phụ nữ, mông hoặc đáy chậu</span>
                        </div>
                        <div class="graphic-rule-item">
                            <i class="fas fa-times-circle text-danger me-2"></i>
                            <span>Hình ảnh thô tục hoặc khiêu dâm khác</span>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Trạng thái "Mẫu"</h2>
                    <p class="content-text">
                        Nếu một tác giả không xuất bản toàn bộ tác phẩm của họ trên trang web hoặc không có kế hoạch làm việc đó, 
                        họ phải gán cho cuốn tiểu thuyết này trạng thái <strong>"Mẫu"</strong>.
                    </p>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Trách nhiệm người dùng</h2>
                    <p class="content-text">
                        Bất kỳ Người dùng nào cũng phải chịu trách nhiệm cá nhân đối với bất kỳ thông tin nào mà họ đặt trên 
                        <strong>{{ parse_url(config('app.url'), PHP_URL_HOST) }}</strong> những gì họ thông báo cho Người dùng khác. 
                        Bất kỳ tương tác nào với Người dùng khác đều có rủi ro riêng.
                    </p>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Hành động của Ban quản trị</h2>
                    <p class="content-text">
                        Trong trường hợp <strong>vi phạm các quy tắc</strong> hoặc rủi ro của nó, Ban quản trị trang web có quyền:
                    </p>
                    
                    <div class="admin-actions-list">
                        <div class="action-item">
                            <i class="fas fa-lock text-danger me-2"></i>
                            <span>Chặn quyền truy cập vào bất kỳ tác phẩm nào được đăng trên trang web</span>
                        </div>
                        <div class="action-item">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            <span>Buộc đánh dấu bất kỳ tác phẩm nào là "18+"</span>
                        </div>
                        <div class="action-item">
                            <i class="fas fa-trash text-danger me-2"></i>
                            <span>Xóa bất kỳ công việc nào</span>
                        </div>
                        <div class="action-item">
                            <i class="fas fa-user-lock text-danger me-2"></i>
                            <span>Khóa người dùng</span>
                        </div>
                        <div class="action-item">
                            <i class="fas fa-book-dead text-danger me-2"></i>
                            <span>Xóa các truyện đăng trên website</span>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Liên hệ</h2>
                    <p class="content-text">
                        Mọi thắc mắc vui lòng liên hệ email: 
                        <a href="mailto:pinknovel25@gmail.com" class="contact-link">pinknovel25@gmail.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .content-rules-content {
        background: white;
        border-radius: 15px;
        padding: 3rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    .content-section {
        margin-bottom: 2.5rem;
    }

    .section-title {
        color: #14425d;
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 3px solid #14425d;
    }

    .content-text {
        color: #4a5568;
        line-height: 1.8;
        margin-bottom: 1.2rem;
        font-size: 1rem;
    }

    .rules-list {
        margin: 1.5rem 0;
    }

    .rule-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        padding: 1.5rem;
        background: #fff5f5;
        border-radius: 10px;
        border-left: 4px solid #e53e3e;
    }

    .rule-icon {
        margin-right: 1rem;
        font-size: 1.5rem;
        margin-top: 0.2rem;
    }

    .rule-content {
        flex: 1;
    }

    .rule-title {
        color: #e53e3e;
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .rule-description {
        color: #4a5568;
        line-height: 1.6;
        margin: 0;
    }

    .age-restriction-list {
        margin: 1.5rem 0;
    }

    .restriction-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        margin-bottom: 0.5rem;
        background: #fffaf0;
        border-radius: 8px;
        border-left: 4px solid #f6ad55;
        color: #4a5568;
        font-weight: 500;
    }

    .graphic-rules-list {
        margin: 1.5rem 0;
    }

    .graphic-rule-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        margin-bottom: 0.5rem;
        background: #fff5f5;
        border-radius: 8px;
        border-left: 4px solid #e53e3e;
        color: #4a5568;
        font-weight: 500;
    }

    .admin-actions-list {
        margin: 1.5rem 0;
    }

    .action-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        margin-bottom: 0.5rem;
        background: #f7fafc;
        border-radius: 8px;
        border-left: 4px solid #14425d;
        color: #4a5568;
        font-weight: 500;
    }

    .contact-link {
        color: #14425d;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .contact-link:hover {
        color: #0f2d3f;
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .content-rules-content {
            padding: 2rem 1.5rem;
        }
        
        .section-title {
            font-size: 1.5rem;
        }
        
        .rule-item {
            flex-direction: column;
            text-align: center;
        }
        
        .rule-icon {
            margin-right: 0;
            margin-bottom: 1rem;
        }
    }
</style>
@endpush
