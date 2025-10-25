@extends('layouts.app')

@section('title', 'Chính sách bảo mật - Privacy Policy - ' . config('app.name'))

@section('description', 'Chính sách bảo mật của ' . config('app.name') . '. Tìm hiểu cách chúng tôi thu thập, sử dụng và bảo vệ thông tin cá nhân của bạn khi sử dụng dịch vụ.')

@section('keyword', 'chính sách bảo mật, privacy policy, bảo vệ dữ liệu, thông tin cá nhân, quyền riêng tư, ' . config('app.name'))

@section('meta')
    <meta property="og:type" content="website">
    <meta property="og:title" content="Chính sách bảo mật - Privacy Policy - {{ config('app.name') }}">
    <meta property="og:description" content="Chính sách bảo mật của {{ config('app.name') }}. Tìm hiểu cách chúng tôi thu thập, sử dụng và bảo vệ thông tin cá nhân của bạn khi sử dụng dịch vụ.">
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
    <meta name="twitter:title" content="Chính sách bảo mật - Privacy Policy - {{ config('app.name') }}">
    <meta name="twitter:description" content="Chính sách bảo mật của {{ config('app.name') }}. Tìm hiểu cách chúng tôi thu thập, sử dụng và bảo vệ thông tin cá nhân của bạn khi sử dụng dịch vụ.">
    <meta name="twitter:image" content="{{ url(asset('assets/images/logo/logo_site.webp')) }}">
    <meta name="twitter:image:alt" content="Logo {{ config('app.name') }}">
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header Section -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">Chính sách bảo mật - Privacy Policy</h1>
            </div>

            <!-- Content Section -->
            <div class="privacy-content">
                <div class="content-section">
                    <h2 class="section-title">Giới thiệu</h2>
                    <p class="content-text">
                        Chúng tôi hiểu rằng bạn nhận thức và quan tâm đến lợi ích riêng tư cá nhân của bạn và chúng tôi coi trọng điều đó. 
                        Thông báo về Quyền riêng tư này nhằm giúp bạn hiểu dữ liệu nào chúng tôi thu thập, lý do chúng tôi thu thập, 
                        chúng tôi làm gì với dữ liệu đó và cũng quy định các quyền riêng tư của bạn. Chúng tôi nhận ra rằng bảo mật thông tin 
                        là trách nhiệm liên tục và vì vậy, chúng tôi sẽ cập nhật Thông báo về quyền riêng tư này theo thời gian khi chúng tôi 
                        thực hiện các thực hành dữ liệu cá nhân mới hoặc áp dụng các chính sách bảo mật mới.
                    </p>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Cách chúng tôi thu thập và sử dụng thông tin cá nhân của bạn</h2>
                    
                    <h3 class="subsection-title">Tóm lược:</h3>
                    <p class="content-text">
                        {{ parse_url(config('app.url'), PHP_URL_HOST) }} thu thập thông tin cá nhân về những người dùng đã đăng ký và những khách hàng khác. 
                        Chúng tôi sử dụng thông tin này để nâng cao trải nghiệm của bạn trên trang web của chúng tôi và cung cấp dịch vụ cho bạn. 
                        Các loại thông tin chúng tôi thu thập có thể khác nhau, tùy thuộc vào cách bạn chọn sử dụng trang web của chúng tôi. 
                        Chúng tôi không bán thông tin cá nhân của bạn cho bất kỳ ai.
                    </p>

                    <h3 class="subsection-title">Mở rộng:</h3>
                    <p class="content-text">Thông tin cá nhân bạn cung cấp cho chúng tôi:</p>

                    <div class="info-list">
                        <div class="info-item">
                            <h4 class="info-title">1. Đăng ký</h4>
                            <p class="content-text">
                                Khi bạn đăng ký và tạo tài khoản trên trang web của chúng tôi, chúng tôi sẽ thu thập thông tin về bạn, 
                                bao gồm tên, ngày sinh, giới tính, địa chỉ email và số điện thoại. Nếu bạn chọn đăng ký bằng hồ sơ mạng xã hội 
                                hiện có của mình, chúng tôi sẽ thu thập thông tin từ hồ sơ của bạn, bao gồm (nhưng không giới hạn) biệt hiệu, 
                                ảnh đại diện (ảnh), giới tính, ngày sinh, ID hồ sơ hoặc URL. Người dùng có thể chỉnh sửa hồ sơ của mình bất kỳ 
                                lúc nào để thay đổi, thêm hoặc xóa một số thông tin cá nhân nhất định, ngoại trừ ngày sinh và giới tính.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">2. Thư từ của bạn với {{ config('app.name') }}</h4>
                            <p class="content-text">
                                Nếu bạn trao đổi với chúng tôi qua email, chúng tôi sẽ lưu trữ nội dung email của bạn, địa chỉ email và 
                                phản hồi của chúng tôi. Chúng tôi cũng có thể lưu trữ thông tin liên lạc của bạn nếu bạn liên hệ với chúng tôi 
                                để được hỗ trợ.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">3. Thông tin từ các nguồn khác</h4>
                            <p class="content-text">
                                Chúng tôi có thể nhận thông tin về bạn từ các nguồn khác, chẳng hạn như các đối tác, nhà cung cấp dịch vụ 
                                và các bên thứ ba khác và kết hợp thông tin đó với thông tin chúng tôi thu thập về bạn.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Cách chúng tôi sử dụng thông tin của bạn</h2>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <h4 class="info-title">1. Khi chúng tôi có lợi ích hợp pháp</h4>
                            <p class="content-text">
                                Chúng tôi xử lý dữ liệu của bạn khi chúng tôi có lợi ích hợp pháp để làm như vậy. Ví dụ: chúng tôi xử lý dữ liệu 
                                của bạn để:
                            </p>
                            <ul class="content-list">
                                <li>Thực thi các khiếu nại pháp lý, bao gồm cả việc điều tra các vi phạm tiềm ẩn đối với Điều khoản dịch vụ hiện hành</li>
                                <li>Bảo vệ quyền lợi, tài sản hoặc an toàn của chúng tôi, người dùng của chúng tôi hoặc công chúng</li>
                                <li>Phát hiện, ngăn chặn hoặc giải quyết các vấn đề bảo mật hoặc gian lận</li>
                            </ul>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">2. Khi chúng tôi cung cấp dịch vụ</h4>
                            <p class="content-text">
                                Chúng tôi xử lý dữ liệu của bạn để cung cấp dịch vụ mà bạn đã yêu cầu theo hợp đồng. Ví dụ: chúng tôi thu thập 
                                bản sao giấy tờ tùy thân do chính phủ cấp của bạn và xử lý hệ thống thanh toán hoặc thông tin ngân hàng của bạn 
                                khi bạn muốn xuất bản nội dung trả phí hoặc cố gắng rút tiền từ số dư ảo của bạn trên trang web của chúng tôi.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">3. Khi chúng tôi tuân thủ các nghĩa vụ pháp lý</h4>
                            <p class="content-text">
                                Chúng tôi sẽ xử lý dữ liệu của bạn khi chúng tôi có nghĩa vụ pháp lý phải làm như vậy, chẳng hạn như nếu chúng tôi 
                                đang phản hồi quy trình pháp lý hoặc yêu cầu có thể thực thi của chính phủ.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Bảo mật thông tin của bạn</h2>
                    <p class="content-text">
                        Để giúp bảo vệ quyền riêng tư của dữ liệu và thông tin nhận dạng cá nhân mà bạn truyền tải thông qua việc sử dụng 
                        trang web của chúng tôi, chúng tôi duy trì các biện pháp bảo vệ vật lý, kỹ thuật và hành chính. Chúng tôi liên tục 
                        cập nhật và kiểm tra công nghệ bảo mật của mình. Chúng tôi hạn chế quyền truy cập vào dữ liệu cá nhân của bạn đối với 
                        những nhân viên cần biết thông tin đó để cung cấp lợi ích hoặc dịch vụ cho bạn. Ngoài ra, chúng tôi đào tạo nhân viên 
                        của mình về tầm quan trọng của tính bảo mật và duy trì sự riêng tư và bảo mật thông tin của bạn. Chúng tôi cam kết thực hiện 
                        các biện pháp kỷ luật thích hợp để thực thi trách nhiệm về quyền riêng tư của nhân viên.
                    </p>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Lưu trữ và lưu giữ dữ liệu</h2>
                    <p class="content-text">
                        Dữ liệu cá nhân của bạn được {{ config('app.name') }} lưu trữ trên (các) máy chủ chuyên dụng. 
                        {{ config('app.name') }} giữ lại dữ liệu trong suốt thời gian khách hàng quan hệ với {{ config('app.name') }}, 
                        cộng thêm sáu tháng. Để biết thêm thông tin về nơi và thời gian lưu trữ dữ liệu cá nhân của bạn cũng như để biết thêm 
                        thông tin về quyền xóa và tính di động của bạn, vui lòng liên hệ với chúng tôi tại 
                        <a href="mailto:pinknovel25@gmail.com" class="contact-link">pinknovel25@gmail.com</a>
                    </p>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Các thay đổi và cập nhật đối với Thông báo về quyền riêng tư</h2>
                    <p class="content-text">
                        Bằng cách sử dụng {{ parse_url(config('app.url'), PHP_URL_HOST) }}, bạn đồng ý với các điều khoản và điều kiện có trong Thông báo về Quyền riêng tư 
                        này và / hoặc bất kỳ thỏa thuận nào khác mà chúng tôi có thể có với bạn. Nếu bạn không đồng ý với bất kỳ điều khoản và 
                        điều kiện nào, bạn không nên sử dụng trang web này hoặc bất kỳ dịch vụ {{ config('app.name') }} nào.
                    </p>
                    <p class="content-text">
                        Khi tổ chức, tư cách thành viên và lợi ích của chúng tôi thay đổi theo thời gian, Thông báo về Quyền riêng tư này cũng 
                        sẽ thay đổi. Chúng tôi có quyền sửa đổi Thông báo về Quyền riêng tư vào bất kỳ lúc nào, vì bất kỳ lý do gì, mà không cần 
                        thông báo cho bạn, ngoài việc đăng Thông báo Bảo mật đã sửa đổi trên trang web này. Chúng tôi có thể gửi email nhắc nhở 
                        định kỳ về các thông báo và điều khoản và điều kiện của chúng tôi và sẽ gửi email cho người dùng về những thay đổi quan 
                        trọng trong đó, nhưng bạn nên kiểm tra trang web của chúng tôi thường xuyên để xem Thông báo về quyền riêng tư hiện có 
                        hiệu lực và bất kỳ thay đổi nào có thể đã được thực hiện đối với thông báo đó. Các điều khoản trong tài liệu này thay thế 
                        tất cả các thông báo hoặc tuyên bố trước đây liên quan đến thực tiễn bảo mật của chúng tôi và các điều khoản và điều kiện 
                        chi phối việc sử dụng Trang web này.
                    </p>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Câu hỏi, thắc mắc hoặc khiếu nại</h2>
                    <p class="content-text">
                        Email: <a href="mailto:pinknovel25@gmail.com" class="contact-link">pinknovel25@gmail.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .privacy-content {
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

    .subsection-title {
        color: #2d3748;
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 1rem;
        margin-top: 1.5rem;
    }

    .content-text {
        color: #4a5568;
        line-height: 1.8;
        margin-bottom: 1.2rem;
        font-size: 1rem;
    }

    .info-list {
        margin: 1.5rem 0;
    }

    .info-item {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #14425d;
    }

    .info-title {
        color: #14425d;
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .content-list {
        margin: 1rem 0;
        padding-left: 1.5rem;
    }

    .content-list li {
        color: #4a5568;
        line-height: 1.6;
        margin-bottom: 0.5rem;
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
        .privacy-content {
            padding: 2rem 1.5rem;
        }
        
        .section-title {
            font-size: 1.5rem;
        }
        
        .subsection-title {
            font-size: 1.2rem;
        }
        
        .info-item {
            padding: 1rem;
        }
    }
</style>
@endpush
