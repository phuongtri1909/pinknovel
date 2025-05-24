@extends('layouts.information')

@section('info_title', 'Nạp xu')
@section('info_description', 'Nạp xu vào tài khoản của bạn trên ' . request()->getHost())
@section('info_keyword', 'nạp xu, thanh toán, ' . request()->getHost())
@section('info_section_title', 'Nạp xu')
@section('info_section_desc', 'Nạp xu vào tài khoản để sử dụng các dịch vụ cao cấp')

@push('styles')
    <style>
        .bank-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .bank-card:hover {
            border-color: #ced4da;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .bank-card.selected {
            border-color: var(--primary-color-3);
            background-color: rgba(var(--primary-rgb), 0.05);
        }

        .bank-logo {
            width: 80px;
            height: 40px;
            object-fit: contain;
        }

        .bank-info {
            font-size: 14px;
            color: #555;
        }

        .transaction-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .transaction-item:hover {
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .transaction-image {
            max-width: 100%;
            height: auto;
            max-height: 300px;
            border-radius: 5px;
        }

        .status-pending {
            color: #ff9800;
        }

        .status-approved {
            color: #4caf50;
        }

        .status-rejected {
            color: #f44336;
        }

        .deposit-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 5px;
        }

        /* Cải thiện hiệu ứng nút copy */
        .copy-button {
            border: none;
            background-color: transparent;
            color: var(--primary-color-3);
            padding: 0.25rem 0.5rem;
            margin-left: 0.5rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .copy-button:hover {
            background-color: rgba(var(--primary-rgb), 0.1);
            color: var(--primary-color-2);
        }

        .copy-button:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.25);
        }

        .copy-button i {
            font-size: 0.875rem;
        }

        /* Đảm bảo phần nội dung có thể sao chép có thể chọn được */
        .payment-info-value {
            user-select: all;
            cursor: text;
        }

        /* Thiết kế cho thông báo copy */
        .copy-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 14px;
            z-index: 9999;
            opacity: 0.9;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.3s ease;
        }

        .copy-toast.success {
            background-color: #198754;
            color: white;
        }

        .copy-toast.error {
            background-color: #dc3545;
            color: white;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 0.9;
                transform: translateY(0);
            }
        }

        /* Cải thiện khả năng tương tác cho thông tin thanh toán */
        .payment-info-value {
            position: relative;
            user-select: all;
            cursor: text;
            padding: 3px 5px;
            border-radius: 3px;
            transition: background-color 0.2s;
        }

        .payment-info-value:hover {
            background-color: rgba(var(--primary-rgb), 0.05);
        }

        .payment-info-value:focus {
            background-color: rgba(var(--primary-rgb), 0.1);
            outline: none;
        }

        /* Hiệu ứng khi sao chép thành công */
        .copy-success {
            animation: flashSuccess 0.5s;
        }

        @keyframes flashSuccess {

            0%,
            100% {
                background-color: transparent;
            }

            50% {
                background-color: rgba(25, 135, 84, 0.1);
            }
        }
    </style>
@endpush

