@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">Danh sách Banner</h5>
                        </div>
                        <a href="{{ route('banners.create') }}" class="btn bg-gradient-primary btn-sm mb-0">
                            <i class="fas fa-plus"></i> Thêm Banner
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @include('admin.pages.components.success-error')
                    
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Hình ảnh</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Liên kết tới</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Link aff</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($banners as $banner)
                                    <tr>
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0">{{ $banner->id }}</p>
                                        </td>
                                        <td>
                                            <img src="{{ Storage::url($banner->image) }}" alt="Banner" class="img-fluid" style="max-height: 60px;">
                                        </td>
                                        <td>
                                            @if($banner->story)
                                                <p class="text-xs font-weight-bold mb-0">Truyện: {{ $banner->story->title }}</p>
                                            @else
                                                <p class="text-xs font-weight-bold mb-0">
                                                    <a href="{{ $banner->link }}" target="_blank">{{ $banner->link }}</a>
                                                </p>
                                            @endif
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                <a href="{{ $banner->link_aff }}" target="_blank"> {{ Str::limit($banner->link_aff, 20) }}</a>
                                            </p>
                                        </td>
                                        <td>
                                            <span class="badge badge-sm {{ $banner->status ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                                {{ $banner->status ? 'Đang hiển thị' : 'Ẩn' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <a href="{{ route('banners.edit', $banner->id) }}" class="mx-3" title="Sửa">
                                                    <i class="fas fa-pencil-alt"></i>
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