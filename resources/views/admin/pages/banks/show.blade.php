@extends('admin.layouts.app')

@section('title', 'Chi tiết Ngân hàng')

@section('content-auth')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Chi tiết Ngân hàng: {{ $bank->name }}</h4>
                        <div>
                            <a href="{{ route('admin.banks.edit', $bank->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit mr-1"></i> Chỉnh sửa
                            </a>
                            <a href="{{ route('admin.banks.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-arrow-left mr-1"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Thông tin cơ bản</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="150">ID</th>
                                            <td>{{ $bank->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tên ngân hàng</th>
                                            <td>{{ $bank->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Mã ngân hàng</th>
                                            <td>{{ $bank->code }}</td>
                                        </tr>
                                        <tr>
                                            <th>Số tài khoản</th>
                                            <td>{{ $bank->account_number }}</td>
                                        </tr>
                                        <tr>
                                            <th>Chủ tài khoản</th>
                                            <td>{{ $bank->account_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Trạng thái</th>
                                            <td>
                                                @if($bank->status)
                                                    <span class="badge badge-success">Hoạt động</span>
                                                @else
                                                    <span class="badge badge-danger">Vô hiệu</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Ngày tạo</th>
                                            <td>{{ $bank->created_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Cập nhật lần cuối</th>
                                            <td>{{ $bank->updated_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">Logo</h5>
                                        </div>
                                        <div class="card-body text-center">
                                            @if($bank->logo)
                                                <img src="{{ Storage::url($bank->logo) }}" alt="{{ $bank->name }}" class="img-fluid mb-3" style="max-height: 150px;">
                                                <div>
                                                    <a href="{{ Storage::url($bank->logo) }}" target="_blank" class="btn btn-sm btn-info">
                                                        <i class="fas fa-external-link-alt mr-1"></i> Xem ảnh gốc
                                                    </a>
                                                </div>
                                            @else
                                                <div class="text-center py-5 bg-light rounded">
                                                    <i class="fas fa-image fa-4x mb-3 text-muted"></i>
                                                    <p>Chưa có logo</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">Mã QR</h5>
                                        </div>
                                        <div class="card-body text-center">
                                            @if($bank->qr_code)
                                                <img src="{{ Storage::url($bank->qr_code) }}" alt="QR Code" class="img-fluid mb-3" style="max-height: 200px;">
                                                <div>
                                                    <a href="{{ Storage::url($bank->qr_code) }}" target="_blank" class="btn btn-sm btn-info">
                                                        <i class="fas fa-external-link-alt mr-1"></i> Xem ảnh gốc
                                                    </a>
                                                </div>
                                            @else
                                                <div class="text-center py-5 bg-light rounded">
                                                    <i class="fas fa-qrcode fa-4x mb-3 text-muted"></i>
                                                    <p>Chưa có mã QR</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 