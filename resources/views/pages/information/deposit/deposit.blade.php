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
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Tạo yêu cầu nạp xu</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.deposit.store') }}" method="POST" enctype="multipart/form-data" id="depositForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Chọn ngân hàng</label>
                            <div class="row">
                                @foreach($banks as $bank)
                                <div class="col-md-6 col-lg-4">
                                    <div class="bank-card" data-bank-id="{{ $bank->id }}" onclick="selectBank(this, {{ $bank->id }})">
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
                                                <div class="bank-info">{{ $bank->code }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="bank_id" id="bankId" required>
                            @error('bank_id')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4" id="bankDetails" style="display: none;">
                            <div class="alert alert-info">
                                <h6 class="mb-2">Thông tin chuyển khoản:</h6>
                                <p class="mb-1"><strong>Số tài khoản:</strong> <span id="bankAccountNumber"></span></p>
                                <p class="mb-1"><strong>Chủ tài khoản:</strong> <span id="bankAccountName"></span></p>
                                <p class="mb-0"><strong>Ngân hàng:</strong> <span id="bankName"></span></p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="amount" class="form-label">Số tiền (VNĐ) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                    id="amount" name="amount" value="{{ old('amount') }}" min="10000" step="10000" required>
                                <span class="input-group-text">VNĐ</span>
                            </div>
                            <div class="form-text">Số xu nhận được: <span id="coinsPreview">0</span> xu</div>
                            @error('amount')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="transaction_image" class="form-label">Hình ảnh chuyển khoản <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('transaction_image') is-invalid @enderror" 
                                id="transaction_image" name="transaction_image" accept="image/*" required>
                            @error('transaction_image')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            
                            <div class="mt-3" id="previewContainer" style="display: none;">
                                <img src="" id="imagePreview" class="deposit-preview">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Hướng dẫn:</label>
                            <ol class="ps-3">
                                <li>Chọn ngân hàng và chuyển khoản theo thông tin hiển thị</li>
                                <li>Nhập chính xác số tiền đã chuyển</li>
                                <li>Tải lên ảnh chụp màn hình/biên lai chuyển khoản</li>
                                <li>Gửi yêu cầu và chờ phê duyệt (thường trong vòng 24 giờ)</li>
                            </ol>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Gửi yêu cầu nạp xu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Xu của bạn</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-5 fw-bold text-warning mb-2">
                        <i class="fas fa-coins me-2"></i> {{ number_format(Auth::user()->coins) }}
                    </div>
                    <p class="text-muted">Số xu hiện có trong tài khoản</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Lịch sử giao dịch</h5>
        </div>
        <div class="card-body">
            @if($deposits->count() > 0)
                @foreach($deposits as $deposit)
                    <div class="transaction-item">
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="position-relative">
                                    @if($deposit->image)
                                        <a href="{{ Storage::url($deposit->image) }}" target="_blank">
                                            <img src="{{ Storage::url($deposit->image) }}" alt="Biên lai" class="transaction-image">
                                        </a>
                                    @else
                                        <div class="transaction-image d-flex align-items-center justify-content-center bg-light">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex justify-content-between">
                                    <h5 class="mb-2">Mã giao dịch: {{ $deposit->transaction_code }}</h5>
                                    <div>
                                        @if($deposit->status == 'pending')
                                            <span class="badge bg-warning">Đang xử lý</span>
                                        @elseif($deposit->status == 'approved')
                                            <span class="badge bg-success">Đã duyệt</span>
                                        @else
                                            <span class="badge bg-danger">Từ chối</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <div><strong>Ngân hàng:</strong> {{ $deposit->bank->name }} ({{ $deposit->bank->code }})</div>
                                    <div><strong>Số tiền:</strong> {{ number_format($deposit->amount) }} VNĐ</div>
                                    <div><strong>Xu:</strong> {{ number_format($deposit->coins) }}</div>
                                    <div><strong>Ngày tạo:</strong> {{ $deposit->created_at->format('d/m/Y H:i') }}</div>
                                    
                                    @if($deposit->approved_at)
                                        <div><strong>Ngày xử lý:</strong> {{ $deposit->approved_at->format('d/m/Y H:i') }}</div>
                                    @endif
                                    
                                    @if($deposit->status == 'rejected' && $deposit->note)
                                        <div class="mt-2 alert alert-danger py-2">
                                            <strong>Lý do từ chối:</strong> {{ $deposit->note }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <div class="d-flex justify-content-center mt-4">
                    {{ $deposits->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-exchange-alt fa-3x text-muted"></i>
                    </div>
                    <h5>Chưa có giao dịch nào</h5>
                    <p class="text-muted">Bạn chưa thực hiện giao dịch nạp xu nào</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('info_scripts')
<script>
    // Xử lý chọn ngân hàng
    function selectBank(element, bankId) {
        // Bỏ chọn tất cả các bank card
        $('.bank-card').removeClass('selected');
        // Chọn bank card được click
        $(element).addClass('selected');
        // Cập nhật giá trị bank_id
        $('#bankId').val(bankId);
        
        // Hiển thị thông tin ngân hàng
        const bankName = $(element).find('h6').text();
        const bankCode = $(element).find('.bank-info').text();
        
        // Lấy thông tin tài khoản (trong thực tế, bạn nên lấy từ dữ liệu của ngân hàng)
        // Đây là dữ liệu mẫu, bạn cần thay thế bằng dữ liệu thật từ server
        const accountNumber = "{{ $banks->first()->account_number ?? '123456789' }}";
        const accountName = "{{ $banks->first()->account_name ?? 'NGUYEN VAN A' }}";
        
        $('#bankName').text(bankName + ' (' + bankCode + ')');
        $('#bankAccountNumber').text(accountNumber);
        $('#bankAccountName').text(accountName);
        $('#bankDetails').show();
    }
    
    // Xử lý preview hình ảnh
    $('#transaction_image').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result);
                $('#previewContainer').show();
            }
            reader.readAsDataURL(file);
        } else {
            $('#previewContainer').hide();
        }
    });
    
    // Cập nhật preview số xu
    $('#amount').on('input', function() {
        const amount = parseInt($(this).val()) || 0;
        const coins = Math.floor(amount / 1000); // 1.000 VNĐ = 1 xu
        $('#coinsPreview').text(coins.toLocaleString('vi-VN'));
    });
    
    // Validation form
    $('#depositForm').on('submit', function(e) {
        const bankId = $('#bankId').val();
        if (!bankId) {
            e.preventDefault();
            alert('Vui lòng chọn ngân hàng để nạp xu');
            return false;
        }
        
        const amount = $('#amount').val();
        if (!amount || amount < 10000) {
            e.preventDefault();
            alert('Số tiền tối thiểu là 10.000 VNĐ');
            return false;
        }
        
        const image = $('#transaction_image').val();
        if (!image) {
            e.preventDefault();
            alert('Vui lòng tải lên hình ảnh chứng minh chuyển khoản');
            return false;
        }
        
        return true;
    });
</script>
@endpush