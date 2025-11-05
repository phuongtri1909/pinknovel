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
        
        /* User Info Card */
        .user-info-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .user-info-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        .user-avatar-section {
            background: linear-gradient(135deg, #ffffff 0%, #a7a7a7 100%);
            padding: 1rem;
            text-align: center;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border: 3px solid rgba(255,255,255,0.3);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .user-details-section {
            padding: 1rem;
        }
        
        .user-info-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .user-info-item:last-child {
            border-bottom: none;
        }
        
        .user-info-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: 0.3px;
            margin-bottom: 0.15rem;
        }
        
        .user-info-value {
            font-size: 0.85rem;
            font-weight: 500;
            color: #2d3748;
        }
        
        .ban-switches-container {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 0.75rem;
        }
        
        .ban-switch-item {
            padding: 0.25rem 0;
        }
        
        /* Stats Cards */
        .stats-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.12);
        }
        
        .stats-card .card-body {
            padding: 0.75rem;
        }
        
        .stats-card .icon-shape {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
        
        .stats-card h6 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .stats-card p {
            font-size: 0.7rem;
            margin-bottom: 0;
        }
        
        /* Tabs */
        .nav-tabs {
            border-bottom: 2px solid #e9ecef;
            background: #fff;
            border-radius: 12px 12px 0 0;
            padding: 0.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }
        
        .nav-tabs::-webkit-scrollbar {
            height: 6px;
        }
        
        .nav-tabs::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .nav-tabs::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .nav-tabs .nav-item {
            flex: 0 0 auto;
        }
        
        .nav-tabs .nav-link {
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #6c757d;
            transition: all 0.3s ease;
            white-space: nowrap;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-tabs .nav-link:hover {
            background: #f8f9fa;
            color: #495057;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        .nav-tabs .nav-link.active .badge {
            background: rgba(255,255,255,0.25) !important;
            color: #fff !important;
        }
        
        .nav-tabs .nav-link .badge {
            font-size: 0.7rem;
            padding: 0.25em 0.5em;
            margin-left: 0.25rem;
        }
        
        @media (max-width: 767.98px) {
            .nav-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 0.5rem;
            }
            
            .nav-tabs .nav-item {
                flex: 0 0 auto;
                min-width: 100px;
            }
            
            .nav-tabs .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.75rem;
                white-space: nowrap;
            }
            
            .nav-tabs .nav-link i {
                font-size: 0.9rem;
                margin-right: 0.25rem;
            }
            
            .nav-tabs .nav-link .badge {
                font-size: 0.65rem;
                padding: 0.15em 0.4em;
            }
        }
        
        /* Tab Content */
        .tab-content {
            background: #fff;
            border-radius: 0 0 12px 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .tab-pane {
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Tables */
        .table-responsive {
            border-radius: 8px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        .table {
            margin-bottom: 0;
            min-width: 600px;
        }
        
        .table thead {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .table thead th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #495057;
            padding: 0.75rem;
            white-space: nowrap;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .table tbody td {
            padding: 0.75rem;
            vertical-align: middle;
        }
        
        .table tbody td:nth-child(2) {
            min-width: 250px;
            max-width: 400px;
        }
        
        .table tbody td:nth-child(2) h6 {
            white-space: normal;
            word-wrap: break-word;
            line-height: 1.4;
        }
        
        @media (max-width: 767.98px) {
            .table thead th {
                font-size: 0.75rem;
                padding: 0.5rem 0.4rem;
            }
            
            .table tbody td {
                font-size: 0.85rem;
                padding: 0.5rem 0.4rem;
            }
            
            .table tbody td:nth-child(2) {
                min-width: 180px;
                max-width: 250px;
            }
        }
        
        /* Section Headers */
        .section-header {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-header i {
            color: #667eea;
        }
        
        /* Responsive improvements */
        @media (max-width: 991.98px) {
            .user-avatar-section {
                padding: 1.5rem;
            }
            
            .user-details-section {
                padding: 1.25rem;
            }
            
            .stats-card .card-body {
                padding: 1rem;
            }
            
            .stats-card h5 {
                font-size: 1.25rem;
            }
        }
        
        /* Button improvements */
        .btn-sm {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.4rem 0.8rem;
        }
        
        .load-more {
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
    </style>
@endpush
@section('content-auth')
    <div class="row g-3">
        <!-- User Info Card -->
        <div class="col-12">
            <div class="card user-info-card">
                <div class="user-avatar-section">
                    <img src="{{ $user->avatar ? Storage::url($user->avatar) : asset('assets/images/avatar_default.jpg') }}"
                        class="rounded-circle user-avatar mb-3"
                        alt="{{ $user->name }}">
                    @if ($user->avatar && auth()->user()->role === 'admin')
                        <button class="btn btn-light btn-sm" id="delete-avatar">
                            <i class="fas fa-trash me-1"></i> Xóa ảnh
                        </button>
                    @endif
                </div>
                <div class="user-details-section">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="user-info-item">
                                <div class="user-info-label">
                                    <i class="fas fa-user me-1"></i> Tên người dùng
                                </div>
                                <div class="user-info-value">{{ $user->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="user-info-item">
                                <div class="user-info-label">
                                    <i class="fas fa-envelope me-1"></i> Email
                                </div>
                                <div class="user-info-value">{{ $user->email }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="user-info-item">
                                <div class="user-info-label">
                                    <i class="fas fa-calendar me-1"></i> Ngày tham gia
                                </div>
                                <div class="user-info-value">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="user-info-item">
                                <div class="user-info-label">
                                    <i class="fas fa-network-wired me-1"></i> IP Address
                                </div>
                                <div class="user-info-value">{{ $user->ip_address ?: 'Không có' }}</div>
                            </div>
                        </div>
                        @php 
                            $superAdminEmails = explode(',', env('SUPER_ADMIN_EMAILS', 'admin@gmail.com'));
                            $isSuperAdmin = in_array(auth()->user()->email, $superAdminEmails);
                        @endphp
                        <div class="col-md-6">
                            <div class="user-info-item">
                                <div class="user-info-label">
                                    <i class="fas fa-user-shield me-1"></i> Vai trò
                                </div>
                                <div class="user-info-value">
                                    @if (($isSuperAdmin && !in_array($user->email, $superAdminEmails)) || 
                                        (auth()->user()->role === 'admin' && $user->role !== 'admin' && !in_array($user->email, $superAdminEmails)))
                                        <select class="form-select form-select-sm w-auto" id="role-select" style="max-width: 120px;">
                                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                            <option value="author" {{ $user->role === 'author' ? 'selected' : '' }}>Author</option>
                                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                    @else
                                        <span class="badge bg-primary" style="font-size: 0.75rem;">{{ ucfirst($user->role) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ban-switches-container mt-2" style="margin-top: 0.75rem !important;">
                        <div class="user-info-label mb-2">
                            <i class="fas fa-ban me-1"></i> Trạng thái hạn chế
                        </div>
                        <div class="row g-1">
                            <div class="col-6 ban-switch-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input ban-toggle" type="checkbox" data-type="login"
                                        {{ $user->ban_login ? 'checked' : '' }}>
                                    <label class="form-check-label" style="font-size: 0.75rem;">Đăng nhập</label>
                                </div>
                            </div>
                            <div class="col-6 ban-switch-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input ban-toggle" type="checkbox" data-type="comment"
                                        {{ $user->ban_comment ? 'checked' : '' }}>
                                    <label class="form-check-label" style="font-size: 0.75rem;">Bình luận</label>
                                </div>
                            </div>
                            <div class="col-6 ban-switch-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input ban-toggle" type="checkbox" data-type="rate"
                                        {{ $user->ban_rate ? 'checked' : '' }}>
                                    <label class="form-check-label" style="font-size: 0.75rem;">Đánh giá</label>
                                </div>
                            </div>
                            <div class="col-6 ban-switch-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input ban-toggle" type="checkbox" data-type="read"
                                        {{ $user->ban_read ? 'checked' : '' }}>
                                    <label class="form-check-label" style="font-size: 0.75rem;">Đọc truyện</label>
                                </div>
                            </div>
                            @if (auth()->user()->role === 'admin')
                                <div class="col-6 ban-switch-item">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input ban-toggle" type="checkbox" data-type="ip"
                                            {{ $user->banned_ips()->exists() ? 'checked' : '' }}>
                                        <label class="form-check-label" style="font-size: 0.75rem;">IP</label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Financial Statistics -->
        <div class="col-12">
            <div class="card mb-3" style="border-radius: 12px; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div class="card-body">
                    <h5 class="section-header mb-3">
                        <i class="fas fa-chart-line"></i>
                        Thống kê tài chính
                    </h5>
                    <div class="row g-2">
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="card stats-card" style="background: #fff; border: 1px solid #e9ecef;">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold" style="font-size: 1rem; color: #2d3748;">{{ number_format($stats['balance']) }}</h6>
                                            <p class="mb-0 small text-muted" style="font-size: 0.7rem;">Số xu hiện tại</p>
                                        </div>
                                        <div class="icon-shape bg-light rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: normal; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-coins" style="font-size: 0.75rem; color: #6c757d;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="card stats-card" style="background: #fff; border: 1px solid #e9ecef;">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold" style="font-size: 1rem; color: #2d3748;">{{ number_format($stats['total_deposits']) }}</h6>
                                            <p class="mb-0 small text-muted" style="font-size: 0.7rem;">Tổng xu đã nạp</p>
                                        </div>
                                        <div class="icon-shape bg-light rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: normal; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-wallet" style="font-size: 0.75rem; color: #6c757d;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="card stats-card" style="background: #fff; border: 1px solid #e9ecef;">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold" style="font-size: 1rem; color: #2d3748;">{{ number_format($stats['total_spent']) }}</h6>
                                            <p class="mb-0 small text-muted" style="font-size: 0.7rem;">Tổng xu đã chi</p>
                                        </div>
                                        <div class="icon-shape bg-light rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: normal; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-shopping-cart" style="font-size: 0.75rem; color: #6c757d;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="card stats-card" style="background: #fff; border: 1px solid #e9ecef;">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold" style="font-size: 1rem; color: #2d3748;">{{ number_format($stats['total_withdrawn']) }}</h6>
                                            <p class="mb-0 small text-muted" style="font-size: 0.7rem;">Tổng xu đã rút</p>
                                        </div>
                                        <div class="icon-shape bg-light rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: normal; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-money-bill-wave" style="font-size: 0.75rem; color: #6c757d;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($user->role === 'author')
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="card stats-card" style="background: #fff; border: 1px solid #e9ecef;">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold" style="font-size: 1rem; color: #2d3748;">{{ number_format($stats['author_revenue']) }}</h6>
                                            <p class="mb-0 small text-muted" style="font-size: 0.7rem;">Doanh thu</p>
                                            <small class="text-muted" style="font-size: 0.65rem;">
                                                T: {{ number_format($stats['author_story_revenue']) }} | 
                                                C: {{ number_format($stats['author_chapter_revenue']) }}
                                            </small>
                                        </div>
                                        <div class="icon-shape bg-light rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: normal; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-hand-holding-usd" style="font-size: 0.75rem; color: #6c757d;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Section -->
        <div class="col-12">
            <div class="card" style="border-radius: 16px; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden;">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist" id="userTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#deposits" role="tab">
                            <i class="fas fa-wallet"></i>
                            <span class="d-none d-md-inline">Nạp xu </span><span class="d-md-none">Bank</span>
                            <span class="badge bg-primary rounded-pill ms-1">{{ $counts['deposits'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#paypal-deposits" role="tab">
                            <i class="fab fa-paypal"></i>
                            <span class="d-none d-md-inline">Nạp </span>PayPal
                            <span class="badge bg-primary rounded-pill ms-1">{{ $counts['paypal_deposits'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#card-deposits" role="tab">
                            <i class="fas fa-credit-card"></i>
                            <span>Nạp thẻ</span>
                            <span class="badge bg-primary rounded-pill ms-1">{{ $counts['card_deposits'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#bank-auto-deposits" role="tab">
                            <i class="fas fa-university"></i>
                            <span>Nạp tự động</span>
                            <span class="badge bg-primary rounded-pill ms-1">{{ $counts['bank_auto_deposits'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#story-purchases" role="tab">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Mua truyện</span>
                            <span class="badge bg-primary rounded-pill ms-1">{{ $counts['story_purchases'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#chapter-purchases" role="tab">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Mua chương</span>
                            <span class="badge bg-primary rounded-pill ms-1">{{ $counts['chapter_purchases'] }}</span>
                        </a>
                    </li>
                    @if($user->role === 'author')
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#author-stories" role="tab">
                            <i class="fas fa-book"></i>
                            <span>Danh sách truyện</span>
                            <span class="badge bg-primary rounded-pill ms-1">{{ $counts['author_stories'] ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#author-chapter-earnings" role="tab">
                            <i class="fas fa-hand-holding-usd"></i>
                            <span class="d-none d-md-inline">Thu nhập </span><span>Chương</span>
                            <span class="badge bg-success rounded-pill ms-1">{{ $counts['author_chapter_earnings'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#author-story-earnings" role="tab">
                            <i class="fas fa-coins"></i>
                            <span class="d-none d-md-inline">Thu nhập </span><span>Truyện</span>
                            <span class="badge bg-success rounded-pill ms-1">{{ $counts['author_story_earnings'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#author-featured-stories" role="tab">
                            <i class="fas fa-star"></i>
                            <span>Đề cử</span>
                            <span class="badge bg-warning rounded-pill ms-1">{{ $counts['author_featured_stories'] }}</span>
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#bookmarks" role="tab">
                            <i class="fas fa-bookmark"></i>
                            <span>Theo dõi</span>
                            <span class="badge bg-primary rounded-pill ms-1">{{ $counts['bookmarks'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#user-daily-tasks" role="tab">
                            <i class="fas fa-tasks"></i>
                            <span>Nhiệm vụ</span>
                            <span class="badge bg-primary rounded-pill ms-1">{{ $counts['user_daily_tasks'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#withdrawal-requests" role="tab">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Rút tiền</span>
                            <span class="badge bg-primary rounded-pill ms-1">{{ $counts['withdrawal_requests'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#coin-transactions" role="tab">
                            <i class="fas fa-coins"></i>
                            <span class="d-none d-md-inline">Cộng/Trừ </span><span>Xu</span>
                            <span class="badge bg-primary rounded-pill ms-1">{{ $counts['coin_transactions'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#coin-history" role="tab">
                            <i class="fas fa-history"></i>
                            <span>Lịch sử</span>
                            <span class="badge bg-info rounded-pill ms-1">{{ $counts['coin_histories'] ?? 0 }}</span>
                        </a>
                    </li>
                </ul>
                
                <!-- Tab panes -->
                <div class="tab-content">
                        <!-- Deposits Tab -->
                        <div class="tab-pane active" id="deposits" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
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
                                            <th class="text-center">Hành động</th>
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
                                                <td class="text-center">
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#viewDepositModal{{ $deposit->id }}"
                                                       class="btn btn-link text-info text-gradient px-2 mb-0">
                                                        <i class="far fa-eye"></i>
                                                    </a>
                                                    @if($deposit->status === 'rejected' && $deposit->note)
                                                        <button type="button" class="btn btn-link text-danger text-gradient px-2 mb-0"
                                                                data-bs-toggle="modal" data-bs-target="#noteDepositModal{{ $deposit->id }}">
                                                            <i class="fas fa-info-circle"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">Chưa có giao dịch nạp xu</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($deposits->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        <x-pagination :paginator="$deposits" />
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- PayPal Deposits Tab -->
                        <div class="tab-pane" id="paypal-deposits" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Mã giao dịch</th>
                                            <th>Số tiền USD</th>
                                            <th>Số xu</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày nạp</th>
                                            <th>Ngày duyệt</th>
                                            <th class="text-center">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($paypalDeposits as $deposit)
                                            <tr>
                                                <td>{{ $deposit->id }}</td>
                                                <td>{{ $deposit->transaction_code }}</td>
                                                <td>{{ $deposit->usd_amount_formatted }}</td>
                                                <td>{{ $deposit->coins_formatted }}</td>
                                                <td>
                                                    <span class="badge {{ $deposit->status_badge }}">{{ $deposit->status_text }}</span>
                                                </td>
                                                <td>{{ $deposit->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $deposit->processed_at ? $deposit->processed_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                                <td class="text-center">
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#viewPaypalModal{{ $deposit->id }}"
                                                       class="btn btn-link text-info text-gradient px-2 mb-0">
                                                        <i class="far fa-eye"></i>
                                                    </a>
                                                    @if($deposit->status === 'rejected' && $deposit->note)
                                                        <button type="button" class="btn btn-link text-danger text-gradient px-2 mb-0"
                                                                data-bs-toggle="modal" data-bs-target="#notePaypalModal{{ $deposit->id }}">
                                                            <i class="fas fa-info-circle"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">Chưa có giao dịch nạp PayPal</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($paypalDeposits->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        <x-pagination :paginator="$paypalDeposits" />
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Card Deposits Tab -->
                        <div class="tab-pane" id="card-deposits" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Loại thẻ</th>
                                            <th>Serial</th>
                                            <th>Mệnh giá</th>
                                            <th>Số xu</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày nạp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($cardDeposits as $deposit)
                                            <tr>
                                                <td>{{ $deposit->id }}</td>
                                                <td>{{ $deposit->card_type_name }}</td>
                                                <td>{{ $deposit->serial }}</td>
                                                <td>{{ $deposit->amount_formatted }}</td>
                                                <td>{{ $deposit->coins_formatted }}</td>
                                                <td>
                                                    <span class="badge {{ $deposit->status_badge }}">{{ $deposit->status_text }}</span>
                                                </td>
                                                <td>{{ $deposit->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Chưa có giao dịch nạp thẻ</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($cardDeposits->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        <x-pagination :paginator="$cardDeposits" />
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Bank Auto Deposits Tab -->
                        <div class="tab-pane" id="bank-auto-deposits" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Ngân hàng</th>
                                            <th>Mã giao dịch</th>
                                            <th>Số tiền</th>
                                            <th>Số xu</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày nạp</th>
                                            <th>Ngày xử lý</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($bankAutoDeposits as $deposit)
                                            <tr>
                                                <td>{{ $deposit->id }}</td>
                                                <td>{{ $deposit->bankAuto->name ?? 'N/A' }}</td>
                                                <td>{{ $deposit->transaction_code }}</td>
                                                <td>{{ number_format($deposit->amount, 0, ',', '.') }}đ</td>
                                                <td>{{ number_format($deposit->coins, 0, ',', '.') }}</td>
                                                <td>
                                                    @if($deposit->status === 'success')
                                                        <span class="badge bg-success">{{ $deposit->status_text }}</span>
                                                    @elseif($deposit->status === 'failed')
                                                        <span class="badge bg-danger">{{ $deposit->status_text }}</span>
                                                    @elseif($deposit->status === 'pending')
                                                        <span class="badge bg-warning">{{ $deposit->status_text }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $deposit->status_text }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $deposit->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $deposit->processed_at ? $deposit->processed_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">Chưa có giao dịch nạp tự động</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($bankAutoDeposits->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        <x-pagination :paginator="$bankAutoDeposits" />
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Story Purchases Tab -->
                        <div class="tab-pane" id="story-purchases" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
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
                                @if($storyPurchases->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        <x-pagination :paginator="$storyPurchases" />
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Chapter Purchases Tab -->
                        <div class="tab-pane" id="chapter-purchases" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
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
                                            @php
                                                // Cache relationships to avoid multiple accesses
                                                $chapter = $purchase->chapter;
                                                $story = $chapter ? $chapter->story : null;
                                            @endphp
                                            <tr>
                                                <td>{{ $purchase->id }}</td>
                                                <td>
                                                    <a href="{{ route('stories.show', $story ? $story->id : $purchase->chapter_id) }}">
                                                        {{ $story ? $story->title : 'Không xác định' }}
                                                    </a>
                                                </td>
                                                <td>Chương {{ $chapter ? $chapter->number : 'N/A' }}: {{ $chapter ? Str::limit($chapter->title, 30) : 'N/A' }}</td>
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
                                @if($chapterPurchases->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        <x-pagination :paginator="$chapterPurchases" />
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        @if($user->role === 'author')
                        <!-- Author Stories Tab -->
                        <div class="tab-pane" id="author-stories" role="tabpanel">
                            @include('admin.pages.users.partials.author-stories-table', ['data' => $authorStories])
                            @if($authorStories->hasPages())
                                <div class="d-flex justify-content-center mt-3">
                                    <x-pagination :paginator="$authorStories" />
                                </div>
                            @endif
                        </div>
                        
                        <!-- Author Chapter Earnings Tab -->
                        <div class="tab-pane" id="author-chapter-earnings" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Người mua</th>
                                            <th>Truyện</th>
                                            <th>Chương</th>
                                            <th>Số xu nhận</th>
                                            <th>Ngày mua</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($authorChapterEarnings as $earning)
                                            <tr>
                                                <td>{{ $earning->id }}</td>
                                                <td>{{ $earning->user->name }}</td>
                                                <td>
                                                    <a href="{{ route('stories.show', $earning->chapter->story_id) }}">
                                                        {{ $earning->chapter->story->title ?? 'Không xác định' }}
                                                    </a>
                                                </td>
                                                <td>Chương {{ $earning->chapter->number }}: {{ Str::limit($earning->chapter->title, 30) }}</td>
                                                <td class="text-success fw-bold">+{{ number_format($earning->amount_received) }} xu</td>
                                                <td>{{ $earning->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Chưa có thu nhập từ chương</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($authorChapterEarnings->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        <x-pagination :paginator="$authorChapterEarnings" />
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Author Story Earnings Tab -->
                        <div class="tab-pane" id="author-story-earnings" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Người mua</th>
                                            <th>Truyện</th>
                                            <th>Số xu nhận</th>
                                            <th>Ngày mua</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($authorStoryEarnings as $earning)
                                            <tr>
                                                <td>{{ $earning->id }}</td>
                                                <td>{{ $earning->user->name }}</td>
                                                <td>
                                                    <a href="{{ route('stories.show', $earning->story_id) }}">
                                                        {{ $earning->story->title ?? 'Không xác định' }}
                                                    </a>
                                                </td>
                                                <td class="text-success fw-bold">+{{ number_format($earning->amount_received) }} xu</td>
                                                <td>{{ $earning->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Chưa có thu nhập từ truyện</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($authorStoryEarnings->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        <x-pagination :paginator="$authorStoryEarnings" />
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Author Featured Stories Tab -->
                        <div class="tab-pane" id="author-featured-stories" role="tabpanel">
                            @include('admin.pages.users.partials.author-featured-stories-table', ['data' => $authorFeaturedStories])
                            @if($authorFeaturedStories->hasPages())
                                <div class="d-flex justify-content-center mt-3">
                                    <x-pagination :paginator="$authorFeaturedStories" />
                                </div>
                            @endif
                        </div>
                        @endif
                        
                        <!-- Bookmarks Tab -->
                        <div class="tab-pane" id="bookmarks" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
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
                                @if($bookmarks->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        <x-pagination :paginator="$bookmarks" />
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- User Daily Tasks Tab -->
                        <div class="tab-pane" id="user-daily-tasks" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nhiệm vụ</th>
                                            <th>Ngày thực hiện</th>
                                            <th>Số lần hoàn thành</th>
                                            <th>Lần cuối</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($userDailyTasks as $task)
                                            <tr>
                                                <td>{{ $task->id }}</td>
                                                <td>{{ $task->dailyTask->name ?? 'N/A' }}</td>
                                                <td>{{ $task->task_date->format('d/m/Y') }}</td>
                                                <td>{{ $task->completed_count }}</td>
                                                <td>{{ $task->last_completed_at ? $task->last_completed_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Chưa có nhiệm vụ nào</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($userDailyTasks->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        <x-pagination :paginator="$userDailyTasks" />
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Withdrawal Requests Tab -->
                        <div class="tab-pane" id="withdrawal-requests" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Số xu</th>
                                            <th>Phí</th>
                                            <th>Số tiền thực nhận</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày yêu cầu</th>
                                            <th>Ngày xử lý</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($withdrawalRequests as $request)
                                            <tr>
                                                <td>{{ $request->id }}</td>
                                                <td>{{ number_format($request->coins) }} xu</td>
                                                <td>{{ number_format($request->fee) }} xu</td>
                                                <td>{{ number_format($request->net_amount) }} xu</td>
                                                <td>
                                                    @if($request->status === 'pending')
                                                        <span class="badge bg-warning">Chờ duyệt</span>
                                                    @elseif($request->status === 'approved')
                                                        <span class="badge bg-success">Đã duyệt</span>
                                                    @elseif($request->status === 'rejected')
                                                        <span class="badge bg-danger">Từ chối</span>
                                                    @endif
                                                </td>
                                                <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $request->processed_at ? $request->processed_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Chưa có yêu cầu rút tiền</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($withdrawalRequests->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        <x-pagination :paginator="$withdrawalRequests" />
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
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
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
                                @if($coinTransactions->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        <x-pagination :paginator="$coinTransactions" />
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Coin History Tab -->
                        <div class="tab-pane" id="coin-history" role="tabpanel">
                            <div class="table-responsive mt-3">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Thời gian</th>
                                            <th>Loại giao dịch</th>
                                            <th>Mô tả</th>
                                            <th>Số xu</th>
                                            <th>Số dư trước</th>
                                            <th>Số dư sau</th>
                                            <th>IP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($coinHistories as $history)
                                            <tr>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span>{{ $history->created_at->format('d/m/Y') }}</span>
                                                        <small class="text-muted">{{ $history->created_at->format('H:i:s') }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $history->type == 'add' ? 'success' : 'danger' }}">
                                                        {{ $history->transaction_type_label }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span>{{ $history->description }}</span>
                                                        @if($history->reference)
                                                            <small class="text-muted">
                                                                Tham chiếu: {{ class_basename($history->reference_type) }} #{{ $history->reference_id }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-{{ $history->type == 'add' ? 'success' : 'danger' }}">
                                                        {{ $history->formatted_amount }} xu
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold">{{ number_format($history->balance_before) }} xu</span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold">{{ number_format($history->balance_after) }} xu</span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $history->ip_address }}</small>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                                        <p>Chưa có lịch sử xu nào</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                
                                <!-- Pagination -->
                                @if($coinHistories->hasPages())
                                    <div class="d-flex justify-content-center mt-4">
                                        <x-pagination :paginator="$coinHistories" />
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modals for Deposits -->
    @foreach($deposits as $deposit)
        <!-- Unified View Modal with actions -->
        <div class="modal fade" id="viewDepositModal{{ $deposit->id }}" tabindex="-1" role="dialog" aria-labelledby="viewDepositModalLabel{{ $deposit->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewDepositModalLabel{{ $deposit->id }}">Chi tiết giao dịch nạp xu</h5>
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
                                            <button type="button" class="btn bg-gradient-danger" id="rejectDepositAction{{ $deposit->id }}">
                                                <i class="fas fa-times me-2"></i>Từ chối
                                            </button>
                                        </div>
                                        <div class="col-6 d-grid">
                                            <button type="button" class="btn bg-gradient-success" id="approveDepositAction{{ $deposit->id }}">
                                                <i class="fas fa-check me-2"></i>Duyệt
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Hidden forms for actions -->
                                    <form action="{{ route('deposits.approve', $deposit) }}" method="POST" class="d-none" id="approveDepositForm{{ $deposit->id }}">
                                        @csrf
                                        <button type="submit" id="approveDepositBtn{{ $deposit->id }}">
                                            <span class="btn-text">Duyệt</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                    </form>
                                    <form action="{{ route('deposits.reject', $deposit) }}" method="POST" class="d-none" id="rejectDepositForm{{ $deposit->id }}">
                                        @csrf
                                        <input type="hidden" name="note" id="rejectDepositNote{{ $deposit->id }}">
                                        <button type="submit" id="rejectDepositBtn{{ $deposit->id }}">
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
            <div class="modal fade" id="noteDepositModal{{ $deposit->id }}" tabindex="-1" role="dialog" aria-labelledby="noteDepositModalLabel{{ $deposit->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="noteDepositModalLabel{{ $deposit->id }}">Lý do từ chối giao dịch</h5>
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

    <!-- Modals for PayPal Deposits -->
    @foreach($paypalDeposits as $deposit)
        <!-- Unified View Modal with actions -->
        <div class="modal fade" id="viewPaypalModal{{ $deposit->id }}" tabindex="-1" role="dialog" aria-labelledby="viewPaypalModalLabel{{ $deposit->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewPaypalModalLabel{{ $deposit->id }}">Chi tiết nạp PayPal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-2"><strong>Mã giao dịch:</strong> <span class="text-danger fw-bold">{{ $deposit->transaction_code }}</span></div>
                                <div class="mb-2"><strong>Người dùng:</strong> {{ $deposit->user->name }} ({{ $deposit->user->email }})</div>
                                <div class="mb-2"><strong>Email PayPal:</strong> {{ $deposit->paypal_email }}</div>
                                <div class="mb-2"><strong>Số tiền USD:</strong> {{ $deposit->usd_amount_formatted }}</div>
                                <div class="mb-2"><strong>Số tiền VND:</strong> <span class="text-danger fw-bold">{{ $deposit->vnd_amount_formatted }}</span></div>
                                <div class="mb-2"><strong>Số xu:</strong> <span class="text-danger fw-bold">{{ $deposit->coins_formatted }} xu</span></div>
                                <div class="mb-2"><strong>Trạng thái:</strong> {{ $deposit->status_text }}</div>
                                <div class="mb-2"><strong>Thời gian:</strong> {{ $deposit->created_at->format('d/m/Y H:i') }}</div>
                                @if($deposit->processed_at)
                                    <div class="mb-2"><strong>Xử lý lúc:</strong> {{ $deposit->processed_at->format('d/m/Y H:i') }}</div>
                                @endif
                                @if($deposit->status === 'rejected' && $deposit->note)
                                    <div class="alert alert-danger mt-2 mb-0"><strong>Lý do từ chối:</strong> {{ $deposit->note }}</div>
                                @endif

                                @if($deposit->status === 'processing')
                                    <hr class="my-3">
                                    <div class="row g-2">
                                        <div class="col-6 d-grid">
                                            <button type="button" class="btn bg-gradient-danger" id="rejectPaypalDepositAction{{ $deposit->id }}">
                                                <i class="fas fa-times me-2"></i>Từ chối
                                            </button>
                                        </div>
                                        <div class="col-6 d-grid">
                                            <button type="button" class="btn bg-gradient-success" id="approvePaypalDepositAction{{ $deposit->id }}">
                                                <i class="fas fa-check me-2"></i>Duyệt
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Hidden forms for actions -->
                                    <form action="{{ route('admin.paypal-deposits.approve', $deposit) }}" method="POST" class="d-none" id="approvePaypalDepositForm{{ $deposit->id }}">
                                        @csrf
                                        <button type="submit" id="approvePaypalDepositBtn{{ $deposit->id }}">
                                            <span class="btn-text">Duyệt</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.paypal-deposits.reject', $deposit) }}" method="POST" class="d-none" id="rejectPaypalDepositForm{{ $deposit->id }}">
                                        @csrf
                                        <input type="hidden" name="note" id="rejectPaypalDepositNote{{ $deposit->id }}">
                                        <button type="submit" id="rejectPaypalDepositBtn{{ $deposit->id }}">
                                            <span class="btn-text">Từ chối</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <div class="col-md-6 text-center">
                                @if($deposit->image)
                                    <img src="{{ Storage::url($deposit->image) }}" class="img-fluid" alt="Chứng minh PayPal">
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
        @if($deposit->status === 'rejected' && $deposit->note)
            <div class="modal fade" id="notePaypalModal{{ $deposit->id }}" tabindex="-1" role="dialog" aria-labelledby="notePaypalModalLabel{{ $deposit->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="notePaypalModalLabel{{ $deposit->id }}">Ghi chú giao dịch</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6 class="text-sm">Mã giao dịch: <span class="text-dark">{{ $deposit->transaction_code }}</span></h6>
                                    <h6 class="text-sm">Người dùng: <span class="text-dark">{{ $deposit->user->name }}</span></h6>
                                    <h6 class="text-sm">Trạng thái: <span class="text-dark">{{ $deposit->status_text }}</span></h6>
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
                            <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
    
    <!-- Back Button -->
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
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
            function getActiveTab() {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get('tab') || window.location.hash.replace('#', '') || 'deposits';
            }
            
            const activeTab = getActiveTab();
            if (activeTab) {
                const triggerEl = document.querySelector(`a[href="#${activeTab}"]`);
                if (triggerEl) {
                    const tab = new bootstrap.Tab(triggerEl);
                    tab.show();
                }
            }
            
            const tabLinks = document.querySelectorAll('.nav-link');
            tabLinks.forEach(link => {
                link.addEventListener('click', function() {
                    const tabId = this.getAttribute('href').replace('#', '');
                    const currentUrl = new URL(window.location);
                    currentUrl.searchParams.set('tab', tabId);
                    window.history.pushState({}, '', currentUrl);
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

    <!-- SweetAlert2 for Deposits and PayPal -->
    <script>
        // Ensure SweetAlert2 is available
        (function ensureSwal(){
            if (typeof Swal === 'undefined') {
                var s=document.createElement('script');
                s.src='https://cdn.jsdelivr.net/npm/sweetalert2@11';
                document.head.appendChild(s);
            }
        })();

        $(document).ready(function() {
            // Approve Deposit action with Swal
            $(document).on('click', '[id^="approveDepositAction"]', function() {
                const id = $(this).attr('id').replace('approveDepositAction', '');
                const form = document.getElementById('approveDepositForm' + id);
                const button = $('#approveDepositAction' + id);
                if (typeof Swal !== 'undefined') {
                    const targetEl = document.getElementById('viewDepositModal' + id) || document.body;
                    Swal.fire({
                        icon: 'question',
                        title: 'Xác nhận duyệt giao dịch?',
                        text: 'Người dùng sẽ được cộng xu vào tài khoản.',
                        showCancelButton: true,
                        cancelButtonText: 'Hủy',
                        confirmButtonText: 'Duyệt',
                        reverseButtons: true,
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

            // Reject Deposit action with Swal + input reason
            $(document).on('click', '[id^="rejectDepositAction"]', function() {
                const id = $(this).attr('id').replace('rejectDepositAction', '');
                const form = document.getElementById('rejectDepositForm' + id);
                const noteInput = document.getElementById('rejectDepositNote' + id);
                const button = $('#rejectDepositAction' + id);
                if (typeof Swal !== 'undefined') {
                    const targetEl = document.getElementById('viewDepositModal' + id) || document.body;
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
                        reverseButtons: true,
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

            // Approve PayPal Deposit action with Swal
            $(document).on('click', '[id^="approvePaypalDepositAction"]', function() {
                const id = $(this).attr('id').replace('approvePaypalDepositAction', '');
                const form = document.getElementById('approvePaypalDepositForm' + id);
                const button = $('#approvePaypalDepositAction' + id);
                const targetEl = document.getElementById('viewPaypalModal' + id) || document.body;
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

            // Reject PayPal Deposit action with Swal + input
            $(document).on('click', '[id^="rejectPaypalDepositAction"]', function() {
                const id = $(this).attr('id').replace('rejectPaypalDepositAction', '');
                const form = document.getElementById('rejectPaypalDepositForm' + id);
                const noteInput = document.getElementById('rejectPaypalDepositNote' + id);
                const button = $('#rejectPaypalDepositAction' + id);
                const targetEl = document.getElementById('viewPaypalModal' + id) || document.body;
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
