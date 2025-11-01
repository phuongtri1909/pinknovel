@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                        <h5 class="mb-0">Danh sách Banner</h5>
                        <a href="{{ route('banners.create') }}" class="btn bg-gradient-primary btn-sm">
                            <i class="fas fa-plus me-2"></i><span class="d-none d-md-inline">Thêm Banner</span><span class="d-md-none">Thêm</span>
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @include('admin.pages.components.success-error')

                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-xxs font-weight-bolder d-none d-md-table-cell">ID</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder ps-2">Hình ảnh</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder">Liên kết tới</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder d-none d-lg-table-cell">Link aff</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder d-none d-md-table-cell">Trạng thái</th>
                                    <th class="text-center text-uppercase text-xxs font-weight-bolder">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($banners as $banner)
                                    <tr>
                                        <td class="ps-4 d-none d-md-table-cell">
                                            <p class="text-xs font-weight-bold mb-0">{{ $banner->id }}</p>
                                        </td>
                                        <td>
                                            <img src="{{ Storage::url($banner->image) }}" alt="Banner" class="img-fluid" style="max-height: 60px;">
                                        </td>
                                        <td>
                                            @if($banner->story)
                                                <p class="text-xs font-weight-bold mb-0">{{ Str::limit($banner->story->title, 30) }}</p>
                                            @else
                                                <p class="text-xs font-weight-bold mb-0" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $banner->link }}">
                                                    <a href="{{ $banner->link }}" target="_blank">{{ Str::limit($banner->link, 25) }}</a>
                                                </p>
                                            @endif
                                            <span class="badge badge-sm d-md-none {{ $banner->status ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                                {{ $banner->status ? 'Hiển thị' : 'Ẩn' }}
                                            </span>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <p class="text-xs font-weight-bold mb-0" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $banner->link_aff }}">
                                                <a href="{{ $banner->link_aff }}" target="_blank">{{ Str::limit($banner->link_aff, 20) }}</a>
                                            </p>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <span class="badge badge-sm {{ $banner->status ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                                {{ $banner->status ? 'Đang hiển thị' : 'Ẩn' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                                <a href="{{ route('banners.edit', $banner->id) }}" class="btn btn-sm btn-outline-success" title="Sửa">
                                                    <i class="fas fa-pencil-alt me-2"></i><span class="d-none d-md-inline">Sửa</span>
                                                </a>
                                                @include('admin.pages.components.delete-form', [
                                                    'id' => $banner->id,
                                                    'route' => route('banners.destroy', $banner->id)
                                                ])
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="px-4 pt-4">
                            <x-pagination :paginator="$banners" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
