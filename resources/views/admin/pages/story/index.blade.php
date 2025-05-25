@extends('admin.layouts.app')

@section('content-auth')
<div class="row">
    <div class="col-12">
        <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                    <div>
                        <h5 class="mb-0">
                            Danh sách truyện
                        </h5>
                        <p class="text-sm mb-0">
                            Tổng số: {{ $totalStories }} Truyện
                            ({{ $publishedStories }} hiển thị / {{ $draftStories }} nháp)
                        </p>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <form method="GET" class="d-flex gap-2">
                        <!-- Status filter -->
                        <select name="status" class="form-select form-select-sm" style="width: auto;">
                            <option value="">- Trạng thái -</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Hiển thị</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                        </select>
                
                        <!-- Category filter -->
                        <select name="category" class="form-select form-select-sm" style="width: auto;">
                            <option value="">- Thể loại -</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                
                        <!-- Search input -->
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" name="search" 
                                   value="{{ request('search') }}" placeholder="Tìm kiếm...">
                            <button class="btn bg-gradient-primary btn-sm px-2 mb-0" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                
                    <div>
                        <a href="{{ route('stories.create') }}" class="btn bg-gradient-primary btn-sm mb-0">
                            <i class="fas fa-plus me-2"></i>Thêm truyện mới
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body px-0 pt-0 pb-2">
                @include('admin.pages.components.success-error')
                
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ảnh bìa</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tiêu đề</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Thể loại</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Số chương</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Giá truyện</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Link aff</th>
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
                                        <span class="badge badge-sm {{ $category->is_main ? 'bg-gradient-warning' : 'bg-gradient-info' }}">
                                            {{ $category->name }}
                                            @if($category->is_main)
                                                <i class="fas fa-star ms-1"></i>
                                            @endif
                                        </span>
                                    @endforeach
                                </td>
                                <td>{{ $story->chapters_count }}</td>
                                <td>
                                    @if($story->has_combo)
                                        <span class="badge bg-gradient-danger">{{ $story->combo_price }} xu</span>
                                    @else
                                       -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ $story->link_aff }}" target="_blank"> {{ Str::limit($story->link_aff, 20) }}</a>
                                </td>
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
                                    <a href="{{ route('stories.edit', $story) }}" class="btn btn-link text-dark p-1 mb-0">
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
                                <td colspan="8" class="text-center py-4">Không có truyện nào</td>
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