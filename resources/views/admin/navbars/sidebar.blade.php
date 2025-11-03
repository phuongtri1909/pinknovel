<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 "
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        @php
          
            $logoPath =
                $logoSite && $logoSite->logo
                    ? Storage::url($logoSite->logo)
                    : asset('assets/images/logo/logo_site.webp');
        @endphp
        <a class="d-flex m-0 justify-content-center text-wrap" href="{{ route('home') }}">
            <img height="70" class="logof_site" src="{{ $logoPath }}" alt="{{ config('app.name') }} logo">
        </a>
    </div>
    <hr class="horizontal dark mt-0">

    <div class="docs-info">
        <a href="{{ route('home') }}" class="btn btn-white btn-sm w-100 mb-0">Trang chủ</a>
    </div>

    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            {{-- Dashboard --}}
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteNamed('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}" data-menu="dashboard">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <svg width="12px" height="12px" viewBox="0 0 45 40" version="1.1"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <g transform="translate(-1716.000000, -439.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                    <g transform="translate(1716.000000, 291.000000)">
                                        <g transform="translate(0.000000, 148.000000)">
                                            <path class="color-background opacity-6"
                                                d="M46.7199583,10.7414583 L40.8449583,0.949791667 C40.4909749,0.360605034 39.8540131,0 39.1666667,0 L7.83333333,0 C7.1459869,0 6.50902508,0.360605034 6.15504167,0.949791667 L0.280041667,10.7414583 C0.0969176761,11.0460037 -1.23209662e-05,11.3946378 -1.23209662e-05,11.75 C-0.00758042603,16.0663731 3.48367543,19.5725301 7.80004167,19.5833333 L7.81570833,19.5833333 C9.75003686,19.5882688 11.6168794,18.8726691 13.0522917,17.5760417 C16.0171492,20.2556967 20.5292675,20.2556967 23.494125,17.5760417 C26.4604562,20.2616016 30.9794188,20.2616016 33.94575,17.5760417 C36.2421905,19.6477597 39.5441143,20.1708521 42.3684437,18.9103691 C45.1927731,17.649886 47.0084685,14.8428276 47.0000295,11.75 C47.0000295,11.3946378 46.9030823,11.0460037 46.7199583,10.7414583 Z">
                                            </path>
                                            <path class="color-background"
                                                d="M39.198,22.4912623 C37.3776246,22.4928106 35.5817531,22.0149171 33.951625,21.0951667 L33.92225,21.1107282 C31.1430221,22.6838032 27.9255001,22.9318916 24.9844167,21.7998837 C24.4750389,21.605469 23.9777983,21.3722567 23.4960833,21.1018359 L23.4745417,21.1129513 C20.6961809,22.6871153 17.4786145,22.9344611 14.5386667,21.7998837 C14.029926,21.6054643 13.533337,21.3722507 13.0522917,21.1018359 C11.4250962,22.0190609 9.63246555,22.4947009 7.81570833,22.4912623 C7.16510551,22.4842162 6.51607673,22.4173045 5.875,22.2911849 L5.875,44.7220845 C5.875,45.9498589 6.7517757,46.9451667 7.83333333,46.9451667 L19.5833333,46.9451667 L19.5833333,33.6066734 L27.4166667,33.6066734 L27.4166667,46.9451667 L39.1666667,46.9451667 C40.2482243,46.9451667 41.125,45.9498589 41.125,44.7220845 L41.125,22.2822926 C40.4887822,22.4116582 39.8442868,22.4815492 39.198,22.4912623 Z">
                                            </path>
                                        </g>
                                    </g>
                                </g>
                            </g>
                        </svg>
                    </div>
                    <span class="nav-link-text ms-1">{{ __('dashboard') }}</span>
                </a>
            </li>

            {{-- Truyện --}}
            @php
                $storiesMenuActive = Route::currentRouteNamed('stories.*', 'stories.chapters.*', 'categories.*', 
                    'admin.story-transfer.*', 'admin.story-reviews.*', 'admin.edit-requests.*', 
                    'admin.author-applications.*', 'comments.all');
            @endphp
            <li class="nav-item mt-2">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Quản lý</h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ $storiesMenuActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" 
                    href="#storiesSubmenu" role="button" aria-expanded="{{ $storiesMenuActive ? 'true' : 'false' }}" 
                    aria-controls="storiesSubmenu">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-book text-dark icon-sidebar"></i>
                    </div>
                    <span class="nav-link-text ms-1">Truyện</span>
                    <i class="fas fa-chevron-down text-xs opacity-5"></i>
                </a>
                <div class="collapse mt-1 {{ $storiesMenuActive ? 'show' : '' }}" id="storiesSubmenu" style="margin-left: 15px">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('stories.*', 'stories.chapters.*') ? 'active' : '' }}"
                                href="{{ route('stories.index') }}" data-menu="stories-list">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-layer-group text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Danh sách truyện</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('comments.all') ? 'active' : '' }}"
                                href="{{ route('comments.all') }}" data-menu="comments">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-comments text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Quản lý Bình luận
                                    @php
                                        $pendingCommentsCount = \App\Models\Comment::where('approval_status', 'pending')
                                            ->whereHas('user', function($q) {
                                                $q->where('role', '!=', 'admin');
                                            })
                                            ->whereDoesntHave('story', function($q) {
                                                $q->whereColumn('stories.user_id', 'comments.user_id');
                                            })
                                            ->count();
                                    @endphp
                                    @if ($pendingCommentsCount > 0)
                                        <span class="badge bg-danger ms-2">{{ $pendingCommentsCount }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                       
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('admin.story-reviews.*') ? 'active' : '' }}"
                                href="{{ route('admin.story-reviews.index') }}" data-menu="story-reviews">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-check-to-slot text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Duyệt truyện
                                    @php
                                        $pendingStoryCount = \App\Models\Story::where('status', 'pending')->count();
                                    @endphp
                                    @if ($pendingStoryCount > 0)
                                        <span class="badge bg-danger ms-2">{{ $pendingStoryCount }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('admin.edit-requests.*') ? 'active' : '' }}"
                                href="{{ route('admin.edit-requests.index') }}" data-menu="edit-requests">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-pen-to-square text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Duyệt chỉnh sửa
                                    @php
                                        $pendingEditCount = \App\Models\StoryEditRequest::where('status', 'pending')->count();
                                    @endphp
                                    @if ($pendingEditCount > 0)
                                        <span class="badge bg-danger ms-2">{{ $pendingEditCount }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('admin.author-applications.*') ? 'active' : '' }}"
                                href="{{ route('admin.author-applications.index') }}" data-menu="author-applications">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-pen-nib text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Đơn đăng ký tác giả
                                    @php
                                        $pendingCount = \App\Models\AuthorApplication::where('status', 'pending')->count();
                                    @endphp
                                    @if ($pendingCount > 0)
                                        <span class="badge bg-danger ms-2">{{ $pendingCount }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('admin.story-transfer.*') ? 'active' : '' }}"
                                href="{{ route('admin.story-transfer.index') }}" data-menu="story-transfer">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-exchange-alt text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Chuyển nhượng truyện</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('categories.*') ? 'active' : '' }}"
                                href="{{ route('categories.index') }}" data-menu="categories">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-book text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Danh sách thể loại</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Tài chính --}}
            @php
                $financeMenuActive = Route::currentRouteNamed('deposits.*', 'admin.card-deposits.*', 
                    'admin.paypal-deposits.*', 'admin.withdrawals.*', 'admin.banks.*', 'request.payments.*', 'admin.request-payment-paypal.*');
            @endphp
            <li class="nav-item">
                <a class="nav-link {{ $financeMenuActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" 
                    href="#financeSubmenu" role="button" aria-expanded="{{ $financeMenuActive ? 'true' : 'false' }}" 
                    aria-controls="financeSubmenu">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-money-bill-transfer text-dark icon-sidebar"></i>
                    </div>
                    <span class="nav-link-text ms-1">Chuyển Xu</span>
                    <i class="fas fa-chevron-down text-xs opacity-5"></i>
                </a>
                <div class="collapse mt-1 {{ $financeMenuActive ? 'show' : '' }}" id="financeSubmenu" style="margin-left: 15px">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('deposits.*', 'request.payments.*') ? 'active' : '' }}"
                                href="{{ route('deposits.index') }}" data-menu="deposits">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-university text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Nạp xu - Bank
                                    @php
                                        $pendingDepositsCount = \App\Models\Deposit::where('status', 'pending')->count();
                                    @endphp
                                    @if ($pendingDepositsCount > 0)
                                        <span class="badge bg-danger ms-2">{{ $pendingDepositsCount }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('admin.card-deposits.*') ? 'active' : '' }}"
                                href="{{ route('admin.card-deposits.index') }}" data-menu="card-deposits">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-credit-card text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Nạp xu - Thẻ cào</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('admin.paypal-deposits.*','admin.request-payment-paypal.*') ? 'active' : '' }}"
                                href="{{ route('admin.paypal-deposits.index') }}" data-menu="paypal-deposits">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fab fa-paypal text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Nạp xu - PayPal
                                    @php
                                        $pendingPaypalCount = \App\Models\PaypalDeposit::where('status', 'processing')->count();
                                    @endphp
                                    @if ($pendingPaypalCount > 0)
                                        <span class="badge bg-danger ms-2">{{ $pendingPaypalCount }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('admin.bank-auto-deposits.*') ? 'active' : '' }}"
                                href="{{ route('admin.bank-auto-deposits.index') }}">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-exchange-alt text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Nạp xu - Bank auto
                                    @php
                                        try {
                                            $pendingBankAutoCount = \App\Models\BankAutoDeposit::where('status', 'pending')->count();
                                        } catch (\Exception $e) {
                                            $pendingBankAutoCount = 0;
                                        }
                                    @endphp
                                    @if ($pendingBankAutoCount > 0)
                                        <span class="badge bg-danger ms-2">{{ $pendingBankAutoCount }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}"
                                href="{{ route('admin.withdrawals.index') }}" data-menu="withdrawals">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-money-bill-transfer text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Quản lý rút xu
                                    @php
                                        $pendingWithdrawalsCount = \App\Models\WithdrawalRequest::where(
                                            'status',
                                            'pending',
                                        )->count();
                                    @endphp
                                    @if ($pendingWithdrawalsCount > 0)
                                        <span class="badge bg-danger ms-2">{{ $pendingWithdrawalsCount }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('admin.banks.*') ? 'active' : '' }}"
                                href="{{ route('admin.banks.index') }}" data-menu="banks">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-university text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Quản lý Ngân hàng</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('admin.bank-autos.*') ? 'active' : '' }}"
                                href="{{ route('admin.bank-autos.index') }}">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-robot text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">QL Ngân hàng (Tự động)</span>
                            </a>
                        </li>
            
                        
                    </ul>
                </div>
            </li>

            {{-- Xu --}}
            @php
                $coinMenuActive = Route::currentRouteNamed('coins.*', 'coin.transactions', 'coin-history.index');
            @endphp
            <li class="nav-item">
                <a class="nav-link {{ $coinMenuActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" 
                    href="#coinSubmenu" role="button" aria-expanded="{{ $coinMenuActive ? 'true' : 'false' }}" 
                    aria-controls="coinSubmenu">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-coins text-dark icon-sidebar"></i>
                    </div>
                    <span class="nav-link-text ms-1">Quản lý xu</span>
                    <i class="fas fa-chevron-down text-xs opacity-5"></i>
                </a>
                <div class="collapse mt-1 {{ $coinMenuActive ? 'show' : '' }}" id="coinSubmenu" style="margin-left: 15px">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('coins.*') ? 'active' : '' }}"
                                href="{{ route('coins.index') }}" data-menu="coins">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-coins text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Quản lý xu</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('coin.transactions') ? 'active' : '' }}"
                                href="{{ route('coin.transactions') }}" data-menu="coin-transactions">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-history text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Kiểm soát xu thủ công</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('coin-history.index') ? 'active' : '' }}"
                                href="{{ route('coin-history.index') }}" data-menu="coin-history">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-history text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Lịch sử xu</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteNamed('users.*') ? 'active' : '' }}"
                    href="{{ route('users.index') }}" data-menu="users">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-users text-dark icon-sidebar"></i>
                    </div>
                    <span class="nav-link-text ms-1">Danh sách User</span>
                </a>
            </li>

            {{-- Cài đặt --}}
            @php
                $settingsMenuActive = Route::currentRouteNamed('admin.configs.*', 'logo-site.*', 
                    'admin.daily-tasks.*', 'admin.socials.*', 'admin.guide.*', 'banners.*');
            @endphp
            <li class="nav-item mt-2">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Cài đặt</h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ $settingsMenuActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" 
                    href="#settingsSubmenu" role="button" aria-expanded="{{ $settingsMenuActive ? 'true' : 'false' }}" 
                    aria-controls="settingsSubmenu">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-gears text-dark icon-sidebar"></i>
                    </div>
                    <span class="nav-link-text ms-1">Cài đặt</span>
                    <i class="fas fa-chevron-down text-xs opacity-5"></i>
                </a>
                <div class="collapse mt-1 {{ $settingsMenuActive ? 'show' : '' }}" id="settingsSubmenu" style="margin-left: 15px">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('admin.configs.*') ? 'active' : '' }}"
                                href="{{ route('admin.configs.index') }}" data-menu="configs">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-gears text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Cấu hình hệ thống</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('logo-site.*') ? 'active' : '' }}"
                                href="{{ route('logo-site.edit') }}" data-menu="logo">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-regular fa-images text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Logo</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('banners.*') ? 'active' : '' }}"
                                href="{{ route('banners.index') }}" data-menu="banners">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-regular fa-image text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Banners</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('admin.daily-tasks.*') ? 'active' : '' }}"
                                href="{{ route('admin.daily-tasks.index') }}" data-menu="daily-tasks">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-tasks text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Quản lý nhiệm vụ</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteNamed('admin.socials.*') ? 'active' : '' }}"
                                href="{{ route('admin.socials.index') }}" data-menu="socials">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-share-nodes text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Liên hệ</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.guides.*') ? 'active' : '' }}"
                                href="{{ route('admin.guides.index') }}" data-menu="guide">
                                <div
                                    class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-book text-dark icon-sidebar"></i>
                                </div>
                                <span class="nav-link-text ms-1">Quản lý Hướng dẫn</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Tài khoản --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Tài khoản</h6>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('logout') }}" data-menu="logout">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-right-from-bracket text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Đăng xuất</span>
                </a>
            </li>
        </ul>
    </div>

    @push('scripts-admin')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Keep parent menu open when child menu is active
            const activeChildMenu = document.querySelector('.collapse .nav-link.active');
            if (activeChildMenu) {
                const parentCollapse = activeChildMenu.closest('.collapse');
                if (parentCollapse) {
                    // Show the collapse
                    parentCollapse.classList.add('show');
                    
                    // Update parent link
                    const parentLink = document.querySelector(`[href="#${parentCollapse.id}"]`);
                    if (parentLink) {
                        parentLink.classList.remove('collapsed');
                        parentLink.setAttribute('aria-expanded', 'true');
                        
                        // Update chevron
                        const chevron = parentLink.querySelector('.fa-chevron-down');
                        if (chevron) {
                            chevron.classList.add('rotated');
                        }
                    }
                }
            }

            // Use event delegation for collapse events
            document.addEventListener('show.bs.collapse', function(e) {
                const target = e.target;
                const parentLink = document.querySelector(`[href="#${target.id}"]`);
                if (parentLink) {
                    parentLink.classList.remove('collapsed');
                    parentLink.setAttribute('aria-expanded', 'true');
                    const chevron = parentLink.querySelector('.fa-chevron-down');
                    if (chevron) {
                        chevron.classList.add('rotated');
                    }
                }
            });
            
            document.addEventListener('hide.bs.collapse', function(e) {
                const target = e.target;
                const parentLink = document.querySelector(`[href="#${target.id}"]`);
                if (parentLink) {
                    parentLink.classList.add('collapsed');
                    parentLink.setAttribute('aria-expanded', 'false');
                    const chevron = parentLink.querySelector('.fa-chevron-down');
                    if (chevron) {
                        chevron.classList.remove('rotated');
                    }
                }
            });

            // Initialize chevron states
            const collapseElements = document.querySelectorAll('[data-bs-toggle="collapse"]');
            collapseElements.forEach(element => {
                const targetId = element.getAttribute('href');
                const target = document.querySelector(targetId);
                if (target && target.classList.contains('show')) {
                    element.classList.remove('collapsed');
                    element.setAttribute('aria-expanded', 'true');
                    const chevron = element.querySelector('.fa-chevron-down');
                    if (chevron) {
                        chevron.classList.add('rotated');
                    }
                }
            });

            // Scroll to active menu on page load
            setTimeout(() => {
                const activeMenu = document.querySelector('.nav-link.active');
                if (activeMenu) {
                    const sidenavMain = document.getElementById('sidenav-main');
                    if (sidenavMain) {
                        const activeMenuOffset = activeMenu.offsetTop;
                        const sidenavHeight = sidenavMain.offsetHeight;
                        const scrollPosition = activeMenuOffset - (sidenavHeight / 2) + (activeMenu.offsetHeight / 2);
                        sidenavMain.scrollTo({
                            top: Math.max(0, scrollPosition),
                            behavior: 'smooth'
                        });
                    }
                }
            }, 300);
        });
    </script>
    <style>
        /* Remove list bullets */
        .sidenav .navbar-nav,
        .sidenav .navbar-nav .nav,
        .sidenav .collapse .nav,
        .sidenav .btn-toggle-nav,
        .sidenav .navbar-nav li,
        .sidenav .navbar-nav .nav li,
        .sidenav .collapse .nav li,
        .sidenav .btn-toggle-nav li {
            list-style: none;
            padding-left: 0;
            margin-left: 0;
        }
        
        /* Remove any pseudo-elements that might create dots */
        .sidenav .navbar-nav li::before,
        .sidenav .navbar-nav .nav li::before,
        .sidenav .collapse .nav li::before,
        .sidenav .btn-toggle-nav li::before,
        .sidenav .navbar-nav li::after,
        .sidenav .navbar-nav .nav li::after,
        .sidenav .collapse .nav li::after,
        .sidenav .btn-toggle-nav li::after {
            content: none;
        }
        
        .sidenav .nav .nav-item .nav-link {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .sidenav .nav .nav-item .nav-link .icon,
        .sidenav .btn-toggle-nav .nav-item .nav-link .icon {
            min-width: 2rem;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidenav .nav-link[data-bs-toggle="collapse"] {
            position: relative;
            padding-right: 3rem !important;
        }
        
        /* Hide Bootstrap's automatic chevron (::after pseudo-element) from dashboard CSS */
        .sidenav.navbar-vertical .navbar-nav .nav-link[data-bs-toggle="collapse"]::after,
        .sidenav.navbar-vertical .navbar-nav .nav-link[data-bs-toggle="collapse"]:after,
        .sidenav.navbar-vertical .navbar-nav .nav-link[data-bs-toggle="collapse"][aria-expanded="true"]::after,
        .sidenav.navbar-vertical .navbar-nav .nav-link[data-bs-toggle="collapse"][aria-expanded="true"]:after,
        .sidenav.navbar-vertical .navbar-nav .nav-link[data-bs-toggle="collapse"][aria-expanded="false"]::after,
        .sidenav.navbar-vertical .navbar-nav .nav-link[data-bs-toggle="collapse"][aria-expanded="false"]:after,
        .sidenav .nav-link[data-bs-toggle="collapse"]::after,
        .sidenav .nav-link[data-bs-toggle="collapse"]:after,
        .sidenav .navbar-nav .nav-link[data-bs-toggle="collapse"]::after,
        .sidenav .navbar-nav .nav-link[data-bs-toggle="collapse"]:after {
            display: none !important;
            content: none !important;
            border: none !important;
            font-family: none !important;
            margin-left: 0 !important;
            transform: none !important;
        }
        
        /* Hide any dropdown-toggle chevron from dashboard CSS */
        .sidenav .nav-link.dropdown-toggle::after,
        .sidenav .nav-link.dropdown-toggle:after {
            display: none !important;
            content: none !important;
            border: none !important;
        }
        
        .sidenav .nav-link[data-bs-toggle="collapse"] .fa-chevron-down {
            transition: transform 0.3s ease;
            position: absolute;
            right: 1rem;
            top: 50%;
            margin-top: -0.5em;
        }
        
        .sidenav .nav-link[data-bs-toggle="collapse"] .fa-chevron-down.rotated {
            transform: rotate(180deg);
        }
        
        .sidenav .collapse.show {
            display: block;
        }
        
        .sidenav-main {
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        /* Ensure submenu items display correctly */
        .sidenav .btn-toggle-nav .nav-item .nav-link {
            padding-left: 1rem;
            padding-right: 1rem;
            display: flex;
            align-items: center;
        }
    </style>
    @endpush

</aside>
