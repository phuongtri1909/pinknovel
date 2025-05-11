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
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
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
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
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
</style>
@endpush

@section('info_content')
    <div class="deposit-container">
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
                                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                                    @foreach($banks as $bank)
                                    <div class="col">
                                        <div class="bank-option" data-bank-id="{{ $bank->id }}">
                                            <div class="d-flex align-items-center">
                                                @if($bank->logo)
                                                    <img src="{{ Storage::url($bank->logo) }}" alt="{{ $bank->name }}" class="bank-logo me-3">
                                                @else
                                                    <div class="bank-logo me-3 d-flex align-items-center justify-content-center bg-light">
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
                                    <input type="number" class="form-control deposit-amount-input" 
                                        id="amount" name="amount" value="{{ old('amount', 50000) }}" min="10000" step="10000">
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
                                        @if($bankTransferDiscount > 0)
                                        <div class="col-auto">
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-gift me-1"></i> +{{ $bankTransferDiscount }}% Khuyến mãi
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="small text-white opacity-75 mt-2">
                                        <i class="fas fa-info-circle me-1"></i> Tỷ giá: {{ number_format($coinExchangeRate) }} VNĐ = 1 xu
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
                        <p class="mb-2"><i class="fas fa-chevron-right me-2"></i> Dùng xu để mở khóa các tính năng cao cấp</p>
                        <p class="mb-0"><i class="fas fa-chevron-right me-2"></i> Thời gian xử lý nạp xu: 24h sau khi thanh toán</p>
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
                    @if($deposits->count() > 0)
                        @foreach($deposits as $deposit)
                            <div class="transaction-item">
                                <div class="transaction-header">
                                    <div class="transaction-id">
                                        <i class="fas fa-receipt me-2"></i> {{ $deposit->transaction_code }}
                                    </div>
                                    <div>
                                        @if($deposit->status == 'pending')
                                            <span class="transaction-status status-pending">
                                                <i class="fas fa-clock me-1"></i> Đang xử lý
                                            </span>
                                        @elseif($deposit->status == 'approved')
                                            <span class="transaction-status status-approved">
                                                <i class="fas fa-check me-1"></i> Đã duyệt
                                            </span>
                                        @else
                                            <span class="transaction-status status-rejected">
                                                <i class="fas fa-times me-1"></i> Từ chối
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="transaction-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <div class="transaction-image-container">
                                                @if($deposit->image)
                                                    <a href="{{ Storage::url($deposit->image) }}" target="_blank" data-fancybox="transaction-images">
                                                        <img src="{{ Storage::url($deposit->image) }}" alt="Biên lai" class="transaction-image">
                                                    </a>
                                                @else
                                                    <div class="text-center py-5 bg-light">
                                                        <i class="fas fa-image fa-3x text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-8">
                                            <div class="transaction-details">
                                                <div class="transaction-detail">
                                                    <div class="transaction-detail-label">Ngân hàng</div>
                                                    <div class="transaction-detail-value">{{ $deposit->bank->name }}</div>
                                                </div>
                                                
                                                <div class="transaction-detail">
                                                    <div class="transaction-detail-label">Số tiền</div>
                                                    <div class="transaction-detail-value">{{ number_format($deposit->amount) }} VNĐ</div>
                                                </div>
                                                
                                                <div class="transaction-detail">
                                                    <div class="transaction-detail-label">Xu</div>
                                                    <div class="transaction-detail-value">{{ number_format($deposit->coins) }}</div>
                                                </div>
                                                
                                                <div class="transaction-detail">
                                                    <div class="transaction-detail-label">Ngày tạo</div>
                                                    <div class="transaction-detail-value">{{ $deposit->created_at->format('d/m/Y H:i') }}</div>
                                                </div>
                                                
                                                @if($deposit->approved_at)
                                                <div class="transaction-detail">
                                                    <div class="transaction-detail-label">Ngày xử lý</div>
                                                    <div class="transaction-detail-value">{{ $deposit->approved_at->format('d/m/Y H:i') }}</div>
                                                </div>
                                                @endif
                                            </div>
                                            
                                            @if($deposit->status == 'rejected' && $deposit->note)
                                                <div class="transaction-feedback rejected">
                                                    <strong>Lý do từ chối:</strong> {{ $deposit->note }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="transaction-footer">
                                    <i class="fas fa-clock me-1"></i> {{ $deposit->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @endforeach
                        
                        <div class="d-flex justify-content-center mt-4">
                            {{ $deposits->links() }}
                        </div>
                    @else
                        <div class="empty-transactions">
                            <div>
                                <i class="fas fa-exchange-alt empty-transactions-icon"></i>
                            </div>
                            <h5>Chưa có giao dịch nào</h5>
                            <p class="empty-transactions-text">Bạn chưa thực hiện giao dịch nạp xu nào. Hãy nạp xu để sử dụng các tính năng trả phí.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Modal -->
    <div class="modal fade payment-modal" id="paymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thông tin thanh toán</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closePaymentModal"></button>
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
                            <div class="d-flex align-items-center">
                                <span class="payment-info-value" id="bankAccountNumber"></span>
                                <button type="button" class="copy-button" onclick="copyToClipboard('#bankAccountNumber')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="payment-info-item">
                            <span class="payment-info-label">Chủ tài khoản:</span>
                            <span class="payment-info-value" id="bankAccountName"></span>
                        </div>
                        
                        <div class="payment-info-item">
                            <span class="payment-info-label">Số tiền:</span>
                            <div class="d-flex align-items-center">
                                <span class="payment-info-value" id="paymentAmount"></span>
                                <button type="button" class="copy-button" onclick="copyToClipboard('#paymentAmount')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="payment-info-item">
                            <span class="payment-info-label">Nội dung chuyển khoản:</span>
                            <div class="d-flex align-items-center">
                                <span class="payment-info-value" id="transactionCode"></span>
                                <button type="button" class="copy-button" onclick="copyToClipboard('#transactionCode')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle me-2"></i> <strong>Lưu ý:</strong> Vui lòng nhập chính xác nội dung chuyển khoản để hệ thống có thể xác nhận giao dịch của bạn.
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
    
    <!-- Upload Evidence Modal -->
    <div class="modal fade" id="uploadEvidenceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tải lên chứng từ thanh toán</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="evidenceForm" action="{{ route('user.deposit.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="bank_id" id="evidenceBankId">
                        <input type="hidden" name="amount" id="evidenceAmount">
                        <input type="hidden" name="transaction_code" id="evidenceTransactionCode">
                        <input type="hidden" name="coins" id="evidenceCoins">
                        
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
                                <input type="file" class="form-control" id="transaction_image" name="transaction_image" accept="image/*" required>
                                <div class="invalid-feedback">Vui lòng tải lên ảnh chứng minh chuyển khoản</div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i> Sau khi gửi chứng từ thanh toán, yêu cầu nạp xu của bạn sẽ được xử lý trong vòng 24 giờ làm việc.
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn payment-btn">
                                <i class="fas fa-paper-plane me-2"></i> Gửi yêu cầu nạp xu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('info_scripts')
<script>
    // Global variables to store payment info
    let paymentInfo = {
        bank: null,
        amount: 0,
        baseCoins: 0,
        bonusCoins: 0,
        totalCoins: 0,
        discount: 0,
        transactionCode: ''
    };
    
    const coinExchangeRate = {{ $coinExchangeRate }};
    const bankTransferDiscount = {{ $bankTransferDiscount }};
    
    // Xử lý khi người dùng rời trang trong quá trình thanh toán
    window.addEventListener('beforeunload', function(e) {
        if ($('#paymentModal').hasClass('show')) {
            e.preventDefault();
            e.returnValue = 'Bạn đang trong quá trình thanh toán. Nếu rời khỏi trang, thông tin thanh toán sẽ bị mất.';
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
        const baseCoins = Math.floor(amount / coinExchangeRate);
        // Bonus coins based on discount
        const bonusCoins = Math.floor(baseCoins * (bankTransferDiscount / 100));
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
            
            // Call the validate payment endpoint
            $.ajax({
                url: '{{ route("user.deposit.validate") }}',
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
                        paymentInfo = {
                            bank: response.bank,
                            amount: response.payment.amount,
                            baseCoins: response.payment.base_coins,
                            bonusCoins: response.payment.bonus_coins,
                            totalCoins: response.payment.total_coins,
                            discount: response.payment.discount,
                            transactionCode: response.payment.transaction_code
                        };
                        
                        // Populate payment modal
                        populatePaymentModal();
                        
                        // Show payment modal
                        $('#paymentModal').modal('show');
                    } else {
                        showErrorAlert('Có lỗi xảy ra: ' + (response.message || 'Không thể xử lý thanh toán'));
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
                    $('#proceedToPaymentBtn').prop('disabled', false).html('<i class="fas fa-wallet"></i> Tiến hành thanh toán');
                }
            });
        }
    });
    
    // Populate payment modal with data
    function populatePaymentModal() {
        $('#bankName').text(paymentInfo.bank.name + ' (' + paymentInfo.bank.code + ')');
        $('#bankAccountNumber').text(paymentInfo.bank.account_number);
        $('#bankAccountName').text(paymentInfo.bank.account_name);
        $('#paymentAmount').text(paymentInfo.amount.toLocaleString('vi-VN') + ' VNĐ');
        $('#transactionCode').text(paymentInfo.transactionCode);
        
        // Display QR code if available
        if (paymentInfo.bank.qr_code) {
            $('#bankQrCode').attr('src', paymentInfo.bank.qr_code).removeClass('d-none');
            $('#qrCodePlaceholder').addClass('d-none');
        } else {
            $('#bankQrCode').addClass('d-none');
            $('#qrCodePlaceholder').removeClass('d-none')
                .html('<div class="text-center text-muted"><i class="fas fa-qrcode fa-3x mb-2"></i><p>QR code không khả dụng</p></div>');
        }
    }
    
    // Xử lý nút "tôi đã chuyển khoản"
    $('#confirmPaymentBtn').on('click', function() {
        // Populate evidence form with data
        $('#evidenceBankId').val($('#bankId').val());
        $('#evidenceAmount').val(paymentInfo.amount);
        $('#evidenceTransactionCode').val(paymentInfo.transactionCode);
        $('#evidenceCoins').val(paymentInfo.totalCoins);
        
        // Hide payment modal and show upload evidence modal
        $('#paymentModal').modal('hide');
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
    
    // Kiểm tra form trước khi submit
    $('#evidenceForm').on('submit', function(e) {
        if (!$('#transaction_image').val()) {
            e.preventDefault();
            $('#transaction_image').addClass('is-invalid');
            return false;
        }
        
        $('#transaction_image').removeClass('is-invalid');
        return true;
    });
    
    // Tiện ích copy vào clipboard
    function copyToClipboard(element) {
        const $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).text()).select();
        document.execCommand("copy");
        $temp.remove();
        
        // Show feedback
        const $button = $(element).next('.copy-button');
        const originalText = $button.html();
        $button.html('<i class="fas fa-check"></i>');
        
        setTimeout(function() {
            $button.html(originalText);
        }, 1000);
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
</script>
@endpush