@extends('admin.layouts.app')

@section('title', 'Quản lý yêu cầu rút xu')

@section('content-auth')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Quản lý yêu cầu rút xu</h6>
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    @if(session('success'))
                        <div class="alert alert-success mx-4 mt-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger mx-4 mt-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="filter-container px-4 pt-4">
                        <div class="d-flex mb-3">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}"
                                   class="btn {{ $status == 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Đang chờ
                                    @php
                                        $pendingCount = \App\Models\WithdrawalRequest::where('status', 'pending')->count();
                                    @endphp
                                    @if($pendingCount > 0)
                                        <span class="badge bg-danger ms-2">{{ $pendingCount }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('admin.withdrawals.index', ['status' => 'approved']) }}"
                                   class="btn {{ $status == 'approved' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Đã duyệt
                                </a>
                                <a href="{{ route('admin.withdrawals.index', ['status' => 'rejected']) }}"
                                   class="btn {{ $status == 'rejected' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Đã từ chối
                                </a>
                                <a href="{{ route('admin.withdrawals.index', ['status' => 'all']) }}"
                                   class="btn {{ $status == 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Tất cả
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive p-0 px-4">
                        @if($withdrawalRequests->isEmpty())
                            <div class="text-center py-5">
                                <i class="fa fa-money-bill-transfer fa-3x mb-3 text-muted"></i>
                                <p>Không có yêu cầu rút xu nào {{ $status != 'all' ? 'với trạng thái này' : '' }}</p>
                            </div>
                        @else
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase  text-xxs font-weight-bolder ">#</th>
                                        <th class="text-uppercase  text-xxs font-weight-bolder ">Người dùng</th>
                                        <th class="text-uppercase  text-xxs font-weight-bolder ">Ngày yêu cầu</th>
                                        <th class="text-uppercase  text-xxs font-weight-bolder ">Số xu</th>
                                        <th class="text-uppercase  text-xxs font-weight-bolder ">Phí</th>
                                        <th class="text-uppercase  text-xxs font-weight-bolder ">Thực nhận</th>
                                        <th class="text-uppercase  text-xxs font-weight-bolder ">Trạng thái</th>
                                        <th class="text-uppercase  text-xxs font-weight-bolder ">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($withdrawalRequests as $request)
                                        <tr>
                                            <td class="ps-4">
                                                <p class="text-xs font-weight-bold mb-0">{{ $loop->iteration }}</p>
                                            </td>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div>
                                                        <img src="{{ $request->user->avatar ? Storage::url($request->user->avatar) : asset('assets/images/avatar_default.jpg') }}"
                                                             class="avatar avatar-sm me-3" alt="user avatar">
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $request->user->name }}</h6>
                                                        <p class="text-xs  mb-0">{{ $request->user->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $request->created_at->format('d/m/Y') }}</p>
                                                <p class="text-xs  mb-0">{{ $request->created_at->format('H:i:s') }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ number_format($request->coins) }} xu</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">- {{ number_format($request->fee) }} xu</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ number_format($request->net_amount) }} xu
                                                    <br>
                                                    {{ number_format($request->payment_info['vnd_amount']) }} VND
                                                </p>
                                            </td>

                                            <td>
                                                @if($request->status == 'pending')
                                                    <span class="badge badge-sm bg-gradient-warning">Đang chờ</span>
                                                @elseif($request->status == 'approved')
                                                    <span class="badge badge-sm bg-gradient-success">Đã duyệt</span>
                                                @elseif($request->status == 'rejected')
                                                    <span class="badge badge-sm bg-gradient-danger" data-bs-toggle="tooltip"
                                                          title="{{ $request->rejection_reason }}">
                                                        Đã từ chối
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.withdrawals.show', $request->id) }}"
                                                   class="btn btn-link text-white px-3 mb-0 btn-primary">
                                                    <i class="fas fa-eye text-white me-2"></i>Chi tiết
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="d-flex justify-content-center mt-4">
                                <x-pagination :paginator="$withdrawalRequests" />
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    });
</script>
@endpush
