@extends('admin.layouts.app')

@section('title', 'Quản lý Ngân hàng')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                        <h4 class="mb-0">Danh sách Ngân hàng</h4>
                        <a href="{{ route('admin.banks.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus-circle me-2"></i><span class="d-none d-md-inline">Thêm Ngân hàng</span><span class="d-md-none">Thêm</span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @include('admin.pages.components.success-error')
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="d-none d-md-table-cell" width="60">ID</th>
                                    <th width="80">Logo</th>
                                    <th>Tên Ngân hàng</th>
                                    <th class="d-none d-lg-table-cell">Mã</th>
                                    <th>Số tài khoản</th>
                                    <th>Chủ tài khoản</th>
                                    <th class="d-none d-md-table-cell" width="80">QR Code</th>
                                    <th width="80">Trạng thái</th>
                                    <th width="130">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($banks as $bank)
                                <tr>
                                    <td class="d-none d-md-table-cell">{{ $bank->id }}</td>
                                    <td>
                                        @if($bank->logo)
                                            <img src="{{ Storage::url($bank->logo) }}" alt="{{ $bank->name }}" class="img-thumbnail" style="max-height: 40px;">
                                        @else
                                            <div class="text-center bg-light p-2 rounded">
                                                <i class="fas fa-university text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $bank->name }}</td>
                                    <td class="d-none d-lg-table-cell">{{ $bank->code }}</td>
                                    <td>{{ $bank->account_number }}</td>
                                    <td>{{ $bank->account_name }}</td>
                                    <td class="d-none d-md-table-cell">
                                        @if($bank->qr_code)
                                            <a href="{{ Storage::url($bank->qr_code) }}" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-qrcode"></i>
                                            </a>
                                        @else
                                            <span class="badge badge-light">Không có</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($bank->status)
                                            <span class="badge badge-success">Hoạt động</span>
                                        @else
                                            <span class="badge badge-danger">Vô hiệu</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <a href="{{ route('admin.banks.edit', $bank->id) }}" class="btn btn-sm btn-info" title="Sửa">
                                                <i class="fas fa-edit"></i><span class="d-none d-md-inline ms-1">Sửa</span>
                                            </a>
                                            <form action="{{ route('admin.banks.destroy', $bank->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa ngân hàng này?')" title="Xóa">
                                                    <i class="fas fa-trash"></i><span class="d-none d-md-inline ms-1">Xóa</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-university fa-3x mb-3 text-muted"></i>
                                            <p class="mb-0">Chưa có ngân hàng nào được thêm vào</p>
                                            <a href="{{ route('admin.banks.create') }}" class="btn btn-primary mt-3 btn-sm">
                                                <i class="fas fa-plus-circle me-2"></i><span class="d-none d-md-inline">Thêm Ngân hàng</span><span class="d-md-none">Thêm</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 