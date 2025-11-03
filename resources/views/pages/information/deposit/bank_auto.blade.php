@extends('layouts.information')

@section('info_title', 'Nạp xu tự động')
@section('info_description', 'Nạp xu tự động qua trên ' . request()->getHost())
@section('info_keyword', 'nạp xu, thanh toán tự động, Casso, ' . request()->getHost())
@section('info_section_title', 'Nạp xu tự động')
@section('info_section_desc', 'Nạp xu tự động với nhiều ưu đãi hấp dẫn')

@push('styles')
    <style>
        /* Bank specific styles */
        .bank-logo {
            width: 80px;
            height: 40px;
            object-fit: contain;
        }

        .bank-info {
            font-size: 14px;
            color: #555;
        }

        /* Payment info value interactions */
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

        .copy-button {
            padding: 2px 6px;
            font-size: 12px;
        }

        .payment-qr-code {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 15px;
            background-color: #f8f9fa;
        }

        /* Bank deposit specific styles */
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

        /* Payment info value interactions */
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

        /* Bank specific reason modal */
        .reason-content {
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #dc3545;
        }

        #reasonText {
            white-space: pre-line;
            color: #444;
            font-size: 15px;
        }

        .show-reason-btn {
            cursor: pointer;
        }

        .show-reason-btn:hover {
            text-decoration: underline;
        }


        .deposit-tabs {
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 30px;
        }

        .deposit-tab {
            padding: 15px 25px;
            background: transparent;
            border: none;
            color: #6c757d;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .deposit-tab:hover {
            color: var(--primary-color-3);
            background-color: rgba(13, 110, 253, 0.05);
            text-decoration: none;
        }

        .deposit-tab.active {
            color: var(--primary-color-3);
            border-bottom-color: var(--primary-color-3);
            background-color: rgba(13, 110, 253, 0.05);
        }

        /* Payment info styles */
        .payment-info {
            margin-top: 20px;
        }

        .payment-info-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 0;
            gap: 10px;
        }

        .payment-info.compact .payment-info-item { padding: 6px 0; }

        .payment-info-label {
            font-weight: 600;
            color: #6c757d;
            min-width: 100px;
            max-width: 100px;
            font-size: 0.92rem;
            flex-shrink: 0;
        }

        .payment-info-value {
            font-weight: 500;
            color: #212529;
            text-align: right;
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }

        .payment-info-item .d-flex {
            flex: 1;
            min-width: 0;
            justify-content: flex-end;
            gap: 8px;
        }

        .payment-info-item .d-flex .payment-info-value {
            min-width: 0;
            text-align: right;
            flex: 1;
            max-width: 100%;
        }
    </style>
@endpush

