@extends('layouts.information')

@section('info_title', 'Lịch sử rút xu')
@section('info_section_title', 'Lịch sử rút xu')
@section('info_description', 'Xem lịch sử rút xu và tạo yêu cầu rút xu mới')
@section('info_section_desc', 'Quản lý các yêu cầu rút xu của bạn')

@section('info_content')
    <div class="box-shadow-custom rounded-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Yêu cầu rút xu của bạn</h5>
            <a href="{{ route('user.withdrawals.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-2"></i>Tạo yêu cầu mới
            </a>
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

        @if($withdrawalRequests->isEmpty())
            <div class="text-center p-5">
                <i class="fa-solid fa-money-bill-transfer fa-3x text-muted mb-3"></i>
                <p class="mb-0">Bạn chưa có yêu cầu rút xu nào</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ngày yêu cầu</th>
                            <th>Số xu rút</th>
                            <th>Phí</th>
                            <th>Thực nhận</th>
                            <th>Trạng thái</th>
                            <th>Ngày xử lý</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($withdrawalRequests as $request)
                            <tr>
                                <td>{{ $request->id }}</td>
                                <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ number_format($request->coins) }} xu</td>
                                <td>- {{ number_format($request->fee) }} xu</td>
                                <td>{{ number_format($request->net_amount) }} xu
                                    <br>
                                    {{ number_format($request->payment_info['vnd_amount']) }} VND
                                </td>
                                <td>
                                    @if($request->status == 'pending')
                                        <span class="badge bg-warning">Đang xử lý</span>
                                    @elseif($request->status == 'approved')
                                        <span class="badge bg-success">Đã duyệt</span>
                                    @elseif($request->status == 'rejected')
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $request->rejection_reason }}">
                                            Đã từ chối
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $request->processed_at ? $request->processed_at->format('d/m/Y H:i') : 'Chưa xử lý' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal{{ $request->id }}">
                                        <i class="fa-solid fa-eye"></i> Chi tiết
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $withdrawalRequests->links() }}
            </div>
        @endif
    </div>

    <!-- Detail Modals -->
    @foreach($withdrawalRequests as $request)
        <div class="modal fade" id="detailModal{{ $request->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $request->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailModalLabel{{ $request->id }}">Chi tiết yêu cầu rút xu #{{ $request->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Thông tin yêu cầu</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <strong>Trạng thái:</strong>
                                            @if($request->status == 'pending')
                                                <span class="badge bg-warning">Đang xử lý</span>
                                            @elseif($request->status == 'approved')
                                                <span class="badge bg-success">Đã duyệt</span>
                                            @elseif($request->status == 'rejected')
                                                <span class="badge bg-danger">Đã từ chối</span>
                                            @endif
                                        </div>
                                        <div class="mb-3">
                                            <strong>Ngày yêu cầu:</strong>
                                            <p>{{ $request->created_at->format('d/m/Y H:i:s') }}</p>
                                        </div>
                                        @if($request->processed_at)
                                            <div class="mb-3">
                                                <strong>Ngày xử lý:</strong>
                                                <p>{{ $request->processed_at->format('d/m/Y H:i:s') }}</p>
                                            </div>
                                        @endif
                                        <div class="mb-3">
                                            <strong>Số xu rút:</strong>
                                            <p>{{ number_format($request->coins) }} xu</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Phí rút xu:</strong>
                                            <p>{{ number_format($request->fee) }} xu</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Xu thực nhận:</strong>
                                            <p>{{ number_format($request->net_amount) }} xu</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Tỷ giá quy đổi:</strong>
                                            <p>1 xu = {{ number_format($request->payment_info['exchange_rate'] ?? 100) }} VND</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Số tiền quy đổi:</strong>
                                            <p>{{ number_format($request->payment_info['vnd_amount']) }} VND</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Thông tin thanh toán</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <strong>Tên chủ tài khoản:</strong>
                                            <p>{{ $request->payment_info['account_name'] ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Số tài khoản:</strong>
                                            <p>{{ $request->payment_info['account_number'] ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Tên ngân hàng:</strong>
                                            <p>{{ $request->payment_info['bank_name'] ?? 'N/A' }}</p>
                                        </div>
                                        @if(!empty($request->payment_info['additional_info']))
                                            <div class="mb-3">
                                                <strong>Thông tin bổ sung:</strong>
                                                <p>{{ $request->payment_info['additional_info'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($request->status == 'rejected' && !empty($request->rejection_reason))
                                    <div class="card mt-3">
                                        <div class="card-header bg-danger text-white">
                                            <h6 class="mb-0">Lý do từ chối</h6>
                                        </div>
                                        <div class="card-body">
                                            <p>{{ $request->rejection_reason }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('info_scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    });
</script>
@endpush 