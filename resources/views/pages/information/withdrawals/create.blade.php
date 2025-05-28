@extends('layouts.information')

@section('info_title', 'Tạo yêu cầu rút xu')
@section('info_section_title', 'Tạo yêu cầu rút xu')
@section('info_description', 'Tạo yêu cầu rút xu mới')
@section('info_section_desc', 'Nhập thông tin để yêu cầu rút xu')

@section('info_content')
    <div class="box-shadow-custom rounded-4 p-4">
        <div class="mb-4">
            <div class="alert alert-info">
                <div class="d-flex">
                    <i class="fa-solid fa-circle-info fa-lg me-3 mt-1"></i>
                    <div>
                        <p class="mb-1"><strong>Quy định rút xu:</strong></p>
                        <ul class="mb-0 ps-3">
                            <li>Số xu rút tối thiểu: {{ number_format($minWithdrawalAmount) }} xu</li>
                            {{-- <li>Tỷ giá quy đổi: 1 xu = {{ number_format($coinExchangeRate) }} VND</li> --}}
                            <li>Phí rút xu: {{ $feePercentage }}% (đối với số xu dưới {{ number_format($feeThresholdAmount) }})</li>
                            <li>Rút từ {{ number_format($feeThresholdAmount) }} xu trở lên: Miễn phí rút xu</li>
                            <li>Thời gian xử lý: 1-3 ngày làm việc</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-balance mb-4">
            <div class="d-flex align-items-center">
                <div class="card-balance-icon me-3">
                    <i class="fa-solid fa-coins fa-2x text-warning"></i>
                </div>
                <div>
                    <h6 class="mb-0">Số dư xu hiện tại</h6>
                    <h3 class="mb-0">{{ number_format(Auth::user()->coins) }} xu</h3>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('user.withdrawals.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="coins" class="form-label">Số xu cần rút <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="coins" name="coins" min="{{ $minWithdrawalAmount }}" max="{{ Auth::user()->coins }}" value="{{ old('coins') }}" required>
                <div class="form-text">Số xu tối thiểu cần rút: {{ number_format($minWithdrawalAmount) }} xu</div>
                @error('coins')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="account_name" class="form-label">Tên chủ tài khoản <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="account_name" name="account_name" value="{{ old('account_name') }}" required>
                @error('account_name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="account_number" class="form-label">Số tài khoản<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="account_number" name="account_number" value="{{ old('account_number') }}" required>
                @error('account_number')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div id="bank_info"">
                <div class="mb-3">
                    <label for="bank_name" class="form-label">Tên ngân hàng <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name') }}">
                </div>
                @error('bank_name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="additional_info" class="form-label">Thông tin thêm</label>
                <textarea class="form-control" id="additional_info" name="additional_info" rows="3">{{ old('additional_info') }}</textarea>
                @error('additional_info')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="withdrawal-summary p-3 bg-light rounded mb-4 d-none" id="summary">
                <h6 class="border-bottom pb-2 mb-3">Tóm tắt yêu cầu rút xu</h6>
                <div class="row">
                    <div class="col-6">Số xu rút:</div>
                    <div class="col-6 text-end" id="summary_amount">0</div>
                </div>
                <div class="row mt-2">
                    <div class="col-6">Phí rút xu (<span id="fee_percentage">{{ $feePercentage }}</span>%):</div>
                    <div class="col-6 text-end" id="summary_fee">0</div>
                </div>
                <div class="row mt-2">
                    <div class="col-6">Quy đổi sang VND:</div>
                    <div class="col-6 text-end" id="summary_vnd">0 VND</div>
                </div>
              
                <div class="row mt-2 border-top pt-2">
                    <div class="col-6"><strong>Thực nhận:</strong></div>
                    <div class="col-6 text-end"><strong id="summary_net_vnd">0 VND</strong></div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('user.withdrawals.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
                </a>
                <button type="submit" class="btn bg-3 text-white">
                    <i class="fa-solid fa-paper-plane me-2"></i>Gửi yêu cầu
                </button>
            </div>
        </form>
    </div>
@endsection

@push('info_scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const coinsInput = document.getElementById('coins');
        const bankInfoDiv = document.getElementById('bank_info');
        const bankNameInput = document.getElementById('bank_name');
        const summaryDiv = document.getElementById('summary');
        const summaryAmount = document.getElementById('summary_amount');
        const summaryFee = document.getElementById('summary_fee');
        const summaryVnd = document.getElementById('summary_vnd');
        const bankFeeRow = document.getElementById('bank_fee_row');
        const summaryNetVnd = document.getElementById('summary_net_vnd');
        const feePercentage = parseFloat(document.getElementById('fee_percentage').textContent);
        const feeThreshold = {{ $feeThresholdAmount }};
        const exchangeRate = {{ $coinExchangeRate }};
        
        // Calculate and display withdrawal summary
        coinsInput.addEventListener('input', updateSummary);
        
        function updateSummary() {
            const coins = parseInt(coinsInput.value) || 0;
            let fee = 0;
            
            if (coins > 0) {
                // Calculate withdrawal fee
                if (coins < feeThreshold) {
                    fee = Math.round((coins * feePercentage) / 100);
                }
                
                // Calculate net coin amount
                const netCoinAmount = coins - fee;
                
                // Convert to VND
                const vndAmount = netCoinAmount * exchangeRate;
                
                // Calculate bank fee if applicable
                let bankFee = 0;
                let netVndAmount = vndAmount;
                
                // Update summary display
                summaryAmount.textContent = coins.toLocaleString('vi-VN') + ' xu';
                summaryFee.textContent = '-' + fee.toLocaleString('vi-VN') + ' xu';
                summaryVnd.textContent = vndAmount.toLocaleString('vi-VN') + ' VND';
                summaryNetVnd.textContent = netVndAmount.toLocaleString('vi-VN') + ' VND';
                
                summaryDiv.classList.remove('d-none');
            } else {
                summaryDiv.classList.add('d-none');
            }
        }

        updateSummary();
    });
</script>
@endpush 