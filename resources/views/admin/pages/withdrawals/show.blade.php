@extends('admin.layouts.app')

@section('title', 'Chi tiết yêu cầu rút xu')

@section('content-auth')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Chi tiết yêu cầu rút xu #{{ $withdrawal->id }}</h6>
                            <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Thông tin yêu cầu</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <strong>Trạng thái:</strong>
                                            @if ($withdrawal->status == 'pending')
                                                <span class="badge bg-warning">Đang xử lý</span>
                                            @elseif($withdrawal->status == 'approved')
                                                <span class="badge bg-success">Đã duyệt</span>
                                            @elseif($withdrawal->status == 'rejected')
                                                <span class="badge bg-danger">Đã từ chối</span>
                                            @endif
                                        </div>

                                        <div class="mb-3">
                                            <strong>Ngày yêu cầu:</strong>
                                            <p>{{ $withdrawal->created_at->format('d/m/Y H:i:s') }}</p>
                                        </div>

                                        @if ($withdrawal->processed_at)
                                            <div class="mb-3">
                                                <strong>Ngày xử lý:</strong>
                                                <p>{{ $withdrawal->processed_at->format('d/m/Y H:i:s') }}</p>
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <strong>Số xu rút:</strong>
                                            <p>{{ number_format($withdrawal->coins) }} xu</p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Phí rút xu:</strong>
                                            <p>{{ number_format($withdrawal->fee) }} xu</p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Xu thực nhận:</strong>
                                            <p>{{ number_format($withdrawal->net_amount) }} xu</p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Tỷ giá quy đổi:</strong>
                                            <p>1 xu =
                                                {{ number_format($withdrawal->payment_info['exchange_rate'] ?? $coinExchangeRate) }} VND
                                            </p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Số tiền quy đổi:</strong>
                                            <p>{{ number_format($withdrawal->payment_info['vnd_amount'] ?? $withdrawal->net_amount * $coinExchangeRate) }}
                                                VND</p>
                                        </div>

                                       
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Thông tin người rút</h6>
                                    </div>
                                    <div class="card-body">
                                        <a href="{{ route('users.show', $withdrawal->user->id) }}" target="_blank" class="d-flex align-items-center mb-3">
                                            <img src="{{ $withdrawal->user->avatar ? Storage::url($withdrawal->user->avatar) : asset('assets/images/avatar_default.jpg') }}"
                                                class="avatar avatar-xl me-3" alt="user avatar">
                                            <div>
                                                <h6 class="mb-0">{{ $withdrawal->user->name }}</h6>
                                                <p class="text-sm text-secondary mb-0">{{ $withdrawal->user->email }}</p>
                                                <p class="text-sm text-secondary mb-0">Số dư:
                                                    {{ number_format($withdrawal->user->coins) }} xu</p>
                                            </div>
                                        </a>


                                        <div class="mb-3">
                                            <strong>Tên chủ tài khoản:</strong>
                                            <p>{{ $withdrawal->payment_info['account_name'] ?? 'N/A' }}</p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Số tài khoản:</strong>
                                            <p>{{ $withdrawal->payment_info['account_number'] ?? 'N/A' }}</p>
                                        </div>


                                        <div class="mb-3">
                                            <strong>Tên ngân hàng:</strong>
                                            <p>{{ $withdrawal->payment_info['bank_name'] ?? 'N/A' }}</p>
                                        </div>


                                        @if (!empty($withdrawal->payment_info['additional_info']))
                                            <div class="mb-3">
                                                <strong>Thông tin bổ sung:</strong>
                                                <p>{{ $withdrawal->payment_info['additional_info'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($withdrawal->status == 'rejected')
                            <div class="card mt-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Lý do từ chối</h6>
                                </div>
                                <div class="card-body">
                                    <p>{{ $withdrawal->rejection_reason }}</p>
                                </div>
                            </div>
                        @endif

                        @if ($withdrawal->status == 'pending')
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">Phê duyệt yêu cầu</h6>
                                        </div>
                                        <div class="card-body">
                                            <p>Nhấn nút bên dưới để phê duyệt yêu cầu rút xu này.</p>
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                                                <i class="fas fa-check me-2"></i>Phê duyệt
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-danger text-white">
                                            <h6 class="mb-0">Từ chối yêu cầu</h6>
                                        </div>
                                        <div class="card-body">
                                            <p>Nhấn nút bên dưới để từ chối yêu cầu rút xu này.</p>
                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                                <i class="fas fa-times me-2"></i>Từ chối
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Xác nhận phê duyệt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn phê duyệt yêu cầu rút xu này?</p>
                    <p><strong>Thông tin rút xu:</strong></p>
                    <ul>
                        <li>Người rút: {{ $withdrawal->user->name }}</li>
                        <li>Số xu: {{ number_format($withdrawal->coins) }} xu</li>
                        <li>Thực nhận: {{ number_format($withdrawal->net_amount) }} xu</li>
                        <li>Quy đổi: {{ number_format($withdrawal->payment_info['vnd_amount'] ?? $withdrawal->net_amount * $coinExchangeRate) }} VND</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <form action="{{ route('admin.withdrawals.approve', $withdrawal->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Xác nhận phê duyệt
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Từ chối yêu cầu rút xu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.withdrawals.reject', $withdrawal->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Vui lòng nhập lý do từ chối yêu cầu rút xu này.</p>
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-2"></i>Xác nhận từ chối
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
