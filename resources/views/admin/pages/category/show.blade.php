@extends('admin.layouts.app')

@section('content-auth')
<div class="row">
    <div class="col-12">
        <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Danh sách truyện thuộc thể loại: {{ $category->name }}</h5>
                        <p class="text-sm mb-0">{{ $category->description }}</p>
                    </div>
                    <a href="{{ route('categories.index') }}" class="btn bg-gradient-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ảnh bìa</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tiêu đề</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Thể loại</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Số chương</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Trạng thái</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stories as $story)
                            <tr>
                                <td class="text-center">{{ $story->id }}</td>
                                <td>
                                    <img src="{{ Storage::url($story->cover) }}" class="avatar avatar-sm me-3">
                                </td>
                                <td>{{ $story->title }}</td>
                                <td>
                                    @foreach($story->categories as $category)
                                        <span class="badge badge-sm bg-gradient-info">{{ $category->name }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $story->chapters_count }}</td>
                                <td>
                                    <span class="badge badge-sm bg-gradient-{{ $story->status === 'published' ? 'success' : 'secondary' }}">
                                        {{ $story->status === 'published' ? 'Đã xuất bản' : 'Bản nháp' }}
                                    </span>
                                </td>
                                <td class="text-center d-flex flex-column">
                                    <a href="{{ route('stories.chapters.index', $story) }}" class="btn btn-link text-info p-1 mb-0">
                                        <i class="fas fa-book-open text-info me-2"></i>Xem chương
                                    </a>
                                    <a href="{{ route('stories.comments.index', $story) }}" class="btn btn-link text-warning p-1 mb-0">
                                        <i class="fas fa-comments text-warning me-2"></i>Xem bình luận
                                    </a>
                                    <a href="{{ route('stories.edit', $story) }}" class="btn btn-link text-dark px-3 mb-0">
                                        <i class="fas fa-pencil-alt text-dark me-2"></i>Sửa
                                    </a>
                                    @include('admin.pages.components.delete-form', [
                                                'id' => $story->id,
                                                'route' => route('stories.destroy', $story)
                                            ])
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Không có truyện nào trong thể loại này</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 pt-4">
                    <x-pagination :paginator="$stories" />
                </div>
            </div>
        </div>
    </div>
</div>
@endsection