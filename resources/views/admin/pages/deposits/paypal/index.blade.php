@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-0 mx-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                        <div>
                            <h5 class="mb-0">Quản lý nạp PayPal</h5>
                            <p class="text-sm mb-0">Quản lý các giao dịch nạp xu bằng PayPal</p>
                        </div>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <a href="{{ route('admin.request-payment-paypal.index') }}"
                                class="btn bg-gradient-warning btn-sm">
                                <i class="fas fa-clock me-2"></i><span class="d-none d-md-inline">Yêu cầu thanh
                                    toán</span><span class="d-md-none">Yêu cầu</span>
                            </a>
                            <a href="{{ route('admin.card-deposits.index') }}" class="btn bg-gradient-info btn-sm">
                                <i class="fas fa-credit-card me-2"></i><span class="d-none d-md-inline">Card
                                    Deposits</span><span class="d-md-none">Card</span>
                            </a>
                            <a href="{{ route('deposits.index') }}" class="btn bg-gradient-primary btn-sm">
                                <i class="fas fa-university me-2"></i><span class="d-none d-md-inline">Bank
                                    Deposits</span><span class="d-md-none">Bank</span>
                            </a>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mt-3">
                        <form method="GET" class="d-flex flex-column flex-md-row gap-2 flex-fill" id="filterForm">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">- Trạng thái -</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý
                                </option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Đang xử
                                    lý</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt
                                </option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối
                                </option>
                            </select>

                            <input type="date" name="date" class="form-control form-control-sm"
                                value="{{ request('date') }}" onchange="this.form.submit()">

                            <div class="input-group input-group-sm flex-fill">
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                    placeholder="Tìm kiếm...">
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
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">ID</th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder  ps-2">Người dùng</th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">Mã GD</th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">Email PayPal</th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">Số tiền</th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">Xu</th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">Trạng thái</th>
                                    <th class="text-uppercase  text-xxs font-weight-bolder ">Thời gian</th>
                                    <th class="text-center text-uppercase  text-xxs font-weight-bolder ">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paypalDeposits as $deposit)
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
                                            <p class="text-xs font-weight-bold mb-0">{{ $deposit->transaction_code }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $deposit->paypal_email }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $deposit->usd_amount_formatted }}
                                            </p>
                                            <p class="text-xs  mb-0">{{ $deposit->vnd_amount_formatted }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $deposit->coins_formatted }} xu</p>
                                        </td>
                                        <td>
                                            <span class="badge badge-sm {{ $deposit->status_badge }}">
                                                {{ $deposit->status_text }}
                                            </span>
                                            {{-- @if ($deposit->note)
                                                <button type="button" class="btn btn-link text-danger text-xs p-0 ms-1"
                                                        data-bs-toggle="modal" data-bs-target="#noteModal{{ $deposit->id }}">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            @endif --}}
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                {{ $deposit->created_at->format('d/m/Y H:i') }}</p>
                                            @if ($deposit->processed_at)
                                                <p class="text-xs  mb-0">{{ $deposit->processed_at->format('d/m/Y H:i') }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="#" data-bs-toggle="modal"
                                                    data-bs-target="#viewModal{{ $deposit->id }}"
                                                    class="btn btn-link text-info text-gradient px-3 mb-0">
                                                    <i class="far fa-eye me-2"></i>Xem
                                                </a>
                                                @if ($deposit->status == 'rejected' && $deposit->note)
                                                    <button type="button"
                                                        class="btn btn-link text-danger text-gradient px-3 mb-0"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#noteModal{{ $deposit->id }}">
                                                        <i class="fas fa-info-circle me-2"></i>Lý do
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">Không có giao dịch PayPal nào</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 pt-4">
                        <x-pagination :paginator="$paypalDeposits" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @foreach ($paypalDeposits as $deposit)
        <!-- Unified View Modal with actions -->
        <div class="modal fade" id="viewModal{{ $deposit->id }}" tabindex="-1" role="dialog"
            aria-labelledby="viewModalLabel{{ $deposit->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewModalLabel{{ $deposit->id }}">Chi tiết nạp PayPal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-2"><strong>Mã giao dịch:</strong> <span class="text-danger fw-bold">{{ $deposit->transaction_code }}</span></div>
                                <div class="mb-2"><strong>Người dùng:</strong> {{ $deposit->user->name }}
                                    ({{ $deposit->user->email }})</div>
                                <div class="mb-2"><strong>Email PayPal:</strong> {{ $deposit->paypal_email }}</div>
                                <div class="mb-2"><strong>Số tiền USD:</strong> {{ $deposit->usd_amount_formatted }}
                                </div>
                                <div class="mb-2"><strong>Số tiền VND:</strong> <span class="text-danger fw-bold">{{ $deposit->vnd_amount_formatted }}</span>
                                </div>
                                <div class="mb-2"><strong>Số xu:</strong> <span class="text-danger fw-bold">{{ $deposit->coins_formatted }} xu</span></div>
                                <div class="mb-2"><strong>Trạng thái:</strong> {{ $deposit->status_text }}</div>
                                <div class="mb-2"><strong>Thời gian:</strong>
                                    {{ $deposit->created_at->format('d/m/Y H:i') }}</div>
                                @if ($deposit->processed_at)
                                    <div class="mb-2"><strong>Xử lý lúc:</strong>
                                        {{ $deposit->processed_at->format('d/m/Y H:i') }}</div>
                                @endif
                                @if ($deposit->status === 'rejected' && $deposit->note)
                                    <div class="alert alert-danger mt-2 mb-0"><strong>Lý do từ chối:</strong>
                                        {{ $deposit->note }}</div>
                                @endif

                                @if ($deposit->status === 'processing')
                                    <hr class="my-3">
                                    <div class="row g-2">
                                        <div class="col-6 d-grid">
                                            <button type="button" class="btn bg-gradient-danger"
                                                id="rejectPaypalAction{{ $deposit->id }}">
                                                <i class="fas fa-times me-2"></i>Từ chối
                                            </button>
                                        </div>
                                        <div class="col-6 d-grid">
                                            <button type="button" class="btn bg-gradient-success"
                                                id="approvePaypalAction{{ $deposit->id }}">
                                                <i class="fas fa-check me-2"></i>Duyệt
                                            </button>
                                        </div>
                                       
                                    </div>

                                    <!-- Hidden forms for actions -->
                                    <form action="{{ route('admin.paypal-deposits.approve', $deposit) }}" method="POST"
                                        class="d-none" id="approvePaypalForm{{ $deposit->id }}">
                                        @csrf
                                        <button type="submit" id="approvePaypalBtn{{ $deposit->id }}">
                                            <span class="btn-text">Duyệt</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                                aria-hidden="true"></span>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.paypal-deposits.reject', $deposit) }}" method="POST"
                                        class="d-none" id="rejectPaypalForm{{ $deposit->id }}">
                                        @csrf
                                        <input type="hidden" name="note" id="rejectPaypalNote{{ $deposit->id }}">
                                        <button type="submit" id="rejectPaypalBtn{{ $deposit->id }}">
                                            <span class="btn-text">Từ chối</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                                aria-hidden="true"></span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <div class="col-md-6 text-center">
                                @if ($deposit->image)
                                    <img src="{{ Storage::url($deposit->image) }}" class="img-fluid"
                                        alt="Chứng minh PayPal">
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

        <!-- Note Modal -->
        @if ($deposit->note)
            <div class="modal fade" id="noteModal{{ $deposit->id }}" tabindex="-1" role="dialog"
                aria-labelledby="noteModalLabel{{ $deposit->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="noteModalLabel{{ $deposit->id }}">Ghi chú giao dịch</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6 class="text-sm">Mã giao dịch: <span
                                            class="text-dark">{{ $deposit->transaction_code }}</span></h6>
                                    <h6 class="text-sm">Người dùng: <span
                                            class="text-dark">{{ $deposit->user->name }}</span></h6>
                                    <h6 class="text-sm">Trạng thái: <span
                                            class="text-dark">{{ $deposit->status_text }}</span></h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <label class="form-control-label mb-2">Ghi chú:</label>
                                    <p class="p-3 bg-light rounded">{{ $deposit->note }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn bg-gradient-secondary"
                                data-bs-dismiss="modal">Đóng</button>
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
        (function ensureSwal() {
            if (typeof Swal === 'undefined') {
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                document.head.appendChild(s);
            }
        })();
        $(document).ready(function() {
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
                    const newUrl = window.location.pathname + window.location.search.replace(/[?&]view=[^&]*/, '')
                        .replace(/^&/, '?');
                    window.history.replaceState({}, '', newUrl || window.location.pathname);
                }
            }

            // Approve action with Swal (PayPal)
            $(document).on('click', '[id^="approvePaypalAction"]', function() {
                const id = $(this).attr('id').replace('approvePaypalAction', '');
                const form = document.getElementById('approvePaypalForm' + id);
                const button = $('#approvePaypalAction' + id);
                const targetEl = document.getElementById('viewModal' + id) || document.body;
                Swal.fire({
                    icon: 'question',
                    title: 'Xác nhận duyệt giao dịch PayPal?',
                    text: 'Người dùng sẽ được cộng xu vào tài khoản.',
                    showCancelButton: true,
                    confirmButtonText: 'Duyệt',
                    cancelButtonText: 'Hủy',
                    reverseButtons: true,
                    target: targetEl,
                    didOpen: () => {
                        const input = Swal.getInput();
                        if (input) input.blur();
                    }
                }).then(res => {
                    if (res.isConfirmed) {
                        button.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...'
                            );
                        form.submit();
                    }
                });
            });

            // Reject action with Swal + input (PayPal)
            $(document).on('click', '[id^="rejectPaypalAction"]', function() {
                const id = $(this).attr('id').replace('rejectPaypalAction', '');
                const form = document.getElementById('rejectPaypalForm' + id);
                const noteInput = document.getElementById('rejectPaypalNote' + id);
                const button = $('#rejectPaypalAction' + id);
                const targetEl = document.getElementById('viewModal' + id) || document.body;
                Swal.fire({
                    icon: 'warning',
                    title: 'Từ chối giao dịch PayPal?',
                    input: 'text',
                    inputLabel: 'Lý do từ chối',
                    inputPlaceholder: 'Nhập lý do từ chối...',
                    inputValidator: (value) => {
                        if (!value) return 'Vui lòng nhập lý do';
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Từ chối',
                    cancelButtonText: 'Hủy',
                    reverseButtons: true,
                    target: targetEl,
                    didOpen: () => {
                        const input = Swal.getInput();
                        if (input) input.focus();
                    }
                }).then(res => {
                    if (res.isConfirmed) {
                        noteInput.value = res.value;
                        button.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...'
                            );
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