@section('info_content')

    <div class="deposit-tabs d-flex mb-4">
        <a href="{{ route('user.bank.auto.deposit') }}" class="deposit-tab active">
            <i class="fas fa-robot me-2"></i>Bank auto
        </a>
        <a href="{{ route('user.deposit') }}" class="deposit-tab">
            <i class="fas fa-university me-2"></i>Bank
        </a>
        <a href="{{ route('user.card.deposit') }}" class="deposit-tab">
            <i class="fas fa-credit-card me-2"></i>Card
        </a>
        <a href="{{ route('user.paypal.deposit') }}" class="deposit-tab">
            <i class="fab fa-paypal me-2"></i>PayPal
        </a>
    </div>

    <div class="deposit-container" id="depositContainer">
        <div class="row">
            <div class="col-lg-8">
                <!-- Bank Auto Info Section -->
                <div class="card-info-section mb-3">
                    <div class="deposit-card-header">
                        <P class="mb-0"> Vui lòng liên hệ về <a class="text-danger fw-bold text-decoration-none"
                                href="https://www.facebook.com/profile.php?id=100094042439181" target="_blank">fanpage</a>
                            để được hỗ trợ nếu có vấn đề về nạp xu</P>
                    </div>
                </div>

                <!-- Bank Auto Form -->
                <div id="depositContainer">
                    <div class="card-body">
                        <form id="bankAutoDepositForm">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-bold mb-3">
                                    <i class="fas fa-university me-2"></i>Chuyển khoản đến ngân hàng
                                </label>
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
                                <label for="amount" class="form-label fw-bold mb-3">
                                    <i class="fas fa-money-bill-wave me-2"></i>Nhập số tiền muốn nạp (VNĐ)
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control deposit-amount-input" id="amount"
                                        name="amount" value="{{ old('amount', '50.000') }}"
                                        data-raw="{{ old('amount', $minBankAutoDepositAmount) }}" placeholder="Nhập số tiền (ví dụ: 100.000)"
                                        pattern="[0-9.,]+" inputmode="numeric">

                                    <span class="input-group-text">VNĐ</span>
                                </div>
                                <div class="form-text">Số tiền tối thiểu: {{ number_format($minBankAutoDepositAmount) }} VNĐ , phải là bội số của 10.000</div>
                                <div class="invalid-feedback amount-error">Vui lòng nhập số tiền hợp lệ</div>

                                <!-- Coin Preview after fee only -->
                                <div class="deposit-coin-preview mt-4">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="small text-white opacity-75">Xu nhận được:</div>
                                            <div class="coin-preview-value">
                                                <i class="fas fa-coins me-2"></i>
                                                <span id="totalCoinsPreview">0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" id="proceedToPaymentBtn" class="btn payment-btn w-100">
                                    <i class="fas fa-robot"></i> Thanh toán tự động
                                </button>
                            </div>
                        </form>
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
                        <p class="mb-2"><i class="fas fa-chevron-right me-2"></i> Nạp xu tự động nhận xu ngay sau khi thanh toán</p>
                        <p class="mb-0"><i class="fas fa-chevron-right me-2"></i> Dùng xu để đọc truyện trả phí</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade payment-modal" id="paymentModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle text-success me-2"></i>Tạo giao dịch thành công!
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="payment-qr-code mb-3" id="qrCodeContainer">
                            <img src="" alt="QR Code" id="bankQrCode" class="img-fluid d-none" style="max-height: 300px;">
                            <div class="d-flex align-items-center justify-content-center h-100" id="qrCodePlaceholder">
                                <div class="text-center text-muted">
                                    <i class="fas fa-qrcode fa-3x mb-2"></i>
                                    <p>QR code không khả dụng</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted">Quét mã QR để thực hiện thanh toán</p>
                    </div>

                    <div class="payment-info compact">
                        <div class="row g-3">
                            <div class="col-lg-5">
                                <div class="payment-info-item">
                                    <span class="payment-info-label">Xu nhận được</span>
                                    <span class="payment-info-value text-info fw-bold" id="paymentCoins" title=""></span>
                                </div>
                                <div class="payment-info-item">
                                    <span class="payment-info-label">Ngân hàng</span>
                                    <span class="payment-info-value" id="bankName" title=""></span>
                                </div>
                                <div class="payment-info-item">
                                    <span class="payment-info-label">Chủ tài khoản</span>
                                    <span class="payment-info-value" id="bankAccountName" title=""></span>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="payment-info-item">
                                    <span class="payment-info-label">Số tài khoản</span>
                                    <div class="d-flex align-items-center justify-content-end flex-1" style="min-width: 0;">
                                        <span class="payment-info-value text-truncate" id="bankAccountNumber" tabindex="0" onclick="this.focus();this.select()" onfocus="this.select()" title=""></span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary copy-button flex-shrink-0" onclick="copyToClipboard('#bankAccountNumber')" title="Sao chép số tài khoản">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="payment-info-item">
                                    <span class="payment-info-label">Số tiền</span>
                                    <div class="d-flex align-items-center justify-content-end flex-1" style="min-width: 0;">
                                        <span class="payment-info-value text-truncate" id="paymentAmount" tabindex="0" onclick="this.focus();this.select()" onfocus="this.select()" title=""></span>
                                        <span class="fw-bold flex-shrink-0">VNĐ</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary copy-button flex-shrink-0" onclick="copyToClipboard('#paymentAmount')" title="Sao chép số tiền">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="payment-info-item">
                                    <span class="payment-info-label">Nội dung</span>
                                    <div class="d-flex align-items-center justify-content-end flex-1" style="min-width: 0;">
                                        <span class="payment-info-value text-truncate" id="transactionCode" tabindex="0" onclick="this.focus();this.select()" onfocus="this.select()" title=""></span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary copy-button flex-shrink-0" onclick="copyToClipboard('#transactionCode')" title="Sao chép nội dung chuyển khoản">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Lưu ý quan trọng:</h6>
                        <ul class="mb-0">
                            <li>Nội dung chuyển khoản phải chính xác</li>
                            <li>Số tiền chuyển khoản phải đúng với số tiền hiển thị</li>
                            <li>Sau khi chuyển khoản, hệ thống sẽ tự động cộng xu trong vòng 1-5 phút</li>
                            <li>Nếu không nhận được xu sau 10 phút, vui lòng liên hệ hỗ trợ</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-plus me-2"></i>Tạo giao dịch mới
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@once
    @push('info_scripts')
        <script>
            $(document).ready(function() {
                const firstBank = $('.bank-option').first();
                if (firstBank.length > 0) {
                    firstBank.addClass('selected');
                    $('#bankId').val(firstBank.data('bank-id'));
                    $('.bank-error').hide();
                }

                $(document).on('click', '.bank-option', function() {
                    $('.bank-option').removeClass('selected');
                    $(this).addClass('selected');
                    $('#bankId').val($(this).data('bank-id'));
                    $('.bank-error').hide();

                    $(this).animate({
                        opacity: 0.7
                    }, 100).animate({
                        opacity: 1
                    }, 100);
                });

                $('.deposit-amount-input').on('input', function() {
                    try {
                        const input = $(this);
                        const currentValue = input.val();

                        if (currentValue && currentValue.trim() !== '') {
                            const cleanValue = currentValue.replace(/[^\d.]/g, '');

                            // Format with dots
                            const formatted = formatVndCurrency(cleanValue);

                            if (formatted !== currentValue) {
                                const cursorPos = input.prop('selectionStart');
                                input.val(formatted);

                                setTimeout(() => {
                                    const newLength = formatted.length;
                                    const newPos = Math.min(cursorPos + (formatted.length - currentValue
                                        .length), newLength);
                                    input.prop('selectionStart', newPos);
                                    input.prop('selectionEnd', newPos);
                                }, 0);
                            }

                            const rawValue = parseVndCurrency(formatted);
                            input.data('raw', rawValue);
                            updateCoinPreview();
                        } else {
                            input.data('raw', 0);
                            updateCoinPreview();
                        }
                    } catch (error) {
                        console.error('Error in input handler:', error);
                        input.data('raw', 0);
                        updateCoinPreview();
                    }
                });

                $('.deposit-amount-input').on('blur', function() {
                    try {
                        const input = $(this);
                        let rawValue = input.data('raw') || 0;
                        const originalRaw = rawValue;
                        const minAmount = {{ (int) $minBankAutoDepositAmount }};

                        if (rawValue > 0) {
                            // Round to nearest 10,000
                            rawValue = Math.round(rawValue / 10000) * 10000;
                            if (rawValue < minAmount) rawValue = minAmount;

                            const formatted = formatVndCurrency(rawValue.toString());
                            input.val(formatted);
                            input.data('raw', rawValue);
                            updateCoinPreview();

                            // Toast if auto adjusted
                            if (originalRaw !== rawValue && window.showToast) {
                                const adjustedText = formatVndCurrency(rawValue.toString());
                                window.showToast(`Số tiền đã được tự động điều chỉnh: ${adjustedText} VNĐ (bội số 10.000, tối thiểu ${formatVndCurrency(minAmount.toString())})`, 'info');
                            }
                        } else {
                            input.val(formatVndCurrency(minAmount.toString()));
                            input.data('raw', minAmount);
                            updateCoinPreview();
                        }
                    } catch (error) {
                        console.error('Error in blur handler:', error);
                    }
                });

                function updateCoinPreview() {
                    try {
                        const amount = parseInt($('#amount').data('raw')) || 0;

                        const minAmount = {{ (int) $minBankAutoDepositAmount }};
                        if (amount > 0 && amount >= minAmount && amount % 10000 === 0) {
                            $.ajax({
                                url: '{{ route('user.bank.auto.deposit.calculate') }}',
                                type: 'POST',
                                data: {
                                    amount: amount,
                                    _token: $('input[name="_token"]').val()
                                },
                                dataType: 'json',
                                success: function(response) {
                                    if (response.success) {
                                        const data = response.data;
                                        $('#totalCoinsPreview').text((data.coins || 0).toLocaleString('vi-VN'));
                                    } else {
                                        $('#totalCoinsPreview').text('-');
                                    }
                                },
                                error: function() {
                                    $('#totalCoinsPreview').text('-');
                                }
                            });
                        } else {
                            $('#totalCoinsPreview').text('-');
                        }
                    } catch (error) {
                        console.error("Error updating coin preview:", error);
                        $('#totalCoinsPreview').text('-');
                    }
                }

                updateCoinPreview();

                $('#proceedToPaymentBtn').off('click').on('click', function() {
                    let valid = true;

                    if (!$('#bankId').val()) {
                        $('.bank-error').show();
                        valid = false;
                    } else {
                        $('.bank-error').hide();
                    }

                    const amount = parseInt($('#amount').data('raw')) || 0;

                    // Debug logging
                    console.log('Validation check:', {
                        amount: amount,
                        bankId: $('#bankId').val(),
                        amountRaw: $('#amount').data('raw'),
                        amountMod10000: amount % 10000
                    });

                    const minAmount = {{ (int) $minBankAutoDepositAmount }};
                    if (amount < minAmount) {
                        $('.amount-error').show().text('Số tiền tối thiểu là ' + minAmount.toLocaleString('vi-VN') + ' VNĐ');
                        valid = false;
                    } else if (amount % 10000 !== 0) {
                        $('.amount-error').show().text('Số tiền phải là bội số của 10.000 VNĐ (ví dụ: 50.000, 60.000, 70.000...)');
                        valid = false;
                    } else if (amount > 99999999) {
                        $('.amount-error').show().text('Số tiền tối đa là 99.999.999 VNĐ');
                        valid = false;
                    } else {
                        $('.amount-error').hide();
                    }

                    if (valid) {
                        const bankId = $('#bankId').val();

                        $(this).prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');

                        $.ajax({
                            url: '{{ route('user.bank.auto.deposit.store') }}',
                            type: 'POST',
                            data: {
                                bank_id: bankId,
                                amount: amount,
                                _token: $('input[name="_token"]').val()
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    showBankTransferInfo(response);
                                } else {
                                    showToast('Có lỗi xảy ra: ' + (response.message ||
                                        'Không thể xử lý thanh toán'), 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX Error Details:', {
                                    status: xhr.status,
                                    statusText: xhr.statusText,
                                    responseText: xhr.responseText,
                                    responseJSON: xhr.responseJSON,
                                    error: error
                                });

                                let errorMessage = 'Đã xảy ra lỗi khi xử lý yêu cầu';

                                if (xhr.responseJSON) {
                                    if (xhr.responseJSON.errors) {
                                        const errors = xhr.responseJSON.errors;
                                        const firstError = Object.values(errors)[0];
                                        errorMessage = firstError[0] || errorMessage;
                                    } else if (xhr.responseJSON.message) {
                                        errorMessage = xhr.responseJSON.message;
                                    }
                                }

                                showToast(errorMessage, 'error');
                            },
                            complete: function() {
                                $('#proceedToPaymentBtn').prop('disabled', false).html(
                                    '<i class="fas fa-robot"></i> Thanh toán tự động với Casso');
                            }
                        });
                    }
                });

                function formatVndCurrency(value) {
                    try {
                        if (!value || value === '' || value === null || value === undefined) return '';
                        const number = value.toString().replace(/\D/g, '');
                        if (number === '' || number === '0') return '';
                        return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    } catch (error) {
                        console.error('Error in formatVndCurrency:', error);
                        return '';
                    }
                }

                function parseVndCurrency(formatted) {
                    try {
                        if (!formatted || formatted === '' || formatted === null || formatted === undefined) return 0;
                        return parseInt(formatted.toString().replace(/\./g, '')) || 0;
                    } catch (error) {
                        console.error('Error in parseVndCurrency:', error);
                        return 0;
                    }
                }

                $('.deposit-amount-input').each(function() {
                    const input = $(this);
                    let raw = input.data('raw');
                    if (raw) {
                        raw = Math.round(raw / 10000) * 10000;
                        const minAmount = {{ (int) $minBankAutoDepositAmount }};
                        if (raw < minAmount) raw = minAmount;
                        input.data('raw', raw);
                        input.val(formatVndCurrency(raw));
                    }
                });

                updateCoinPreview();
            });

            function showBankTransferInfo(response) {
                const bankInfo = response.bank_info;
                const transactionCode = response.transaction_code;
                const amount = response.amount;
                const coins = response.coins;

                // Populate modal
                $('#bankName').text(bankInfo.name + (bankInfo.code ? ' (' + bankInfo.code + ')' : ''));
                $('#bankAccountNumber').text(bankInfo.account_number);
                $('#bankAccountName').text(bankInfo.account_name);
                $('#paymentAmount').text(amount.toLocaleString('vi-VN'));
                $('#transactionCode').text(transactionCode);
                $('#paymentCoins').text(coins.toLocaleString('vi-VN') + ' xu');

                // Display QR code if available
                if (bankInfo.qr_code) {
                    $('#bankQrCode').attr('src', bankInfo.qr_code).removeClass('d-none');
                    $('#qrCodePlaceholder').addClass('d-none');
                } else {
                    $('#bankQrCode').addClass('d-none');
                    $('#qrCodePlaceholder').removeClass('d-none');
                }

                // Show modal
                var paymentModalEl = document.getElementById('paymentModal');
                var paymentModal = new bootstrap.Modal(paymentModalEl);
                paymentModal.show();

                startSSEConnection(transactionCode);
            }

            function copyToClipboard(selector) {
                const $button = event.target.closest('.copy-button');
                const originalText = $button.innerHTML;
                
                // Get text from selector (element ID)
                let textToCopy = '';
                if (typeof selector === 'string' && selector.startsWith('#')) {
                    textToCopy = $(selector).text().trim();
                } else {
                    textToCopy = selector;
                }

                $button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(textToCopy)
                        .then(() => {
                            showCopySuccess($button, originalText);
                        })
                        .catch(() => {
                            copyUsingExecCommand(textToCopy, $button, originalText);
                        });
                } else {
                    copyUsingExecCommand(textToCopy, $button, originalText);
                }
            }

            function copyUsingExecCommand(text, $button, originalText) {
                try {
                    const $temp = $("<input>");
                    $("body").append($temp);
                    $temp.val(text).select();

                    const successful = document.execCommand('copy');

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

            function showCopySuccess($button, originalText) {
                $button.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => $button.innerHTML = originalText, 1000);
            }

            function showCopyFailure($button, originalText) {
                $button.innerHTML = '<i class="fas fa-times"></i>';

                setTimeout(() => $button.innerHTML = originalText, 1000);
            }

            let currentTransactionCode = null;
            let sseConnection = null;

            function startSSEConnection(transactionCode) {
                if (sseConnection) {
                    sseConnection.close();
                }

                currentTransactionCode = transactionCode;
                const sseUrl = '{{ route('user.bank.auto.sse') }}?transaction_code=' + encodeURIComponent(transactionCode);

                sseConnection = new EventSource(sseUrl);

                sseConnection.onmessage = function(event) {
                    try {
                        const data = JSON.parse(event.data);

                        if (data.type === 'close') {
                            sseConnection.close();
                            return;
                        }

                        if (data.status === 'success') {
                            showSuccessNotification(data);
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);

                            sseConnection.close();
                        }
                    } catch (error) {
                        console.error('SSE parsing error:', error);
                    }
                };

                sseConnection.onerror = function(event) {
                    console.error('SSE connection error:', event);
                    setTimeout(() => {
                        if (currentTransactionCode) {
                            startSSEConnection(currentTransactionCode);
                        }
                    }, 5000);
                };
            }

            function showSuccessNotification(data) {
                const toast = `
                    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas fa-check-circle me-2"></i>
                                Giao dịch thành công! Bạn đã nhận được ${data.total_coins ? data.total_coins.toLocaleString('vi-VN') : 'xu'} xu.
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `;

                if (!$('#toast-container').length) {
                    $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
                }

                $('#toast-container').append(toast);

                const toastElement = $('#toast-container .toast').last();
                const toastInstance = new bootstrap.Toast(toastElement[0]);
                toastInstance.show();
            }
        </script>
    @endpush
@endonce
