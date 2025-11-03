<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Truyện</th>
                <th>Trạng thái</th>
                <th>Loại</th>
                <th>Số chương</th>
                <th>Lượt xem</th>
                <th>Lượt theo dõi</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $story)
                <tr>
                    <td>{{ $story->id }}</td>
                    <td>
                        <a href="{{ route('show.page.story', $story->slug) }}" class="d-flex align-items-center">
                            @if($story->cover)
                                <img src="{{ Storage::url($story->cover) }}" 
                                     alt="{{ $story->title }}" 
                                     class="rounded me-2" 
                                     style="width: 40px; height: 60px; object-fit: cover;">
                            @endif
                            <div class="flex-grow-1" style="min-width: 0;">
                                <h6 class="mb-0" style="line-height: 1.4; word-wrap: break-word;">{{ $story->title }}</h6>
                                <small class="text-muted d-block text-truncate" style="max-width: 100%;" title="{{ $story->slug }}">{{ $story->slug }}</small>
                            </div>
                        </a>
                    </td>
                    <td>
                        @if($story->status === 'published')
                            <span class="badge bg-success">Đã xuất bản</span>
                        @elseif($story->status === 'pending')
                            <span class="badge bg-warning">Chờ duyệt</span>
                        @elseif($story->status === 'draft')
                            <span class="badge bg-secondary">Bản nháp</span>
                        @else
                            <span class="badge bg-info">{{ ucfirst($story->status) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($story->story_type === 'original')
                            <span class="badge bg-primary">Sáng tác</span>
                        @elseif($story->story_type === 'translated')
                            <span class="badge bg-info">Dịch</span>
                        @elseif($story->story_type === 'collected')
                            <span class="badge bg-secondary">Sưu tầm</span>
                        @endif
                    </td>
                    <td>{{ $story->chapters_count ?? $story->chapters()->count() }}</td>
                    <td>{{ number_format($story->views ?? 0) }}</td>
                    <td>{{ number_format($story->bookmarks_count ?? $story->bookmarks()->count()) }}</td>
                    <td>
                        <small>{{ $story->created_at->format('d/m/Y H:i') }}</small>
                    </td>
                    <td>
                        <a href="{{ route('stories.show', $story->id) }}" 
                           class="btn btn-sm btn-outline-primary" 
                           target="_blank"
                           title="Xem truyện">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('stories.edit', $story->id) }}" 
                               class="btn btn-sm btn-outline-info" 
                               title="Chỉnh sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="fas fa-book fa-2x mb-2"></i>
                        <br>
                        Chưa có truyện nào
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
