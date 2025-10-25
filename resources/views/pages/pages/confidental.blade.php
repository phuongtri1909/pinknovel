@extends('layouts.app')

@section('title', 'Thỏa thuận quyền riêng tư - Privacy Agreement - ' . config('app.name'))

@section('description', 'Thỏa thuận quyền riêng tư của ' . config('app.name') . '. Tìm hiểu cách chúng tôi xử lý và bảo vệ dữ liệu cá nhân của bạn.')

@section('keyword', 'thỏa thuận quyền riêng tư, privacy agreement, bảo vệ dữ liệu, xử lý thông tin, quyền riêng tư, ' . config('app.name'))

@section('meta')
    <meta property="og:type" content="website">
    <meta property="og:title" content="Thỏa thuận quyền riêng tư - Privacy Agreement - {{ config('app.name') }}">
    <meta property="og:description" content="Thỏa thuận quyền riêng tư của {{ config('app.name') }}. Tìm hiểu cách chúng tôi xử lý và bảo vệ dữ liệu cá nhân của bạn.">
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
    <meta name="twitter:title" content="Thỏa thuận quyền riêng tư - Privacy Agreement - {{ config('app.name') }}">
    <meta name="twitter:description" content="Thỏa thuận quyền riêng tư của {{ config('app.name') }}. Tìm hiểu cách chúng tôi xử lý và bảo vệ dữ liệu cá nhân của bạn.">
    <meta name="twitter:image" content="{{ url(asset('assets/images/logo/logo_site.webp')) }}">
    <meta name="twitter:image:alt" content="Logo {{ config('app.name') }}">
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header Section -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">Thỏa thuận quyền riêng tư - Privacy Agreement</h1>
            </div>

            <!-- Content Section -->
            <div class="confidental-content">
                <div class="content-section">
                    <p class="content-text">
                        Chính sách bảo mật này là một phần của Thỏa thuận. Tất cả các điều khoản được sử dụng trong Thỏa thuận đều có thể áp dụng cho chính sách này. 
                        Bằng cách sử dụng Trang web, bạn đồng ý với việc thu thập và sử dụng dữ liệu cá nhân của mình theo Chính sách Bảo mật này.
                    </p>
                    <p class="content-text">
                        Chúng tôi hoàn toàn nhận thức được tầm quan trọng của dữ liệu cá nhân của bạn. Chính sách Bảo mật này mô tả dữ liệu cá nhân nào chúng tôi nhận được và thu thập khi bạn sử dụng Trang web.
                    </p>
                    <p class="content-text">
                        Ngoài ra, chúng tôi có thể yêu cầu sự đồng ý của bạn để sử dụng dữ liệu cá nhân của bạn cho các mục đích mà Chính sách quyền riêng tư này không được áp dụng. 
                        Bạn không nhất thiết phải đồng ý, nhưng nếu bạn quyết định không đồng ý, sự tham gia của bạn vào một số hoạt động nhất định có thể bị hạn chế. 
                        Nếu bạn đồng ý với các điều khoản bổ sung, chúng sẽ được ưu tiên áp dụng nếu chúng khác với các điều khoản của Chính sách bảo mật này.
                    </p>
                    <p class="content-text">
                        Chúng tôi thực hiện tất cả các biện pháp phòng ngừa hợp lý để bảo vệ dữ liệu cá nhân của bạn và yêu cầu tương tự từ các bên thứ ba, 
                        những người có thể xử lý thông tin cá nhân của bạn cho chúng tôi. Quyền truy cập vào dữ liệu cá nhân của bạn bị hạn chế để ngăn chặn truy cập trái phép, 
                        thay đổi hoặc sử dụng sai mục đích và chỉ được phép đối với nhân viên và nhà thầu của chúng tôi.
                    </p>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Mục đích xử lý dữ liệu cá nhân</h2>
                    <p class="content-text">
                        Chúng tôi chỉ thu thập và lưu trữ những dữ liệu cá nhân cần thiết để bạn tương tác thoải mái với chúng tôi, cụ thể là cho các mục đích:
                    </p>
                    
                    <div class="purposes-list">
                        <div class="purpose-item">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Cung cấp dịch vụ cho Người dùng và Tác giả trong khuôn khổ chức năng của Trang web</span>
                        </div>
                        <div class="purpose-item">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Nhận dạng và tương tác</span>
                        </div>
                        <div class="purpose-item">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Gửi thông báo hoặc các tài liệu và yêu cầu thông tin khác</span>
                        </div>
                        <div class="purpose-item">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Thực hiện nghiên cứu thống kê</span>
                        </div>
                        <div class="purpose-item">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Xử lý thanh toán</span>
                        </div>
                        <div class="purpose-item">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Giám sát hoạt động</span>
                        </div>
                        <div class="purpose-item">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Phòng chống gian lận</span>
                        </div>
                        <div class="purpose-item">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Cá nhân hóa công việc của bạn với Trang web</span>
                        </div>
                        <div class="purpose-item">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Tăng tốc công việc với Trang web</span>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Danh sách thông tin chúng tôi thu thập và xử lý</h2>
                    <p class="content-text">
                        Thông thường, dữ liệu cá nhân bao gồm thông tin cho phép nhận dạng bạn. Nó bao gồm tên, địa chỉ, bí danh (biệt hiệu), ảnh, 
                        địa chỉ email và số điện thoại, nhưng nó cũng có thể bao gồm các thông tin khác như địa chỉ IP, sở thích của người đọc, sở thích và sở thích.
                    </p>
                    
                    <div class="data-sources">
                        <div class="data-source-item">
                            <h3 class="data-source-title">
                                <i class="fas fa-user-edit text-primary me-2"></i>
                                Dữ liệu mà bạn tự cung cấp cho chúng tôi
                            </h3>
                            <p class="data-source-description">Nó có thể bao gồm:</p>
                            <div class="data-list">
                                <div class="data-item">
                                    <i class="fas fa-envelope text-info me-2"></i>
                                    <span>Địa chỉ email</span>
                                </div>
                                <div class="data-item">
                                    <i class="fas fa-user text-info me-2"></i>
                                    <span>Tên tài khoản</span>
                                </div>
                                <div class="data-item">
                                    <i class="fas fa-phone text-info me-2"></i>
                                    <span>Số điện thoại</span>
                                </div>
                                <div class="data-item">
                                    <i class="fas fa-birthday-cake text-info me-2"></i>
                                    <span>Tuổi tác</span>
                                </div>
                                <div class="data-item">
                                    <i class="fas fa-calendar text-info me-2"></i>
                                    <span>Ngày sinh</span>
                                </div>
                                <div class="data-item">
                                    <i class="fas fa-venus-mars text-info me-2"></i>
                                    <span>Giới tính</span>
                                </div>
                                <div class="data-item">
                                    <i class="fas fa-info-circle text-info me-2"></i>
                                    <span>Bất kỳ dữ liệu cá nhân nào khác mà bạn tự nguyện cung cấp cho chúng tôi</span>
                                </div>
                            </div>
                        </div>

                        <div class="data-source-item">
                            <h3 class="data-source-title">
                                <i class="fas fa-robot text-primary me-2"></i>
                                Dữ liệu tự động đến khi sử dụng Trang web
                            </h3>
                            <p class="data-source-description">Nó có thể bao gồm:</p>
                            <div class="data-list">
                                <div class="data-item">
                                    <i class="fas fa-globe text-info me-2"></i>
                                    <span>Dữ liệu về loại trình duyệt của bạn</span>
                                </div>
                                <div class="data-item">
                                    <i class="fas fa-chart-bar text-info me-2"></i>
                                    <span>Thống kê các trang bạn đã xem</span>
                                </div>
                                <div class="data-item">
                                    <i class="fas fa-network-wired text-info me-2"></i>
                                    <span>Địa chỉ IP của bạn</span>
                                </div>
                                <div class="data-item">
                                    <i class="fas fa-cookie-bite text-info me-2"></i>
                                    <span>Bánh quy (Cookie)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Cookie và công nghệ theo dõi</h2>
                    <p class="content-text">
                        Khi bạn truy cập Trang web, một hoặc nhiều cookie sẽ được gửi đến máy tính của bạn. Đây là một tệp nhỏ chứa các bộ ký hiệu và cho phép bạn xác định trình duyệt.
                    </p>
                    <p class="content-text">
                        Khi bạn đăng ký Trang web, các cookie bổ sung có thể được gửi đến máy tính của bạn để tránh nhập lại tên người dùng và mật khẩu vào lần sau khi bạn truy cập. 
                        Bạn có thể xóa chúng vào cuối phiên nếu bạn đang sử dụng máy tính công cộng và không muốn mở dữ liệu của mình cho những người dùng máy tính tiếp theo.
                    </p>
                    <p class="content-text">
                        Chúng tôi sử dụng cookie để cải thiện chất lượng dịch vụ của mình bằng cách lưu cài đặt người dùng và theo dõi xu hướng hành động. 
                        Hầu hết các trình duyệt ban đầu được định cấu hình để chấp nhận cookie, nhưng bạn hoàn toàn có thể không cho phép sử dụng cookie hoặc định cấu hình thông báo gửi của chúng. 
                        Hãy chú ý đến thực tế là nếu không có cookie, một số chức năng của trang web có thể hoạt động không chính xác.
                    </p>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Quyền của bạn về dữ liệu cá nhân</h2>
                    <p class="content-text">Bạn có quyền:</p>
                    
                    <div class="rights-list">
                        <div class="right-item">
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            <span>Hạn chế hoặc từ chối nhận các bản tin hoặc tin nhắn từ chúng tôi trong tương lai</span>
                        </div>
                        <div class="right-item">
                            <i class="fas fa-edit text-success me-2"></i>
                            <span>Sửa, cập nhật hoặc xóa dữ liệu cá nhân của bạn khỏi bộ nhớ của chúng tôi</span>
                        </div>
                        <div class="right-item">
                            <i class="fas fa-exclamation-triangle text-success me-2"></i>
                            <span>Báo cáo việc sử dụng trái phép dữ liệu cá nhân của bạn</span>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Đồng ý với việc xử lý dữ liệu cá nhân</h2>
                    <p class="content-text">
                        Kể từ thời điểm bạn chấp nhận Đề nghị công khai, bạn hoàn toàn đồng ý với việc xử lý dữ liệu cá nhân của mình, cụ thể là: 
                        thu thập, hệ thống hóa, tích lũy, lưu trữ, làm rõ, sử dụng, phân phối, phi cá nhân hóa, chặn, tiêu hủy dữ liệu cá nhân do bạn cung cấp 
                        trong quy trình sử dụng Trang web, bao gồm, nhưng không giới hạn, họ, tên, tên viết tắt, giới tính, tuổi, số điện thoại liên lạc hoặc địa chỉ e-mail 
                        hoặc các phương tiện liên lạc điện tử khác, trong trường hợp có được trạng thái thuê bao trả phí - thông tin về thẻ thanh toán tín dụng hoặc thẻ ghi nợ 
                        của bạn và các phương tiện thanh toán khác.
                    </p>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Các thay đổi đối với Chính sách quyền riêng tư</h2>
                    <p class="content-text">
                        Xin lưu ý rằng chính sách bảo mật có thể thay đổi theo thời gian. Tất cả các thay đổi đối với chính sách bảo mật được đăng trên trang này tại 
                        <a href="{{ route('privacy-policy') }}" class="contact-link">Chính sách bảo mật</a>
                    </p>
                </div>

                <div class="content-section warning-section">
                    <div class="warning-box">
                        <h2 class="warning-title">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            Lưu ý quan trọng
                        </h2>
                        <p class="warning-text">
                            <strong>NẾU BẠN KHÔNG ĐỒNG Ý VỚI VIỆC THU THẬP, SỬ DỤNG VÀ CÔNG BỐ DỮ LIỆU CÁ NHÂN CỦA BẠN ĐƯỢC MÔ TẢ CHI TIẾT TRÊN, 
                            VUI LÒNG KHÔNG SỬ DỤNG DỊCH VỤ CỦA TRANG WEB.</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .confidental-content {
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

    .purposes-list {
        margin: 1.5rem 0;
    }

    .purpose-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        margin-bottom: 0.5rem;
        background: #f0fff4;
        border-radius: 8px;
        border-left: 4px solid #38a169;
        color: #4a5568;
        font-weight: 500;
    }

    .data-sources {
        margin: 1.5rem 0;
    }

    .data-source-item {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #14425d;
    }

    .data-source-title {
        color: #14425d;
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
    }

    .data-source-description {
        color: #4a5568;
        margin-bottom: 1rem;
        font-weight: 500;
    }

    .data-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 0.5rem;
    }

    .data-item {
        display: flex;
        align-items: center;
        padding: 0.8rem;
        background: white;
        border-radius: 6px;
        color: #4a5568;
        font-weight: 500;
        border: 1px solid #e2e8f0;
    }

    .rights-list {
        margin: 1.5rem 0;
    }

    .right-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        margin-bottom: 0.5rem;
        background: #f0fff4;
        border-radius: 8px;
        border-left: 4px solid #38a169;
        color: #4a5568;
        font-weight: 500;
    }

    .warning-section {
        margin-top: 3rem;
    }

    .warning-box {
        background: #fffaf0;
        border: 2px solid #f6ad55;
        border-radius: 10px;
        padding: 2rem;
        text-align: center;
    }

    .warning-title {
        color: #c05621;
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .warning-text {
        color: #744210;
        font-size: 1.1rem;
        line-height: 1.6;
        margin: 0;
        font-weight: 600;
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
        .confidental-content {
            padding: 2rem 1.5rem;
        }
        
        .section-title {
            font-size: 1.5rem;
        }
        
        .data-list {
            grid-template-columns: 1fr;
        }
        
        .warning-title {
            font-size: 1.3rem;
        }
        
        .warning-text {
            font-size: 1rem;
        }
    }
</style>
@endpush
