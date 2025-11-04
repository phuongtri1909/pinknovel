@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-0 mx-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                        <div>
                            <h5 class="mb-0">Quản lý nạp xu</h5>
                            <p class="text-sm mb-0">
                                Quản lý các giao dịch nạp xu của người dùng
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('request.payments.index') }}" class="btn bg-gradient-primary btn-sm">
                                <i class="fas fa-clock me-2"></i><span class="d-none d-md-inline">Quản lý yêu cầu thanh toán</span><span class="d-md-none">Yêu cầu</span>
                            </a>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mt-3">
                        <form method="GET" class="d-flex flex-column flex-md-row gap-2 flex-fill" id="filterForm">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">- Trạng thái -</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                            </select>

                            <input type="date" name="date" class="form-control form-control-sm"
                                   value="{{ request('date') }}" onchange="this.form.submit()">

                            <div class="input-group input-group-sm flex-fill">
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
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">
                                        ID
                                    </th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder  ps-2">
                                        Người dùng
                                    </th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">
                                        Ngân hàng
                                    </th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">
                                        Mã giao dịch
                                    </th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">
                                        Số tiền
                                    </th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">
                                        Xu
                                    </th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">
                                        Trạng thái
                                    </th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">
                                        Người duyệt
                                    </th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">
                                        Ngày tạo
                                    </th>
                                    <th class="text-center text-uppercase  text-xxs font-weight-bolder ">
                                        Hành động
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposits as $deposit)
                                    <tr>
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0">{{ $deposit->id }}</p>
                                        </td>
                                        <td>
                                            <a href="{{ route('users.show', $deposit->user->id) }}" class="d-flex">
                                                <div>
                                                    <img src="{{ $deposit->user->avatar ? asset('storage/' . $deposit->user->avatar) : asset('assets/images/avatar_default.jpg') }}"
                                                         class="avatar avatar-sm me-2" alt="user image">
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-xs">{{ $deposit->user->name }}</h6>
                                                    <p class="text-xs  mb-0">{{ $deposit->user->email }}</p>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $deposit->bank->name }}</p>
                                            <p class="text-xs  mb-0">{{ $deposit->bank->account_number }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $deposit->transaction_code }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ number_format($deposit->amount, 0, ',', '.') }} VNĐ</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ number_format($deposit->coins, 0, ',', '.') }} xu</p>
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger'
                                                ][$deposit->status] ?? 'secondary';

                                                $statusText = [
                                                    'pending' => 'Chờ duyệt',
                                                    'approved' => 'Đã duyệt',
                                                    'rejected' => 'Đã từ chối'
                                                ][$deposit->status] ?? 'Không xác định';
                                            @endphp

                                            <span class="badge badge-sm bg-gradient-{{ $statusClass }}">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($deposit->status !== 'pending' && $deposit->approver)
                                                <p class="text-xs font-weight-bold mb-0">{{ $deposit->approver->name }}</p>
                                                <p class="text-xs  mb-0">{{ $deposit->approved_at->format('d/m/Y H:i') }}</p>
                                            @else
                                                <p class="text-xs  mb-0">-</p>
                                            @endif
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                {{ $deposit->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#viewModal{{ $deposit->id }}"
                                                   class="btn btn-link text-info text-gradient px-3 mb-0">
                                                    <i class="far fa-eye me-2"></i>Xem
                                                </a>
                                                @if($deposit->status === 'rejected' && $deposit->note)
                                                    <button type="button" class="btn btn-link text-danger text-gradient px-3 mb-0"
                                                            data-bs-toggle="modal" data-bs-target="#noteModal{{ $deposit->id }}">
                                                        <i class="fas fa-info-circle me-2"></i>Lý do
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">Không có giao dịch nào</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 pt-4">
                        <x-pagination :paginator="$deposits" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @foreach($deposits as $deposit)
        <!-- Unified View Modal with actions -->
        <div class="modal fade" id="viewModal{{ $deposit->id }}" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel{{ $deposit->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewModalLabel{{ $deposit->id }}">Chi tiết giao dịch nạp xu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-2"><strong>Mã giao dịch:</strong> {{ $deposit->transaction_code }}</div>
                                <div class="mb-2"><strong>Người dùng:</strong> {{ $deposit->user->name }} ({{ $deposit->user->email }})</div>
                                <div class="mb-2"><strong>Ngân hàng:</strong> {{ $deposit->bank->name }} - {{ $deposit->bank->account_number }}</div>
                                <div class="mb-2"><strong>Số tiền:</strong> <span class="text-danger fw-bold">{{ number_format($deposit->amount, 0, ',', '.') }} VNĐ</span></div>
                                <div class="mb-2"><strong>Số xu:</strong> <span class="text-danger fw-bold">{{ number_format($deposit->coins, 0, ',', '.') }} xu</span></div>
                                @php
                                    $__statusText = [
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'rejected' => 'Đã từ chối'
                                    ][$deposit->status] ?? 'Không xác định';
                                @endphp
                                <div class="mb-2"><strong>Trạng thái:</strong> {{ $__statusText }}</div>
                                <div class="mb-2"><strong>Thời gian:</strong> {{ $deposit->created_at->format('d/m/Y H:i') }}</div>
                                @if($deposit->approved_at)
                                    <div class="mb-2"><strong>Xử lý lúc:</strong> {{ $deposit->approved_at->format('d/m/Y H:i') }}</div>
                                @endif
                                @if($deposit->status === 'rejected' && $deposit->note)
                                    <div class="alert alert-danger mt-2 mb-0"><strong>Lý do từ chối:</strong> {{ $deposit->note }}</div>
                                @endif

                                @if($deposit->status === 'pending')
                                    <hr class="my-3">
                                    <div class="row g-2">
                                        <div class="col-6 d-grid">
                                            <button type="button" class="btn bg-gradient-danger" id="rejectAction{{ $deposit->id }}">
                                                <i class="fas fa-times me-2"></i>Từ chối
                                            </button>
                                        </div>
                                        <div class="col-6 d-grid">
                                            <button type="button" class="btn bg-gradient-success" id="approveAction{{ $deposit->id }}">
                                                <i class="fas fa-check me-2"></i>Duyệt
                                            </button>
                                        </div>
                                        
                                    </div>

                                    <!-- Hidden forms for actions -->
                                    <form action="{{ route('deposits.approve', $deposit) }}" method="POST" class="d-none" id="approveForm{{ $deposit->id }}">
                                        @csrf
                                        <button type="submit" id="approveBtn{{ $deposit->id }}">
                                            <span class="btn-text">Duyệt</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                    </form>
                                    <form action="{{ route('deposits.reject', $deposit) }}" method="POST" class="d-none" id="rejectForm{{ $deposit->id }}">
                                        @csrf
                                        <input type="hidden" name="note" id="rejectNote{{ $deposit->id }}">
                                        <button type="submit" id="rejectBtn{{ $deposit->id }}">
                                            <span class="btn-text">Từ chối</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <div class="col-md-6 text-center">
                                @if($deposit->image)
                        <img src="{{ asset('storage/' . $deposit->image) }}" class="img-fluid" alt="Chứng minh chuyển khoản">
                                @else
                                    <span class="text-muted">Không có ảnh chứng minh</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Note Modal (for viewing rejection reason) -->
        @if($deposit->status === 'rejected' && $deposit->note)
            <div class="modal fade" id="noteModal{{ $deposit->id }}" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel{{ $deposit->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="noteModalLabel{{ $deposit->id }}">Lý do từ chối giao dịch</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6 class="text-sm">Mã giao dịch: <span class="text-dark">{{ $deposit->transaction_code }}</span></h6>
                                    <h6 class="text-sm">Người dùng: <span class="text-dark">{{ $deposit->user->name }}</span></h6>
                                    <h6 class="text-sm">Số tiền: <span class="text-dark">{{ number_format($deposit->amount, 0, ',', '.') }} VNĐ</span></h6>
                                    <h6 class="text-sm">Từ chối bởi: <span class="text-dark">{{ $deposit->approver->name ?? 'Không xác định' }}</span></h6>
                                    <h6 class="text-sm">Thời gian: <span class="text-dark">{{ $deposit->approved_at->format('d/m/Y H:i') }}</span></h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <label class="form-control-label mb-2">Lý do từ chối:</label>
                                    <p class="p-3 bg-light rounded">{{ $deposit->note }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection

@push('scripts-admin')
    <script>
        // Ensure SweetAlert2 is available in admin pages
        (function ensureSwal(){
            if (typeof Swal === 'undefined') {
                var s=document.createElement('script');
                s.src='https://cdn.jsdelivr.net/npm/sweetalert2@11';
                document.head.appendChild(s);
            }
        })();
        $(document).ready(function() {
            var modals = [].slice.call(document.querySelectorAll('.modal'))
            modals.map(function (modalEl) {
                return new bootstrap.Modal(modalEl)
            });

            // Auto-open modal from Telegram link
            const urlParams = new URLSearchParams(window.location.search);
            const viewId = urlParams.get('view');
            if (viewId) {
                const modalId = 'viewModal' + viewId;
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    // Remove query parameter from URL after opening modal
                    const newUrl = window.location.pathname + window.location.search.replace(/[?&]view=[^&]*/, '').replace(/^&/, '?');
                    window.history.replaceState({}, '', newUrl || window.location.pathname);
                }
            }

            // Approve action with Swal
            $(document).on('click', '[id^="approveAction"]', function() {
                const id = $(this).attr('id').replace('approveAction', '');
                const form = document.getElementById('approveForm' + id);
                const button = $('#approveAction' + id);
                if (typeof Swal !== 'undefined') {
                    const targetEl = document.getElementById('viewModal' + id) || document.body;
                    Swal.fire({
                        icon: 'question',
                        title: 'Xác nhận duyệt giao dịch?',
                        text: 'Người dùng sẽ được cộng xu vào tài khoản.',
                        showCancelButton: true,
                        confirmButtonText: 'Duyệt',
                        cancelButtonText: 'Hủy',
                        target: targetEl,
                        didOpen: () => {
                            const input = Swal.getInput();
                            if (input) input.blur();
                        }
                    }).then(res => {
                        if (res.isConfirmed) {
                            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Đang xử lý...');
                            form.submit();
                        }
                    });
                }
            });

            // Reject action with Swal + input reason
            $(document).on('click', '[id^="rejectAction"]', function() {
                const id = $(this).attr('id').replace('rejectAction', '');
                const form = document.getElementById('rejectForm' + id);
                const noteInput = document.getElementById('rejectNote' + id);
                const button = $('#rejectAction' + id);
                if (typeof Swal !== 'undefined') {
                    const targetEl = document.getElementById('viewModal' + id) || document.body;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Từ chối giao dịch?',
                        input: 'text',
                        inputLabel: 'Lý do từ chối',
                        inputPlaceholder: 'Nhập lý do từ chối...',
                        inputValidator: (value) => { if (!value) return 'Vui lòng nhập lý do'; },
                        showCancelButton: true,
                        confirmButtonText: 'Từ chối',
                        cancelButtonText: 'Hủy',
                        target: targetEl,
                        didOpen: () => {
                            const input = Swal.getInput();
                            if (input) input.focus();
                        }
                    }).then(res => {
                        if (res.isConfirmed) {
                            noteInput.value = res.value;
                            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Đang xử lý...');
                            form.submit();
                        }
                    });
                }
            });
        });
    </script>
@endpush
