@extends('layouts.app')

@section('title', 'Điều khoản sử dụng - Terms of Use - ' . config('app.name'))

@section('description', 'Điều khoản sử dụng của ' . config('app.name') . '. Tìm hiểu các quyền và nghĩa vụ của người dùng khi sử dụng dịch vụ truyện online.')

@section('keyword', 'điều khoản sử dụng, terms of use, quyền người dùng, nghĩa vụ, bản quyền, ' . config('app.name'))

@section('meta')
    <meta property="og:type" content="website">
    <meta property="og:title" content="Điều khoản sử dụng - Terms of Use - {{ config('app.name') }}">
    <meta property="og:description" content="Điều khoản sử dụng của {{ config('app.name') }}. Tìm hiểu các quyền và nghĩa vụ của người dùng khi sử dụng dịch vụ truyện online.">
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
    <meta name="twitter:title" content="Điều khoản sử dụng - Terms of Use - {{ config('app.name') }}">
    <meta name="twitter:description" content="Điều khoản sử dụng của {{ config('app.name') }}. Tìm hiểu các quyền và nghĩa vụ của người dùng khi sử dụng dịch vụ truyện online.">
    <meta name="twitter:image" content="{{ url(asset('assets/images/logo/logo_site.webp')) }}">
    <meta name="twitter:image:alt" content="Logo {{ config('app.name') }}">
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header Section -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">Terms of use - Điều khoản sử dụng</h1>
            </div>

            <!-- Content Section -->
            <div class="terms-content">
                <div class="content-section">
                    <h2 class="section-title">Thỏa thuận Giấy phép Người dùng Cuối</h2>
                    <p class="content-text">
                        Tất cả các quyền đối với dịch vụ trên {{ parse_url(config('app.url'), PHP_URL_HOST) }} (sau đây gọi là "Trang web").
                    </p>
                    <p class="content-text">
                        Sử dụng và / hoặc truy cập Trang web (bao gồm tất cả nội dung có sẵn thông qua tên miền 
                        <strong>{{ parse_url(config('app.url'), PHP_URL_HOST) }}</strong> và tất cả các miền phụ, cũng như nội dung của Trang web, 
                        có thể truy cập thông qua các ứng dụng di động, bạn thể hiện sự chấp nhận đầy đủ và vô điều kiện của mình đối với các điều khoản 
                        của Thỏa thuận cấp phép người dùng cuối này (sau đây gọi là - Thỏa thuận) và thông báo Bảo mật. 
                        Nếu bạn không đồng ý với bất kỳ điều khoản nào của các tài liệu này, vui lòng không sử dụng Trang web.
                    </p>
                    <p class="content-text">
                        Chúng tôi sẽ cố gắng thông báo cho bạn nếu có bất kỳ thay đổi nào đối với Thỏa thuận, nhưng trong mọi trường hợp, 
                        bạn nên định kỳ xem xét và kiểm tra phiên bản mới nhất của Thỏa thuận. Chúng tôi có thể, theo quyết định riêng của mình, 
                        sửa đổi hoặc điều chỉnh các điều khoản của Thỏa thuận này bất cứ lúc nào và bằng cách tiếp tục sử dụng Trang web, 
                        bạn chấp nhận điều kiện đó. Các điều khoản của Thỏa thuận này hoàn toàn có thể áp dụng cho tất cả Người dùng của Trang web.
                    </p>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Các định nghĩa:</h2>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <h4 class="info-title">Trang web</h4>
                            <p class="content-text">
                                Là một tập hợp các yếu tố hình ảnh, tài liệu văn bản, âm thanh và video, được sắp xếp thành các phần và trang 
                                nằm trên miền <strong>{{ parse_url(config('app.url'), PHP_URL_HOST) }}</strong> và tất cả các miền phụ từ cấp thứ ba trở xuống.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">Người dùng Trang web</h4>
                            <p class="content-text">
                                Là một cá nhân đã đăng ký trên Trang web theo các điều khoản của Thỏa thuận, đã đủ tuổi cho phép theo luật của 
                                Liên minh Châu Âu và Quốc Tế để chấp nhận các điều khoản của Thỏa thuận này.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">Nội dung bản quyền</h4>
                            <p class="content-text">
                                Là một đối tượng của bản quyền dưới dạng văn bản hoặc hình ảnh, do Người sử dụng tạo ra và đăng tải lên Trang web.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">I. Điều kiện sử dụng</h2>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <h4 class="info-title">1. Chấp nhận điều khoản</h4>
                            <p class="content-text">
                                Bằng cách truy cập và sử dụng Trang web, bạn đồng ý tuân thủ tất cả các điều khoản và điều kiện được nêu trong 
                                Thỏa thuận này. Nếu bạn không đồng ý với bất kỳ điều khoản nào, vui lòng không sử dụng Trang web.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">2. Tuổi sử dụng</h4>
                            <p class="content-text">
                                Bạn phải đủ 18 tuổi hoặc đủ tuổi theo luật pháp địa phương để sử dụng Trang web. Nếu bạn dưới 18 tuổi, 
                                bạn cần có sự đồng ý của cha mẹ hoặc người giám hộ.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">3. Tài khoản người dùng</h4>
                            <p class="content-text">
                                Bạn có trách nhiệm duy trì tính bảo mật của tài khoản và mật khẩu của mình. Bạn đồng ý chịu trách nhiệm 
                                cho tất cả các hoạt động xảy ra dưới tài khoản của bạn.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">II. Quyền và nghĩa vụ của người dùng</h2>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <h4 class="info-title">1. Quyền của người dùng</h4>
                            <ul class="content-list">
                                <li>Truy cập và sử dụng các dịch vụ miễn phí của Trang web</li>
                                <li>Tạo và quản lý nội dung cá nhân</li>
                                <li>Tham gia cộng đồng và tương tác với người dùng khác</li>
                                <li>Báo cáo nội dung vi phạm</li>
                            </ul>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">2. Nghĩa vụ của người dùng</h4>
                            <ul class="content-list">
                                <li>Tuân thủ tất cả các điều khoản và điều kiện</li>
                                <li>Không đăng tải nội dung vi phạm bản quyền</li>
                                <li>Không sử dụng Trang web cho mục đích bất hợp pháp</li>
                                <li>Tôn trọng quyền riêng tư của người dùng khác</li>
                                <li>Không tạo nhiều tài khoản hoặc sử dụng tài khoản giả mạo</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">III. Nội dung và bản quyền</h2>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <h4 class="info-title">1. Quyền sở hữu trí tuệ</h4>
                            <p class="content-text">
                                Tất cả nội dung trên Trang web, bao gồm văn bản, hình ảnh, âm thanh, video và phần mềm, đều được bảo vệ 
                                bởi luật bản quyền và các luật sở hữu trí tuệ khác.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">2. Nội dung người dùng</h4>
                            <p class="content-text">
                                Khi bạn đăng tải nội dung lên Trang web, bạn giữ quyền sở hữu đối với nội dung đó nhưng cấp cho chúng tôi 
                                giấy phép không độc quyền để sử dụng, hiển thị và phân phối nội dung đó trên Trang web.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">3. Vi phạm bản quyền</h4>
                            <p class="content-text">
                                Chúng tôi tôn trọng quyền sở hữu trí tuệ của người khác và yêu cầu người dùng cũng làm như vậy. 
                                Nếu bạn tin rằng nội dung của bạn đã bị sao chép vi phạm bản quyền, vui lòng liên hệ với chúng tôi.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">IV. Quyền của Trang web</h2>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <h4 class="info-title">1. Quyền quản lý nội dung</h4>
                            <p class="content-text">
                                Trang web có quyền lưu trữ tất cả thông tin mà các tác giả xuất bản (bao gồm văn bản, hình ảnh, 
                                liên kết và các nguồn thông tin khác) và cung cấp cho người sử dụng Trang web miễn phí và / hoặc trả phí.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">2. Quyền xóa nội dung</h4>
                            <p class="content-text">
                                Website có quyền xóa các thông tin đã đăng trên tín hiệu của cơ quan giám sát, tư pháp cũng như trong các 
                                trường hợp có nghi vấn nghiêm trọng về việc vi phạm bản quyền đối với nội dung do Người sử dụng dịch vụ đăng tải.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">3. Quyền thay đổi dịch vụ</h4>
                            <p class="content-text">
                                Trang web có quyền thay đổi, cập nhật hoặc ngừng cung cấp bất kỳ dịch vụ nào mà không cần thông báo trước.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">V. Bảo vệ thông tin cá nhân</h2>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <h4 class="info-title">1. Bảo mật dữ liệu</h4>
                            <p class="content-text">
                                Website là cơ quan quản lý dữ liệu cá nhân theo Luật bảo vệ dữ liệu cá nhân. Trang web thực hiện các biện pháp 
                                cần thiết và chịu trách nhiệm bảo vệ thông tin của Người sử dụng đã biết đến mình do việc đăng ký của Người dùng 
                                trên trang web của Trang web, trừ các trường hợp bất khả kháng, ngẫu nhiên hoặc hành vi ác ý của bên thứ ba.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">2. Thu thập thông tin</h4>
                            <p class="content-text">
                                Website có quyền thu thập và sử dụng các thông tin khác của Người sử dụng. Thông tin có thể chứa địa chỉ e-mail, 
                                địa chỉ IP mà từ đó việc truy cập được thực hiện, v.v. Người dùng cung cấp thông tin một cách tự nguyện khi sử dụng tài nguyên.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">3. Đồng ý xử lý dữ liệu</h4>
                            <p class="content-text">
                                Bằng cách chấp nhận Thỏa thuận này, Người dùng cấp cho {{ parse_url(config('app.url'), PHP_URL_HOST) }} sự đồng ý của mình 
                                đối với việc xử lý dữ liệu cá nhân do Người dùng cung cấp khi sử dụng các phương tiện tự động, bao gồm: họ, tên, 
                                tên viết tắt, chi tiết của tài liệu nhận dạng, địa chỉ thường trú và nơi cư trú, điện thoại liên hệ.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">VI. Giải quyết tranh chấp</h2>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <h4 class="info-title">1. Khiếu nại</h4>
                            <p class="content-text">
                                Trong trường hợp xảy ra tranh chấp và bất đồng, Người sử dụng phải liên hệ với Website khiếu nại cụ thể tại địa chỉ: 
                                <a href="mailto:pinknovel25@gmail.com" class="contact-link">pinknovel25@gmail.com</a>. 
                                Trang Web đồng ý xem xét khiếu nại của Người sử dụng trong vòng 2 tuần và nỗ lực hết sức để giải quyết tranh chấp hoặc bất đồng.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">2. Luật áp dụng</h4>
                            <p class="content-text">
                                Nếu các tranh chấp không được giải quyết thông qua thương lượng, chúng sẽ được giải quyết theo cách thức mà 
                                luật pháp Việt Nam quy định.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <h2 class="section-title">VII. Điều khoản chung</h2>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <h4 class="info-title">1. Thay đổi điều khoản</h4>
                            <p class="content-text">
                                Chúng tôi có quyền sửa đổi các điều khoản này bất cứ lúc nào. Việc tiếp tục sử dụng Trang web sau khi có thay đổi 
                                được coi là chấp nhận các điều khoản mới.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">2. Chấm dứt</h4>
                            <p class="content-text">
                                Chúng tôi có quyền chấm dứt hoặc tạm ngưng quyền truy cập của bạn vào Trang web bất cứ lúc nào, 
                                với hoặc không có lý do, với hoặc không có thông báo trước.
                            </p>
                        </div>

                        <div class="info-item">
                            <h4 class="info-title">3. Liên hệ</h4>
                            <p class="content-text">
                                Nếu bạn có bất kỳ câu hỏi nào về các điều khoản này, vui lòng liên hệ với chúng tôi tại: 
                                <a href="mailto:pinknovel25@gmail.com" class="contact-link">pinknovel25@gmail.com</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .terms-content {
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
        .terms-content {
            padding: 2rem 1.5rem;
        }
        
        .section-title {
            font-size: 1.5rem;
        }
        
        .info-item {
            padding: 1rem;
        }
    }
</style>
@endpush
