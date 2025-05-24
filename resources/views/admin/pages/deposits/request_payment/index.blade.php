@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-0 mx-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">Quản lý yêu cầu thanh toán</h5>
                            <p class="text-sm mb-0">
                                Danh sách các yêu cầu thanh toán đang chờ xử lý
                            </p>
                        </div>
                        <div>
                            <button class="btn bg-gradient-danger btn-sm" id="deleteExpiredBtn" data-url="{{ route('request.payments.delete-expired') }}">
                                <i class="fas fa-trash me-2"></i> Xóa yêu cầu hết hạn
                            </button>
                            <a href="{{ route('deposits.index') }}" class="btn bg-gradient-primary btn-sm ms-2">
                                <i class="fas fa-list me-2"></i> Quản lý nạp xu
                            </a>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <form method="GET" class="d-flex gap-2">
                            <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                <option value="">- Trạng thái -</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                            </select>

                            <input type="date" name="date" class="form-control form-control-sm" style="width: auto;" 
                                   value="{{ request('date') }}" onchange="this.form.submit()">

                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" name="search" 
                                       value="{{ request('search') }}" placeholder="Tìm kiếm...">
                                <button class="btn bg-gradient-primary btn-sm px-2 mb-0" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    @include('admin.pages.components.success-error')

                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        ID
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Người dùng
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Ngân hàng
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Mã giao dịch
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Số tiền
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Xu
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Trạng thái
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Hết hạn
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Ngày tạo
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requestPayments as $payment)
                                    <tr>
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0">{{ $payment->id }}</p>
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <div>
                                                    <img src="{{ $payment->user->avatar ? asset('storage/' . $payment->user->avatar) : asset('assets/img/default-avatar.png') }}" 
                                                         class="avatar avatar-sm me-2" alt="user image">
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-xs">{{ $payment->user->name }}</h6>
                                                    <p class="text-xs text-secondary mb-0">{{ $payment->user->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $payment->bank->name }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ $payment->bank->account_number }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $payment->transaction_code }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ number_format($payment->amount, 0, ',', '.') }} VNĐ</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ number_format($payment->total_coins, 0, ',', '.') }} xu</p>
                                        </td>
                                        <td>
                                            @if($payment->is_completed)
                                                <span class="badge badge-sm bg-gradient-success">
                                                    Đã hoàn thành
                                                </span>
                                                @if($payment->deposit)
                                                    <a href="{{ route('deposits.index', ['search' => $payment->deposit->transaction_code]) }}" class="badge badge-sm bg-gradient-info text-white">
                                                        Xem giao dịch
                                                    </a>
                                                @endif
                                            @elseif($payment->isExpired())
                                                <span class="badge badge-sm bg-gradient-danger">
                                                    Đã hết hạn
                                                </span>
                                            @else
                                                <span class="badge badge-sm bg-gradient-warning">
                                                    Chờ xử lý
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($payment->expired_at)
                                                <p class="text-xs font-weight-bold mb-0">
                                                    {{ $payment->expired_at->format('d/m/Y H:i') }}
                                                </p>
                                                <p class="text-xs mb-0">
                                                    @if(now()->greaterThan($payment->expired_at))
                                                        <span class="text-danger">Đã hết hạn</span>
                                                    @else
                                                        <span class="text-success">Còn hiệu lực</span>
                                                    @endif
                                                </p>
                                            @else
                                                <p class="text-xs text-secondary mb-0">-</p>
                                            @endif
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                {{ $payment->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">Không có yêu cầu thanh toán nào</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 pt-4">
                        {{ $requestPayments->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts-admin')
    <script>
        $(document).ready(function() {
            // Xử lý xóa các yêu cầu hết hạn
            $('#deleteExpiredBtn').click(function() {
                const url = $(this).data('url');
                
                if (confirm('Bạn có chắc chắn muốn xóa tất cả các yêu cầu thanh toán đã hết hạn?')) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                window.location.reload();
                            } else {
                                alert('Có lỗi xảy ra: ' + response.message);
                            }
                        },
                        error: function(xhr) {
                            alert('Đã xảy ra lỗi!');
                        }
                    });
                }
            });
        });
    </script>
@endpush
