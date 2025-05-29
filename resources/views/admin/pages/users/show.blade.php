@extends('admin.layouts.app')
@push('styles-admin')
    <style>
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }

        .toast {
            min-width: 250px;
        }
        
        .stats-card {
            transition: all 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .nav-tabs .nav-link {
            position: relative;
        }
        
        .nav-tabs .nav-link.active:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #5e72e4 0%, #825ee4 100%);
        }
    </style>
@endpush
@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <h5 class="mb-0">Chi tiết người dùng</h5>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">Quay lại</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="{{ $user->avatar ? Storage::url($user->avatar) : asset('assets/images/avatar_default.jpg') }}"
                                class="rounded-circle img-fluid mb-3"
                                style="width: 150px; height: 150px; object-fit: cover;">
                            @if ($user->avatar && auth()->user()->role === 'admin')
                                <button class="btn btn-danger btn-sm" id="delete-avatar">
                                    <i class="fas fa-trash"></i> Xóa ảnh
                                </button>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <h6 class="text-sm">Tên người dùng</h6>
                                <p class="text-dark mb-0">{{ $user->name }}</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-sm">Email</h6>
                                <p class="text-dark mb-0">{{ $user->email }}</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-sm">Ngày tham gia</h6>
                                <p class="text-dark mb-0">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-sm">IP Address</h6>
                                <p class="text-dark mb-0">{{ $user->ip_address ?: 'Không có' }}</p>
                            </div>

                            @php 
                                $superAdminEmails = explode(',', env('SUPER_ADMIN_EMAILS', 'admin@gmail.com'));
                                $isSuperAdmin = in_array(auth()->user()->email, $superAdminEmails);
                            @endphp
                            <div class="mb-3">
                                <h6 class="text-sm">Vai trò</h6>
                                @if (($isSuperAdmin && !in_array($user->email, $superAdminEmails)) || 
                                    (auth()->user()->role === 'admin' && $user->role !== 'admin' && !in_array($user->email, $superAdminEmails)))
                                    <select class="form-select form-select-sm w-auto" id="role-select">
                                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                        <option value="author" {{ $user->role === 'author' ? 'selected' : '' }}>Author</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                @else
                                    <p class="text-dark mb-0">{{ ucfirst($user->role) }}</p>
                                @endif
                            </div>
                            <div class="mb-3">
                                <h6 class="text-sm mb-3">Trạng thái hạn chế</h6>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input ban-toggle" type="checkbox" data-type="login"
                                            {{ $user->ban_login ? 'checked' : '' }}>
                                        <label class="form-check-label">Cấm đăng nhập</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input ban-toggle" type="checkbox" data-type="comment"
                                            {{ $user->ban_comment ? 'checked' : '' }}>
                                        <label class="form-check-label">Cấm bình luận</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input ban-toggle" type="checkbox" data-type="rate"
                                            {{ $user->ban_rate ? 'checked' : '' }}>
                                        <label class="form-check-label">Cấm đánh giá</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input ban-toggle" type="checkbox" data-type="read"
                                            {{ $user->ban_read ? 'checked' : '' }}>
                                        <label class="form-check-label">Cấm đọc truyện</label>
                                    </div>

                                    @if (auth()->user()->role === 'admin')
                                        <div class="form-check form-switch">
                                            <input class="form-check-input ban-toggle" type="checkbox" data-type="ip"
                                                {{ $user->banned_ips()->exists() ? 'checked' : '' }}>
                                            <label class="form-check-label">Cấm IP</label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Financial Statistics -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="mb-3">Thống kê tài chính</h5>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card bg-gradient-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-white mb-0">{{ number_format($stats['balance']) }}</h5>
                                            <p class="mb-0 text-sm">Số xu hiện tại</p>
                                        </div>
                                        <div class="icon-shape bg-white text-center rounded-circle shadow">
                                            <i class="fas fa-coins text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card bg-gradient-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-white mb-0">{{ number_format($stats['total_deposits']) }}</h5>
                                            <p class="mb-0 text-sm">Tổng xu đã nạp</p>
                                        </div>
                                        <div class="icon-shape bg-white text-center rounded-circle shadow">
                                            <i class="fas fa-wallet text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card bg-gradient-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-white mb-0">{{ number_format($stats['total_spent']) }}</h5>
                                            <p class="mb-0 text-sm">Tổng xu đã chi</p>
                                        </div>
                                        <div class="icon-shape bg-white text-center rounded-circle shadow">
                                            <i class="fas fa-shopping-cart text-danger"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($user->role === 'author')
                        <div class="col-md-3">
                            <div class="card stats-card bg-gradient-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-white mb-0">{{ number_format($stats['author_revenue']) }}</h5>
                                            <p class="mb-0 text-sm">Doanh thu tác giả</p>
                                        </div>
                                        <div class="icon-shape bg-white text-center rounded-circle shadow">
                                            <i class="fas fa-hand-holding-usd text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs mt-4" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#deposits" role="tab">
                                <i class="fas fa-wallet me-1"></i> Nạp xu
                                <span class="badge bg-primary rounded-pill">{{ $counts['deposits'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#story-purchases" role="tab">
                                <i class="fas fa-shopping-cart me-1"></i> Mua truyện
                                <span class="badge bg-primary rounded-pill">{{ $counts['story_purchases'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#chapter-purchases" role="tab">
                                <i class="fas fa-file-invoice-dollar me-1"></i> Mua chương
                                <span class="badge bg-primary rounded-pill">{{ $counts['chapter_purchases'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#bookmarks" role="tab">
                                <i class="fas fa-bookmark me-1"></i> Theo dõi
                                <span class="badge bg-primary rounded-pill">{{ $counts['bookmarks'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#coin-transactions" role="tab">
                                <i class="fas fa-coins me-1"></i> Cộng/Trừ xu
                                <span class="badge bg-primary rounded-pill">{{ $counts['coin_transactions'] }}</span>
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <!-- Deposits Tab -->
                        <div class="tab-pane active" id="deposits" role="tabpanel">
                            <div class="table-responsive mt-3">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Ngân hàng</th>
                                            <th>Mã giao dịch</th>
                                            <th>Số tiền</th>
                                            <th>Số xu</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày nạp</th>
                                            <th>Ngày duyệt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($deposits as $deposit)
                                            <tr>
                                                <td>{{ $deposit->id }}</td>
                                                <td>{{ $deposit->bank->name ?? 'N/A' }}</td>
                                                <td>{{ $deposit->transaction_code }}</td>
                                                <td>{{ number_format($deposit->amount) }}đ</td>
                                                <td>{{ number_format($deposit->coins) }}</td>
                                                <td>
                                                    @if($deposit->status === 'approved')
                                                        <span class="badge bg-success">Đã duyệt</span>
                                                    @elseif($deposit->status === 'rejected')
                                                        <span class="badge bg-danger">Từ chối</span>
                                                    @else
                                                        <span class="badge bg-warning">Chờ duyệt</span>
                                                    @endif
                                                </td>
                                                <td>{{ $deposit->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $deposit->approved_at ? $deposit->approved_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">Chưa có giao dịch nạp xu</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($counts['deposits'] > 5)
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $deposits->fragment('deposits')->links() }}
                                    </div>
                                    <div class="text-center mt-3">
                                        <button class="btn btn-sm btn-primary load-more" data-type="deposits">
                                            Xem thêm <i class="fas fa-chevron-down ms-1"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Story Purchases Tab -->
                        <div class="tab-pane" id="story-purchases" role="tabpanel">
                            <div class="table-responsive mt-3">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Truyện</th>
                                            <th>Số xu</th>
                                            <th>Ngày mua</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($storyPurchases as $purchase)
                                            <tr>
                                                <td>{{ $purchase->id }}</td>
                                                <td>
                                                    <a href="{{ route('stories.show', $purchase->story_id) }}">
                                                        {{ $purchase->story->title ?? 'Không xác định' }}
                                                    </a>
                                                </td>
                                                <td>{{ number_format($purchase->amount_paid) }}</td>
                                                <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">Chưa có giao dịch mua truyện</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($counts['story_purchases'] > 5)
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $storyPurchases->fragment('story-purchases')->links() }}
                                    </div>
                                    <div class="text-center mt-3">
                                        <button class="btn btn-sm btn-primary load-more" data-type="story-purchases">
                                            Xem thêm <i class="fas fa-chevron-down ms-1"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Chapter Purchases Tab -->
                        <div class="tab-pane" id="chapter-purchases" role="tabpanel">
                            <div class="table-responsive mt-3">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Truyện</th>
                                            <th>Chương</th>
                                            <th>Số xu</th>
                                            <th>Ngày mua</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($chapterPurchases as $purchase)
                                            <tr>
                                                <td>{{ $purchase->id }}</td>
                                                <td>
                                                    <a href="{{ route('stories.show', $purchase->chapter->story_id) }}">
                                                        {{ $purchase->chapter->story->title ?? 'Không xác định' }}
                                                    </a>
                                                </td>
                                                <td>Chương {{ $purchase->chapter->number }}: {{ Str::limit($purchase->chapter->title, 30) }}</td>
                                                <td>{{ number_format($purchase->amount_paid) }}</td>
                                                <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Chưa có giao dịch mua chương</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($counts['chapter_purchases'] > 5)
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $chapterPurchases->fragment('chapter-purchases')->links() }}
                                    </div>
                                    <div class="text-center mt-3">
                                        <button class="btn btn-sm btn-primary load-more" data-type="chapter-purchases">
                                            Xem thêm <i class="fas fa-chevron-down ms-1"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Bookmarks Tab -->
                        <div class="tab-pane" id="bookmarks" role="tabpanel">
                            <div class="table-responsive mt-3">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Truyện</th>
                                            <th>Chương đã đọc</th>
                                            <th>Thông báo</th>
                                            <th>Ngày theo dõi</th>
                                            <th>Đọc gần nhất</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($bookmarks as $bookmark)
                                            <tr>
                                                <td>{{ $bookmark->id }}</td>
                                                <td>
                                                    <a href="{{ route('stories.show', $bookmark->story_id) }}">
                                                        {{ $bookmark->story->title ?? 'Không xác định' }}
                                                    </a>
                                                </td>
                                                <td>
                                                    @if($bookmark->lastChapter)
                                                        Chương {{ $bookmark->lastChapter->number }}: {{ Str::limit($bookmark->lastChapter->title, 30) }}
                                                    @else
                                                        Chưa đọc
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($bookmark->notification_enabled)
                                                        <span class="badge bg-success">Bật</span>
                                                    @else
                                                        <span class="badge bg-secondary">Tắt</span>
                                                    @endif
                                                </td>
                                                <td>{{ $bookmark->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $bookmark->last_read_at ? $bookmark->last_read_at->format('d/m/Y H:i') : 'Chưa đọc' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Chưa có truyện nào được theo dõi</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($counts['bookmarks'] > 5)
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $bookmarks->fragment('bookmarks')->links() }}
                                    </div>
                                    <div class="text-center mt-3">
                                        <button class="btn btn-sm btn-primary load-more" data-type="bookmarks">
                                            Xem thêm <i class="fas fa-chevron-down ms-1"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Coin Transactions Tab -->
                        <div class="tab-pane" id="coin-transactions" role="tabpanel">
                            <div class="d-flex justify-content-end mt-3">
                                <a href="{{ route('coins.create', $user->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Cộng/Trừ xu
                                </a>
                            </div>
                            <div class="table-responsive mt-3">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Loại giao dịch</th>
                                            <th>Số xu</th>
                                            <th>Admin thực hiện</th>
                                            <th>Ghi chú</th>
                                            <th>Thời gian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($coinTransactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->id }}</td>
                                                <td>
                                                    @if($transaction->type === 'add')
                                                        <span class="badge bg-success">Cộng xu</span>
                                                    @else
                                                        <span class="badge bg-danger">Trừ xu</span>
                                                    @endif
                                                </td>
                                                <td>{{ number_format($transaction->amount) }}</td>
                                                <td>{{ $transaction->admin->name ?? 'N/A' }}</td>
                                                <td>{{ $transaction->note ?? 'Không có ghi chú' }}</td>
                                                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Chưa có giao dịch xu nào</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($counts['coin_transactions'] > 5)
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $coinTransactions->fragment('coin-transactions')->links() }}
                                    </div>
                                    <div class="text-center mt-3">
                                        <button class="btn btn-sm btn-primary load-more" data-type="coin-transactions">
                                            Xem thêm <i class="fas fa-chevron-down ms-1"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container"></div>
@endsection

@push('scripts-admin')
    <script>
        function showToast(message, type = 'success') {
            const toast = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

            $('.toast-container').append(toast);
            const toastElement = $('.toast-container .toast').last();
            const bsToast = new bootstrap.Toast(toastElement, {
                delay: 3000
            });
            bsToast.show();

            // Remove toast after it's hidden
            toastElement.on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }

        $(document).ready(function() {
            // Handle tab navigation with URL hash
            const hash = window.location.hash;
            if (hash) {
                const triggerEl = document.querySelector(`a[href="${hash}"]`);
                if (triggerEl) {
                    triggerEl.click();
                }
            }
            
            // Update URL hash when tab changes
            const tabLinks = document.querySelectorAll('.nav-link');
            tabLinks.forEach(link => {
                link.addEventListener('click', function() {
                    window.location.hash = this.getAttribute('href');
                });
            });
            
            // Load more functionality
            $('.load-more').click(function() {
                const type = $(this).data('type');
                const currentPage = parseInt($(this).data('page') || 1);
                const nextPage = currentPage + 1;
                const button = $(this);
                
                button.html('<i class="fas fa-spinner fa-spin"></i> Đang tải...');
                button.prop('disabled', true);
                
                $.ajax({
                    url: '{{ route('users.load-more', $user->id) }}',
                    type: 'GET',
                    data: {
                        type: type,
                        page: nextPage
                    },
                    success: function(response) {
                        // Append new rows to the table
                        $(`#${type} table tbody`).append(response.html);
                        
                        // Update pagination
                        $(`#${type} .justify-content-center`).html(response.pagination);
                        
                        // Update the load more button
                        button.data('page', nextPage);
                        button.html('Xem thêm <i class="fas fa-chevron-down ms-1"></i>');
                        button.prop('disabled', false);
                        
                        // Hide button if no more pages
                        if (!response.has_more) {
                            button.hide();
                        }
                    },
                    error: function() {
                        showToast('Có lỗi xảy ra khi tải dữ liệu', 'error');
                        button.html('Xem thêm <i class="fas fa-chevron-down ms-1"></i>');
                        button.prop('disabled', false);
                    }
                });
            });

            $('.ban-toggle').change(function() {
                const type = $(this).data('type');
                const value = $(this).prop('checked');
                const checkbox = $(this);

                if (type === 'ip') {
                    $.ajax({
                        url: '{{ route('users.banip', $user->id) }}',
                        type: 'POST',
                        data: {
                            ban: value,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                showToast(res.message, 'success');
                            }
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON.message || 'Có lỗi xảy ra', 'error');
                            checkbox.prop('checked', !value);
                        }
                    });
                    return;
                }

                $.ajax({
                    url: '{{ route('users.update', $user->id) }}',
                    type: 'PATCH',
                    data: {
                        [`ban_${type}`]: value,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            showToast(res.message, 'success');
                        }
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON.message || 'Có lỗi xảy ra', 'error');
                        checkbox.prop('checked', !value);
                    }
                });
            });
        });
    </script>


    {{-- edit role --}}
    <script>
        $(document).ready(function() {
            $('#role-select').change(function() {
                const newRole = $(this).val();
                const oldRole = $(this).find('option[selected]').val();

                if (confirm(
                        `Bạn có chắc muốn thay đổi quyền của người dùng thành ${newRole.toUpperCase()}?`)) {
                    $.ajax({
                        url: '{{ route('users.update', $user->id) }}',
                        type: 'PATCH',
                        data: {
                            role: newRole,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                showToast(res.message, 'success');
                            }
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON.message || 'Có lỗi xảy ra', 'error');
                            $(this).val(oldRole);
                        }
                    });
                } else {
                    $(this).val(oldRole);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#delete-avatar').click(function() {
                if (confirm('Bạn có chắc muốn xóa ảnh đại diện?')) {
                    $.ajax({
                        url: '{{ route('users.update', $user->id) }}',
                        type: 'PATCH',
                        data: {
                            delete_avatar: true,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                showToast(res.message, 'success');
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON.message || 'Có lỗi xảy ra', 'error');
                        }
                    });
                }
            });
        });
    </script>
@endpush