@section('info_content')
    <div class="deposit-container" id="depositContainer">
        <div class="row">
            <div class="col-lg-8">
                <div class="deposit-card">
                    <div class="deposit-card-header">
                        <h5 class="mb-0">Nạp xu qua chuyển khoản ngân hàng</h5>
                    </div>
                    <div class="deposit-card-body">
                        <form id="depositForm">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-bold mb-3">Chọn ngân hàng</label>
                                <div class="row">
                                    @foreach ($banks as $bank)
                                        <div class="col-6">
                                            <div class="bank-option" data-bank-id="{{ $bank->id }}">
                                                <div class="d-flex align-items-center">
                                                    @if ($bank->logo)
                                                        <img src="{{ Storage::url($bank->logo) }}" alt="{{ $bank->name }}"
                                                            class="bank-logo me-3">
                                                    @else
                                                        <div
                                                            class="bank-logo me-3 d-flex align-items-center justify-content-center bg-light">
                                                            <i class="fas fa-university fa-2x"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-1">{{ $bank->name }}</h6>
                                                        <div class="small text-muted">{{ $bank->code }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="bank_id" id="bankId" required>
                                <div class="invalid-feedback bank-error">Vui lòng chọn ngân hàng</div>
                            </div>

                            <div class="deposit-amount-container">
                                <label for="amount" class="form-label fw-bold mb-3">Nhập số tiền muốn nạp (VNĐ)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control deposit-amount-input" id="amount"
                                        name="amount" value="{{ old('amount', 50000) }}" min="10000" step="10000">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                                <div class="form-text">Số tiền tối thiểu: 10.000 VNĐ</div>
                                <div class="invalid-feedback amount-error">Vui lòng nhập số tiền hợp lệ</div>

                                <div class="deposit-coin-preview mt-4">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="small text-white opacity-75">Xu nhận được:</div>
                                            <div class="coin-preview-value">
                                                <i class="fas fa-coins me-2"></i> <span id="coinsPreview">50</span>
                                            </div>
                                        </div>
                                        @if ($bankTransferDiscount > 0)
                                            <div class="col-auto">
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-gift me-1"></i> +{{ $bankTransferDiscount }}% Khuyến
                                                    mãi
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="small text-white opacity-75 mt-2">
                                        <i class="fas fa-info-circle me-1"></i> Tỷ giá:
                                        {{ number_format($coinExchangeRate) }} VNĐ = 1 xu
                                    </div>
                                </div>

                                <button type="button" id="proceedToPaymentBtn" class="btn payment-btn w-100">
                                    <i class="fas fa-wallet"></i> Tiến hành thanh toán
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="deposit-card">
                    <div class="deposit-card-header">
                        <h5 class="mb-0">Nạp xu bằng thẻ cào (Sắp ra mắt)</h5>
                    </div>
                    <div class="deposit-card-body">
                        <div class="deposit-method disabled" style="opacity: 0.6; cursor: not-allowed;">
                            <div class="d-flex align-items-center">
                                <div class="deposit-method-icon">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Nạp bằng thẻ cào điện thoại</h6>
                                    <p class="mb-0 small text-muted">Hỗ trợ các loại thẻ: Viettel, Mobifone, Vinaphone...</p>
                                </div>
                                <div class="ms-auto">
                                    <span class="badge bg-secondary">Sắp ra mắt</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i> Tính năng nạp xu bằng thẻ cào sẽ sớm được ra mắt. Vui lòng sử dụng chuyển khoản ngân hàng để nạp xu trong thời gian này.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="coins-panel">
                    <div class="coins-balance">
                        <i class="fas fa-coins coins-icon"></i> {{ number_format(Auth::user()->coins ?? 0) }}
                    </div>
                    <div class="coins-label">Số xu hiện có trong tài khoản</div>

                    <div class="coins-info">
                        <p class="mb-2"><i class="fas fa-chevron-right me-2"></i> Dùng xu để đọc truyện trả phí</p>
                        <p class="mb-2"><i class="fas fa-chevron-right me-2"></i> Dùng xu để mở khóa các tính năng cao cấp
                        </p>
                        <p class="mb-0"><i class="fas fa-chevron-right me-2"></i> Thời gian xử lý nạp xu: 24h sau khi
                            thanh toán</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="transaction-history">
            <div class="deposit-card">
                <div class="deposit-card-header">
                    <h5 class="mb-0">Lịch sử giao dịch</h5>
                </div>
                <div class="deposit-card-body">
                    @if ($deposits->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 15%">Mã giao dịch</th>
                                        <th style="width: 15%">Ngân hàng</th>
                                        <th style="width: 15%">Số tiền</th>
                                        <th style="width: 15%">Xu</th>
                                        <th style="width: 15%">Ngày tạo</th>
                                        <th style="width: 10%">Trạng thái</th>
                                        <th style="width: 15%">Biên lai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($deposits as $deposit)
                                        <tr>
                                            <td class="align-middle">
                                                <small class="text-muted">{{ $deposit->transaction_code }}</small>
                                            </td>
                                            <td class="align-middle">{{ $deposit->bank->name }}</td>
                                            <td class="align-middle">{{ number_format($deposit->amount) }} VNĐ</td>
                                            <td class="align-middle">{{ number_format($deposit->coins) }}</td>
                                            <td class="align-middle">
                                                <div>{{ $deposit->created_at->format('d/m/Y H:i') }}</div>
                                                <small class="text-muted">{{ $deposit->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td class="align-middle">
                                                @if ($deposit->status == 'pending')
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-clock me-1"></i> Đang xử lý
                                                    </span>
                                                @elseif($deposit->status == 'approved')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i> Đã duyệt
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times me-1"></i> Từ chối
                                                    </span>
                                                @endif
                                                
                                                @if ($deposit->status == 'rejected' && $deposit->note)
                                                    <div class="mt-1">
                                                        <a href="#" class="small text-danger" data-bs-toggle="tooltip" title="{{ $deposit->note }}">
                                                            <i class="fas fa-info-circle"></i> Lý do
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @if ($deposit->image)
                                                    <a href="{{ Storage::url($deposit->image) }}" class="btn btn-sm btn-outline-primary" 
                                                       data-fancybox="transaction-images" data-caption="Biên lai #{{ $deposit->transaction_code }}">
                                                        <i class="fas fa-image me-1"></i> Xem
                                                    </a>
                                                @else
                                                    <span class="text-muted small">
                                                        <i class="fas fa-ban me-1"></i> Không có
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $deposits->links() }}
                        </div>
                    @else
                        <div class="empty-transactions text-center py-5">
                            <div>
                                <i class="fas fa-exchange-alt empty-transactions-icon"></i>
                            </div>
                            <h5>Chưa có giao dịch nào</h5>
                            <p class="empty-transactions-text">Bạn chưa thực hiện giao dịch nạp xu nào. Hãy nạp xu để sử
                                dụng các tính năng trả phí.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade payment-modal" id="paymentModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thông tin thanh toán</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="closePaymentModal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="payment-qr-code mb-3" id="qrCodeContainer">
                            <img src="" alt="QR Code" id="bankQrCode" class="d-none">
                            <div class="d-flex align-items-center justify-content-center h-100" id="qrCodePlaceholder">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted">Quét mã QR để thực hiện thanh toán</p>
                    </div>

                    <div class="payment-info">
                        <div class="payment-info-item">
                            <span class="payment-info-label">Ngân hàng:</span>
                            <span class="payment-info-value" id="bankName"></span>
                        </div>

                        <div class="payment-info-item">
                            <span class="payment-info-label">Số tài khoản:</span>
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center">
                                    <span class="payment-info-value" id="bankAccountNumber" tabindex="0"
                                        onclick="this.focus();this.select()" onfocus="this.select()"></span>
                                    <button type="button" class="copy-button"
                                        onclick="copyToClipboard('#bankAccountNumber')" title="Sao chép số tài khoản">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="payment-info-item">
                            <span class="payment-info-label">Chủ tài khoản:</span>
                            <span class="payment-info-value" id="bankAccountName"></span>
                        </div>

                        <div class="payment-info-item">
                            <span class="payment-info-label">Số tiền:</span>
                            <div class="d-flex align-items-center">
                                <span class="payment-info-value" id="paymentAmount" tabindex="0"
                                    onclick="this.focus();this.select()" onfocus="this.select()"></span>
                                <span class="ms-1 fw-bold">VNĐ</span>
                                <button type="button" class="copy-button" onclick="copyToClipboard('#paymentAmount')"
                                    title="Sao chép số tiền">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <div class="payment-info-item">
                            <span class="payment-info-label">Nội dung chuyển khoản:</span>
                            <div class="d-flex align-items-center">
                                <span class="payment-info-value" id="transactionCode" tabindex="0"
                                    onclick="this.focus();this.select()" onfocus="this.select()"></span>
                                <button type="button" class="copy-button" onclick="copyToClipboard('#transactionCode')"
                                    title="Sao chép nội dung chuyển khoản">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <div class="payment-info-item" id="expiryContainer">
                            <span class="payment-info-label">Thời hạn:</span>
                            <div class="d-flex align-items-center">
                                <span class="payment-info-value" id="paymentExpiry"></span>
                                <span class="ms-2 badge bg-warning text-dark">
                                    <i class="fas fa-clock me-1"></i> <span id="countdownTimer">Đang tính toán...</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle me-2"></i> <strong>Lưu ý:</strong> Vui lòng nhập chính xác
                        nội dung chuyển khoản để hệ thống có thể xác nhận giao dịch của bạn.
                        <br> Giữ biên lai để làm minh chứng.
                    </div>

                    <div class="payment-confirmation">
                        <button type="button" class="confirm-payment-btn" id="confirmPaymentBtn">
                            <i class="fas fa-check-circle me-2"></i> Tôi đã chuyển khoản
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Payment Confirmation Modal -->
    <div class="modal fade" id="cancelPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i> Xác nhận hủy thanh toán
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <i class="fas fa-hand-paper text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h5>Bạn có chắc chắn muốn hủy giao dịch này?</h5>
                    <p class="text-muted">Thông tin giao dịch sẽ không được lưu lại nếu bạn hủy.</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-2"></i> Tiếp tục thanh toán
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="confirmCancelPayment">
                        <i class="fas fa-times-circle me-2"></i> Đồng ý hủy
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Evidence Modal -->
    <div class="modal fade" id="uploadEvidenceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tải lên chứng từ thanh toán</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="evidenceForm">
                        @csrf
                        <input type="hidden" name="request_payment_id" id="evidenceRequestPaymentId">

                        <div class="transaction-image-upload text-center">
                            <div id="uploadIconContainer">
                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                <h6>Tải lên ảnh chứng minh chuyển khoản</h6>
                                <p class="text-muted small">Hỗ trợ định dạng: JPG, PNG, GIF (tối đa 4MB)</p>
                            </div>

                            <div id="previewContainer" class="mt-3 d-none">
                                <img src="" id="evidencePreview" class="transaction-image-preview">
                            </div>

                            <div class="mt-3">
                                <input type="file" class="form-control" id="transaction_image"
                                    name="transaction_image" accept="image/*" required>
                                <div class="invalid-feedback">Vui lòng tải lên ảnh chứng minh chuyển khoản</div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i> Sau khi gửi chứng từ thanh toán, yêu cầu nạp xu của bạn
                            sẽ được xử lý trong vòng 24 giờ làm việc.
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn payment-btn" id="evidenceSubmitBtn">
                                <i class="fas fa-paper-plane me-2"></i> Gửi yêu cầu nạp xu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@once
    @push('info_scripts')
        <script>
            // Global variables to store payment info
            window.paymentInfo = {
                bank: null,
                amount: 0,
                baseCoins: 0,
                bonusCoins: 0,
                totalCoins: 0,
                discount: 0,
                transactionCode: '',
                requestPaymentId: null,
                expiredAt: null
            };

            window.coinExchangeRate = {{ $coinExchangeRate }};
            window.bankTransferDiscount = {{ $bankTransferDiscount }};

            // Xử lý khi người dùng rời trang trong quá trình thanh toán
            window.addEventListener('beforeunload', function(e) {
                if ($('#paymentModal').hasClass('show')) {
                    e.preventDefault();
                    e.returnValue =
                        'Bạn đang trong quá trình thanh toán. Nếu rời khỏi trang, thông tin thanh toán sẽ bị mất.';
                    return e.returnValue;
                }
            });

            // Xử lý chọn ngân hàng
            $(document).on('click', '.bank-option', function() {
                $('.bank-option').removeClass('selected');
                $(this).addClass('selected');
                $('#bankId').val($(this).data('bank-id'));
                $('.bank-error').hide();
            });

            // Cập nhật preview số xu
            $('#amount').on('input', function() {
                updateCoinPreview();
            });

            function updateCoinPreview() {
                const amount = parseInt($('#amount').val()) || 0;
                // Base coins calculation
                const baseCoins = Math.floor(amount / window.coinExchangeRate);
                // Bonus coins based on discount
                const bonusCoins = Math.floor(baseCoins * (window.bankTransferDiscount / 100));
                // Total coins
                const totalCoins = baseCoins + bonusCoins;

                $('#coinsPreview').text(totalCoins.toLocaleString('vi-VN'));
            }

            // Initialize coin preview
            updateCoinPreview();

            // Xử lý nút thanh toán
            $('#proceedToPaymentBtn').on('click', function() {
                let valid = true;

                // Validate bank selection
                if (!$('#bankId').val()) {
                    $('.bank-error').show();
                    valid = false;
                } else {
                    $('.bank-error').hide();
                }

                // Validate amount
                const amount = parseInt($('#amount').val()) || 0;
                if (amount < 10000) {
                    $('.amount-error').show().text('Số tiền tối thiểu là 10.000 VNĐ');
                    valid = false;
                } else {
                    $('.amount-error').hide();
                }

                if (valid) {
                    // Prepare payment data
                    const bankId = $('#bankId').val();

                    // Show loading state
                    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');

                    // Call to create request payment instead of direct validation
                    $.ajax({
                        url: '{{ route('user.request.payment.store') }}',
                        type: 'POST',
                        data: {
                            bank_id: bankId,
                            amount: amount,
                            _token: $('input[name="_token"]').val()
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Store payment info
                                window.paymentInfo = {
                                    bank: response.bank,
                                    amount: response.payment.amount,
                                    baseCoins: response.payment.base_coins,
                                    bonusCoins: response.payment.bonus_coins,
                                    totalCoins: response.payment.total_coins,
                                    discount: response.payment.discount,
                                    transactionCode: response.payment.transaction_code,
                                    requestPaymentId: response.request_payment_id,
                                    expiredAt: response.payment.expired_at
                                };

                                // Populate payment modal
                                populatePaymentModal();

                                // Show payment modal using bootstrap instance
                                var paymentModalEl = document.getElementById('paymentModal');
                                var paymentModal = new bootstrap.Modal(paymentModalEl);
                                paymentModal.show();
                            } else {
                                showErrorAlert('Có lỗi xảy ra: ' + (response.message ||
                                    'Không thể xử lý thanh toán'));
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Đã xảy ra lỗi khi xử lý yêu cầu';

                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                errorMessage = Object.values(errors)[0][0];
                            }

                            showErrorAlert(errorMessage);
                        },
                        complete: function() {
                            // Reset button state
                            $('#proceedToPaymentBtn').prop('disabled', false).html(
                                '<i class="fas fa-wallet"></i> Tiến hành thanh toán');
                        }
                    });
                }
            });

            // Populate payment modal with data
            function populatePaymentModal() {
                $('#bankName').text(window.paymentInfo.bank.name + ' (' + window.paymentInfo.bank.code + ')');
                $('#bankAccountNumber').text(window.paymentInfo.bank.account_number);
                $('#bankAccountName').text(window.paymentInfo.bank.account_name);
                // Chỉ hiển thị số tiền, không kèm "VNĐ"
                $('#paymentAmount').text(window.paymentInfo.amount.toLocaleString('vi-VN'));
                $('#transactionCode').text(window.paymentInfo.transactionCode);
                
                // Hiển thị thời gian hết hạn
                if (window.paymentInfo.expiredAt) {
                    const expiredDate = new Date(window.paymentInfo.expiredAt);
                    $('#paymentExpiry').text(expiredDate.toLocaleString('vi-VN'));
                    $('#expiryContainer').removeClass('d-none');
                    
                    // Bắt đầu đếm ngược
                    startCountdown(expiredDate);
                } else {
                    $('#expiryContainer').addClass('d-none');
                }

                // Display QR code if available
                if (window.paymentInfo.bank.qr_code) {
                    $('#bankQrCode').attr('src', window.paymentInfo.bank.qr_code).removeClass('d-none');
                    $('#qrCodePlaceholder').addClass('d-none');
                } else {
                    $('#bankQrCode').addClass('d-none');
                    $('#qrCodePlaceholder').removeClass('d-none')
                        .html(
                            '<div class="text-center text-muted"><i class="fas fa-qrcode fa-3x mb-2"></i><p>QR code không khả dụng</p></div>'
                            );
                }
            }

            // Đếm ngược thời gian
            let countdownInterval;
            
            function startCountdown(expiredDate) {
                // Xóa interval cũ nếu có
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }
                
                // Cập nhật đếm ngược mỗi giây
                function updateCountdown() {
                    const now = new Date().getTime();
                    const expiredTime = expiredDate.getTime();
                    const timeRemaining = expiredTime - now;
                    
                    if (timeRemaining <= 0) {
                        // Hết thời gian
                        clearInterval(countdownInterval);
                        $('#countdownTimer').html('<span class="text-danger">Đã hết hạn</span>');
                        // Có thể thêm xử lý khi hết hạn ở đây (ví dụ: ẩn nút xác nhận)
                        $('#confirmPaymentBtn').prop('disabled', true)
                            .html('<i class="fas fa-exclamation-circle me-2"></i> Đã hết hạn thanh toán');
                        
                        // Hiển thị thông báo
                        showNotification('Yêu cầu thanh toán đã hết hạn. Vui lòng tạo yêu cầu mới.', 'warning');
                        return;
                    }
                    
                    // Tính toán thời gian còn lại
                    const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);
                    
                    // Định dạng chuỗi hiển thị
                    let countdownText = '';
                    if (hours > 0) {
                        countdownText += hours + ' giờ ';
                    }
                    if (hours > 0 || minutes > 0) {
                        countdownText += minutes + ' phút ';
                    }
                    countdownText += seconds + ' giây';
                    
                    // Cập nhật giao diện
                    $('#countdownTimer').text(countdownText);
                    
                    // Đổi màu khi gần hết hạn (dưới 10 phút)
                    if (timeRemaining < 10 * 60 * 1000) {
                        $('#countdownTimer').addClass('text-danger fw-bold');
                    } else {
                        $('#countdownTimer').removeClass('text-danger fw-bold');
                    }
                }
                
                // Cập nhật ngay lập tức
                updateCountdown();
                
                // Cập nhật mỗi giây
                countdownInterval = setInterval(updateCountdown, 1000);
            }

            // Xử lý nút "tôi đã chuyển khoản"
            $('#confirmPaymentBtn').on('click', function() {
                // Hide payment modal and show upload evidence modal
                $('#paymentModal').modal('hide');
                
                // Populate evidence form with data
                $('#evidenceRequestPaymentId').val(window.paymentInfo.requestPaymentId);
                
                setTimeout(function() {
                    $('#uploadEvidenceModal').modal('show');
                }, 500);
            });

            // Xử lý preview hình ảnh
            $('#transaction_image').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#evidencePreview').attr('src', e.target.result);
                        $('#previewContainer').removeClass('d-none');
                        $('#uploadIconContainer').addClass('d-none');
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#previewContainer').addClass('d-none');
                    $('#uploadIconContainer').removeClass('d-none');
                }
            });

            // Submit xác nhận đã chuyển khoản với upload ảnh
            $('#evidenceForm').on('submit', function(e) {
                e.preventDefault();
                
                if (!$('#transaction_image').val()) {
                    $('#transaction_image').addClass('is-invalid');
                    return;
                }
                
                $('#transaction_image').removeClass('is-invalid');
                
                // Sử dụng FormData để gửi file
                var formData = new FormData(this);
                
                // Hiển thị trạng thái loading
                $('#evidenceSubmitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Đang xử lý...');
                
                // Gọi API xác nhận thanh toán
                $.ajax({
                    url: '{{ route('user.request.payment.confirm') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Đóng modal
                            $('#uploadEvidenceModal').modal('hide');
                            
                            // Hiển thị thông báo thành công
                            setTimeout(function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Thành công!',
                                    text: response.message,
                                    confirmButtonColor: 'var(--primary-color-3)',
                                    confirmButtonText: 'Đóng'
                                }).then((result) => {
                                    // Reload trang để cập nhật danh sách giao dịch
                                    window.location.reload();
                                });
                            }, 500);
                        } else {
                            showErrorAlert(response.message || 'Có lỗi xảy ra khi xử lý yêu cầu.');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Đã xảy ra lỗi khi xử lý yêu cầu';
                        
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors)[0][0];
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        showErrorAlert(errorMessage);
                    },
                    complete: function() {
                        // Khôi phục trạng thái nút
                        $('#evidenceSubmitBtn').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Gửi yêu cầu nạp xu');
                    }
                });
            });

            // Tiện ích copy vào clipboard - phiên bản nâng cao
            function copyToClipboard(element) {
                const textToCopy = $(element).text().trim();
                const $button = $(element).next('.copy-button');
                const originalText = $button.html();

                // Hiển thị trạng thái đang xử lý
                $button.html('<i class="fas fa-spinner fa-spin"></i>');

                // Phương pháp 1: Clipboard API (chỉ hoạt động trên HTTPS hoặc localhost)
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(textToCopy)
                        .then(() => {
                            showCopySuccess($button, originalText);
                        })
                        .catch(() => {
                            // Nếu phương pháp 1 thất bại, thử phương pháp 2
                            copyUsingExecCommand(element, $button, originalText);
                        });
                }
                // Phương pháp 2: document.execCommand (hỗ trợ cũ)
                else {
                    copyUsingExecCommand(element, $button, originalText);
                }
            }

            // Phương pháp sao chép bằng execCommand
            function copyUsingExecCommand(element, $button, originalText) {
                try {
                    // Tạo vùng chọn văn bản và chọn nội dung
                    const range = document.createRange();
                    range.selectNode($(element)[0]);
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);

                    // Thực hiện lệnh sao chép
                    const successful = document.execCommand('copy');

                    // Xóa vùng chọn
                    window.getSelection().removeAllRanges();

                    if (successful) {
                        showCopySuccess($button, originalText);
                    } else {
                        // Nếu không thành công, thử phương pháp 3
                        copyUsingTempTextarea($(element).text().trim(), $button, originalText);
                    }
                } catch (err) {
                    // Nếu có lỗi, thử phương pháp 3
                    copyUsingTempTextarea($(element).text().trim(), $button, originalText);
                }
            }

            // Phương pháp sao chép bằng textarea tạm thời
            function copyUsingTempTextarea(text, $button, originalText) {
                try {
                    // Tạo phần tử input tạm thời
                    const $temp = $("<input>");
                    $("body").append($temp);
                    $temp.val(text).select();

                    // Thực hiện lệnh sao chép
                    const successful = document.execCommand('copy');

                    // Dọn dẹp
                    $temp.remove();

                    if (successful) {
                        showCopySuccess($button, originalText);
                    } else {
                        showCopyFailure($button, originalText);
                    }
                } catch (err) {
                    showCopyFailure($button, originalText);
                }
            }

            // Hiển thị thành công
            function showCopySuccess($button, originalText) {
                $button.html('<i class="fas fa-check"></i>');

                // Tạo toast thông báo nhỏ
                $('<div class="copy-toast success">Đã sao chép</div>')
                    .appendTo('body')
                    .fadeIn(200)
                    .delay(1500)
                    .fadeOut(200, function() {
                        $(this).remove();
                    });

                // Khôi phục nút sau 1 giây
                setTimeout(() => $button.html(originalText), 1000);
            }

            // Hiển thị thất bại
            function showCopyFailure($button, originalText) {
                $button.html('<i class="fas fa-times"></i>');

                // Hiển thị hướng dẫn sao chép thủ công
                $('<div class="copy-toast error">Không thể tự động sao chép. Vui lòng nhấp vào văn bản và chọn Sao chép.</div>')
                    .appendTo('body')
                    .fadeIn(200)
                    .delay(3000)
                    .fadeOut(200, function() {
                        $(this).remove();
                    });

                // Khôi phục nút sau 1 giây
                setTimeout(() => $button.html(originalText), 1000);
            }

            // Hiển thị thông báo nhỏ
            function showNotification(message, type = 'info') {
                // Kiểm tra xem SweetAlert2 đã được định nghĩa chưa
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    Toast.fire({
                        icon: type,
                        title: message
                    });
                } else {
                    // Nếu không có SweetAlert, hiển thị thông báo đơn giản
                    if (!document.getElementById('copy-notification')) {
                        const notif = document.createElement('div');
                        notif.id = 'copy-notification';
                        notif.style.position = 'fixed';
                        notif.style.bottom = '20px';
                        notif.style.right = '20px';
                        notif.style.padding = '10px 15px';
                        notif.style.borderRadius = '4px';
                        notif.style.backgroundColor = type === 'warning' ? '#fff3cd' : '#d1e7dd';
                        notif.style.color = type === 'warning' ? '#664d03' : '#0f5132';
                        notif.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
                        notif.style.zIndex = '9999';
                        notif.style.opacity = '0';
                        notif.style.transition = 'opacity 0.3s ease';
                        notif.textContent = message;

                        document.body.appendChild(notif);

                        // Hiển thị và ẩn thông báo
                        setTimeout(() => {
                            notif.style.opacity = '1';
                            setTimeout(() => {
                                notif.style.opacity = '0';
                                setTimeout(() => {
                                    document.body.removeChild(notif);
                                }, 300);
                            }, 2700);
                        }, 10);
                    }
                }
            }

            // Hiển thị thông báo lỗi
            function showErrorAlert(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: message,
                    confirmButtonText: 'Đóng',
                    confirmButtonColor: 'var(--primary-color-3)'
                });
            }

            // Xử lý khi nhấn nút đóng modal thanh toán
            $(document).ready(function() {
                // Xử lý sự kiện khi nhấn nút X hoặc nút đóng
                $('#closePaymentModal, #paymentModal .btn-close').on('click', function(e) {
                    e.preventDefault();
                    // Hiển thị modal xác nhận hủy
                    $('#cancelPaymentModal').modal('show');
                });

                // Xử lý khi người dùng xác nhận hủy
                $('#confirmCancelPayment').on('click', function() {
                    
                    $('#cancelPaymentModal').modal('hide');
                    
                    // Đánh dấu modal để cho phép đóng
                    $('#paymentModal').data('force-close', true);
                    
                    // Đóng modal thanh toán
                    setTimeout(function() {
                        $('#paymentModal').modal('hide');
                        
                        // Xóa backdrop và reset body
                        setTimeout(function() {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open');
                            $('body').css('padding-right', '');
                            $('#paymentModal').data('force-close', false);
                        }, 300);
                        
                        // Hiển thị thông báo đã hủy
                        showNotification('Đã hủy giao dịch thanh toán', 'info');
                    }, 300);
                });

                // Ngăn chặn sự kiện đóng khi người dùng click ra ngoài modal
                $('#paymentModal').on('hide.bs.modal', function(e) {
                    if (e.namespace === 'bs.modal' && !e.relatedTarget) {
                        if ($(this).data('force-close') !== true) {
                            e.preventDefault();
                            if (!$(document.activeElement).is('#confirmPaymentBtn')) {
                                $('#cancelPaymentModal').modal('show');
                            }
                        }
                    }
                });

                // Kích hoạt tooltip cho lý do từ chối
                $('[data-bs-toggle="tooltip"]').tooltip();
            });
        </script>
    @endpush
@endonce
