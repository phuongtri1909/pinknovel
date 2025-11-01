@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                        <h6 class="mb-0">Danh sách cấu hình hệ thống</h6>
                        <a href="{{ route('admin.configs.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i><span class="d-none d-md-inline">Thêm cấu hình</span><span class="d-md-none">Thêm</span>
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-xxs font-weight-bolder">Khóa</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2 d-none d-lg-table-cell">Giá trị</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2 d-none d-md-table-cell">Mô tả</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($configs as $config)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $config->key }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <p class="text-xs font-weight-bold mb-0" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $config->value }}">
                                            {{ Str::limit($config->value, 50) }}
                                        </p>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <p class="text-xs mb-0" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $config->description ?? 'Không có mô tả' }}">
                                            {{ Str::limit($config->description ?? 'Không có mô tả', 30) }}
                                        </p>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('admin.configs.edit', $config->id) }}" class="btn btn-sm btn-outline-success" title="Chỉnh sửa">
                                                <i class="fas fa-edit me-2"></i><span class="d-none d-md-inline">Sửa</span>
                                            </a>
                                            <form action="{{ route('admin.configs.destroy', $config->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa cấu hình này?')">
                                                    <i class="fas fa-trash me-2"></i><span class="d-none d-md-inline">Xóa</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
