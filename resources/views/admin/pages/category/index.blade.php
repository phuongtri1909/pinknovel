@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">Danh sách thể loại</h5>
                        </div>
                        <a href="{{ route('categories.create') }}" class="btn bg-gradient-primary btn-sm mb-0">
                            <i class="fas fa-plus"></i> Thêm thể loại
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tên thể loại</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Slug</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mô tả</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thể loại chính</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0">{{ $category->id }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $category->name }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $category->slug }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $category->description }}</p>
                                        </td>
                                        <td class="text-center">
                                            @if($category->is_main)
                                                <span class="badge badge-sm bg-gradient-success">Có</span>
                                            @else
                                                <span class="badge badge-sm bg-gradient-secondary">Không</span>
                                            @endif
                                        </td>
                                        <td class="text-center d-flex flex-column">
                                            <a href="{{ route('categories.show', $category->id) }}" class="mx-3 text-info" title="Xem truyện">
                                                <i class="fas fa-eye text-info"></i> xem truyện
                                            </a>
                                            <a href="{{ route('categories.edit', $category->id) }}" class="mx-3" title="Sửa">
                                                <i class="fas fa-pencil-alt"></i> sửa
                                            </a>
                                            @include('admin.pages.components.delete-form', [
                                                'id' => $category->id,
                                                'route' => route('categories.destroy', $category->id)
                                            ])
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="px-4 pt-4">
                            <x-pagination :paginator="$categories" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection