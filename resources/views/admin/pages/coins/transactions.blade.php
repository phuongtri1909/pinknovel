@extends('admin.layouts.app')

@section('content-auth')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Tổng xu đã cộng</p>
                                <h5 class="font-weight-bolder mb-0 text-success">
                                    {{ number_format($stats['total_add']) }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="fas fa-plus text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Tổng xu đã trừ</p>
                                <h5 class="font-weight-bolder mb-0 text-danger">
                                    {{ number_format($stats['total_subtract']) }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                <i class="fas fa-minus text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Tổng giao dịch</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($stats['total_transactions']) }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="fas fa-exchange-alt text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Giao dịch hôm nay</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($stats['today_transactions']) }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                <i class="fas fa-calendar-day text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mb-3">
                        <h6 class="mb-0">Lịch sử kiểm soát xu thủ công</h6>
                        <a href="{{ route('coins.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i><span class="d-none d-md-inline">Thêm giao dịch</span><span class="d-md-none">Thêm</span>
                        </a>
                    </div>

                    <!-- Filters -->
                    <form method="GET" action="{{ route('coin.transactions') }}" class="mb-3">
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <input type="text" class="form-control form-control-sm" name="search"
                                   placeholder="Tìm kiếm..." value="{{ request('search') }}">
                            <select name="type" class="form-select form-select-sm">
                                <option value="">Tất cả loại</option>
                                <option value="add" {{ request('type') == 'add' ? 'selected' : '' }}>Cộng xu</option>
                                <option value="subtract" {{ request('type') == 'subtract' ? 'selected' : '' }}>Trừ xu</option>
                            </select>
                            <input type="date" class="form-control form-control-sm" name="date_from"
                                   value="{{ request('date_from') }}" placeholder="Từ ngày">
                            <input type="date" class="form-control form-control-sm" name="date_to"
                                   value="{{ request('date_to') }}" placeholder="Đến ngày">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search me-2"></i><span class="d-none d-md-inline">Tìm kiếm</span>
                                </button>
                                <a href="{{ route('coin.transactions') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-refresh me-2"></i><span class="d-none d-md-inline">Làm mới</span>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-xxs font-weight-bolder d-none d-md-table-cell">ID</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2">Người dùng</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2">Loại giao dịch</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2">Số xu</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2 d-none d-lg-table-cell">Quản trị viên</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2 d-none d-md-table-cell">Ghi chú</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2 d-none d-md-table-cell">Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                <tr>
                                    <td class="d-none d-md-table-cell">
                                        <p class="text-sm font-weight-bold mb-0">#{{ $transaction->id }}</p>
                                    </td>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div>
                                                <img src="{{ $transaction->user->avatar ? Storage::url($transaction->user->avatar) : asset('assets/images/avatar_default.jpg') }}"
                                                     class="avatar avatar-sm me-3">
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $transaction->user->name }}</h6>
                                                <small class="text-xs text-muted d-md-none">{{ Str::limit($transaction->user->email, 20) }}</small>
                                                <p class="text-xs text-secondary mb-0 d-none d-md-block">{{ $transaction->user->email }}</p>
                                                <small class="text-xs text-muted d-md-none">{{ $transaction->created_at->format('d/m/Y') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type === 'add' ? 'success' : 'danger' }}">
                                            <i class="fas fa-{{ $transaction->type === 'add' ? 'plus' : 'minus' }} me-1"></i>
                                            <span class="d-none d-md-inline">{{ $transaction->type === 'add' ? 'Cộng xu' : 'Trừ xu' }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0 text-{{ $transaction->type === 'add' ? 'success' : 'danger' }}">
                                            {{ $transaction->type === 'add' ? '+' : '-' }}{{ number_format($transaction->amount) }}
                                        </p>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $transaction->admin->avatar ? Storage::url($transaction->admin->avatar) : asset('assets/images/avatar_default.jpg') }}"
                                                 class="avatar avatar-xs me-2">
                                            <p class="text-sm font-weight-bold mb-0">{{ $transaction->admin->name }}</p>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <p class="text-sm mb-0" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $transaction->note ?: 'Không có ghi chú' }}">
                                            {{ $transaction->note ?: 'Không có ghi chú' }}
                                        </p>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <p class="text-sm font-weight-bold mb-0">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $transaction->created_at->diffForHumans() }}</p>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-inbox text-muted mb-2" style="font-size: 3rem;"></i>
                                            <p class="text-muted mb-0">Chưa có giao dịch nào</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        <x-pagination :paginator="$transactions" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .avatar-xs {
        width: 24px;
        height: 24px;
    }
</style>
@endpush
